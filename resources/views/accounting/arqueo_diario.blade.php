@extends('dash.app')

@section('page', 'Arqueo Diario')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-calendar-day mr-2"></i> Arqueo Diario</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form method="GET" action="{{ route('accounting.arqueo-diario') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha</label>
                                <input type="date" name="fecha" class="form-control" value="{{ $fecha ?? '' }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search mr-2"></i> Aplicar Filtros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(isset($fecha))
                    <div class="row">
                        <div class="col-12">
                            <a href="{{ route('accounting.arqueo-diario.download', ['fecha' => $fecha]) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel mr-2"></i> Descargar Excel
                            </a>
                            <a href="{{ route('accounting.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i> Volver
                            </a>
                        </div>
                    </div>
                    @endif
                </form>

                @if(isset($fecha))
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-dollar-sign mr-2"></i>Base Diaria del {{ date('d/m/Y', strtotime($fecha)) }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($cashBase)
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-success mb-0">
                                            <h6 class="mb-2"><i class="fas fa-money-bill-wave mr-2"></i>Base de Efectivo</h6>
                                            <h4 class="mb-0">${{ number_format($cashBase->base_efectivo, 2, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-primary mb-0">
                                            <h6 class="mb-2"><i class="fas fa-university mr-2"></i>Base de Banco</h6>
                                            <h4 class="mb-0">${{ number_format($cashBase->base_banco, 2, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Base diaria registrada. Última actualización: {{ $cashBase->updated_at ? $cashBase->updated_at->format('d/m/Y H:i') : 'N/A' }}
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>No hay base diaria registrada para esta fecha.</strong>
                                    <p class="mb-0 mt-2">Para generar el reporte completo, es necesario registrar la base diaria.</p>
                                    <a href="{{ route('accounting.cash-bases', ['missing_dates' => [$fecha]]) }}" class="btn btn-warning btn-sm mt-2">
                                        <i class="fas fa-edit mr-2"></i> Registrar Base Diaria
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if(isset($dataset))
                    @if(!empty($dataset['missing_dates']))
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i>Faltan bases diarias</h5>
                            <p>No se puede generar el reporte completo porque faltan bases para las siguientes fechas:</p>
                            <ul>
                                @foreach($dataset['missing_dates'] as $missingDate)
                                    <li>{{ date('d/m/Y', strtotime($missingDate)) }}</li>
                                @endforeach
                            </ul>
                            <a href="{{ route('accounting.cash-bases') }}" class="btn btn-warning">
                                <i class="fas fa-edit mr-2"></i> Registrar Bases Diarias
                            </a>
                        </div>
                    @elseif(isset($dataset['dates']) && count($dataset['dates']) > 0)
                        @if($dataset['is_partial'])
                            <div class="alert alert-warning">
                                <strong>Vista previa parcial.</strong> Se muestran solo las primeras {{ \App\Services\AccountingReportService::MAX_PREVIEW_ROWS }} filas. Descarga el Excel para ver el informe completo.
                            </div>
                        @endif

                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0; font-size: 0.85em;">
                                <thead class="thead-secondary" style="position: sticky; top: 0; z-index: 10; background-color: #6c757d !important;">
                                    <tr>
                                        <th style="width: 7%;">FECHA</th>
                                        <th style="width: 15%;">NOMBRE DEL REGISTRO</th>
                                        <th style="width: 10%;">OCUPACION</th>
                                        <th style="width: 8%;">CONCEPTO</th>
                                        <th style="width: 15%;">DESCRIPCIÓN</th>
                                        <th style="width: 7%;">N°RECIBO</th>
                                        <th style="width: 7%;" class="text-right">ING EFECTIVO</th>
                                        <th style="width: 7%;" class="text-right">ING BANCO</th>
                                        <th style="width: 7%;" class="text-right">EGR EFECTIVO</th>
                                        <th style="width: 7%;" class="text-right">EGR BANCO</th>
                                        <th style="width: 8%;" class="text-right">SALDO EFECTIVO</th>
                                        <th style="width: 8%;" class="text-right">SALDO BANCO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataset['dates'] as $dayData)
                                        <tr style="background-color: #0d6efd; color: #fff;">
                                            <td colspan="12" class="font-weight-bold" style="padding: 12px;">
                                                <i class="fas fa-calendar-alt mr-2"></i>{{ date('d/m/Y', strtotime($dayData['fecha'])) }}
                                            </td>
                                        </tr>
                                        <tr style="background-color: #0dcaf0; font-weight: bold;">
                                            <td style="padding: 10px;">{{ date('d/m/Y', strtotime($dayData['fecha'])) }}</td>
                                            <td colspan="4" style="padding: 10px;">BASE DE EFECTIVO</td>
                                            <td style="padding: 10px;"></td>
                                            <td class="text-right" style="padding: 10px;">${{ number_format($dayData['base_efectivo'], 0, ',', '.') }}</td>
                                            <td style="padding: 10px;"></td>
                                            <td style="padding: 10px;"></td>
                                            <td style="padding: 10px;"></td>
                                            <td class="text-right" style="padding: 10px;">${{ number_format($dayData['base_efectivo'], 0, ',', '.') }}</td>
                                            <td style="padding: 10px;"></td>
                                        </tr>
                                        <tr style="background-color: #0dcaf0; font-weight: bold;">
                                            <td style="padding: 10px;">{{ date('d/m/Y', strtotime($dayData['fecha'])) }}</td>
                                            <td colspan="4" style="padding: 10px;">BASE DE BANCO</td>
                                            <td style="padding: 10px;"></td>
                                            <td style="padding: 10px;"></td>
                                            <td class="text-right" style="padding: 10px;">${{ number_format($dayData['base_banco'], 0, ',', '.') }}</td>
                                            <td style="padding: 10px;"></td>
                                            <td style="padding: 10px;"></td>
                                            <td style="padding: 10px;"></td>
                                            <td class="text-right" style="padding: 10px;">${{ number_format($dayData['base_banco'], 0, ',', '.') }}</td>
                                        </tr>
                                        @foreach($dayData['movements'] as $mov)
                                            <tr>
                                                <td style="padding: 8px;">{{ date('d/m/Y', strtotime($dayData['fecha'])) }}</td>
                                                <td style="padding: 8px;">{{ $mov['nombre'] }}</td>
                                                <td style="padding: 8px;">{{ $mov['ocupacion'] }}</td>
                                                <td style="padding: 8px;">{{ $mov['concepto'] }}</td>
                                                <td style="padding: 8px;">{{ $mov['descripcion'] }}</td>
                                                <td style="padding: 8px;">{{ $mov['no_recibo'] }}</td>
                                                <td class="text-right" style="padding: 8px;">@if($mov['ing_efectivo'] > 0)${{ number_format($mov['ing_efectivo'], 0, ',', '.') }}@endif</td>
                                                <td class="text-right" style="padding: 8px;">@if($mov['ing_banco'] > 0)${{ number_format($mov['ing_banco'], 0, ',', '.') }}@endif</td>
                                                <td class="text-right" style="padding: 8px;">@if($mov['egr_efectivo'] > 0)${{ number_format($mov['egr_efectivo'], 0, ',', '.') }}@endif</td>
                                                <td class="text-right" style="padding: 8px;">@if($mov['egr_banco'] > 0)${{ number_format($mov['egr_banco'], 0, ',', '.') }}@endif</td>
                                                <td class="text-right font-weight-bold" style="padding: 8px;">${{ number_format($mov['saldo_efectivo'], 0, ',', '.') }}</td>
                                                <td class="text-right font-weight-bold" style="padding: 8px;">${{ number_format($mov['saldo_banco'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr><td colspan="12" style="padding: 5px; border: none;"></td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>No se encontraron registros para la fecha seleccionada.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
