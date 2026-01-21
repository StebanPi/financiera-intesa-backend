@extends('dash.app')

@section('page', 'Informe Semanal')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-calendar-week mr-2"></i> Informe Semanal</h4>
            </div>
            <div class="card-body">

                <form method="GET" action="{{ route('accounting.informe-semanal') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha (cualquier día de la semana)</label>
                                <input type="date" name="fecha" class="form-control" value="{{ $fecha ?? '' }}" required>
                                <small class="form-text text-muted">Se calculará el rango de lunes a domingo</small>
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
                            <a href="{{ route('accounting.informe-semanal.download', ['fecha' => $fecha]) }}" 
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

                @if(isset($dataset))
                    @if($dataset['missing_initial_base'] ?? false)
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i>Base Inicial No Configurada</h5>
                            <p>No se puede generar el reporte porque no se ha configurado la base inicial.</p>
                            <a href="{{ route('accounting.base-inicial') }}" class="btn btn-warning">
                                <i class="fas fa-cog mr-2"></i> Configurar Base Inicial
                            </a>
                        </div>
                    @elseif(isset($dataset['rows']) && count($dataset['rows']) > 0)
                        @if($dataset['is_partial'] ?? false)
                            <div class="alert alert-warning">
                                <strong>Vista previa parcial.</strong> Se muestran solo las primeras {{ \App\Services\AccountingReportService::MAX_PREVIEW_ROWS }} filas. Descarga el Excel para ver el informe completo.
                            </div>
                        @endif

                        {{-- Saldo de Apertura --}}
                        <div class="alert alert-info mb-3">
                            <h5><i class="fas fa-info-circle mr-2"></i>Saldo de Apertura</h5>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <strong>Saldo Apertura Efectivo:</strong> ${{ number_format($dataset['opening']['saldo_efectivo'] ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Saldo Apertura Banco:</strong> ${{ number_format($dataset['opening']['saldo_banco'] ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

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
                                    @foreach($dataset['rows'] as $row)
                                        <tr>
                                            <td style="padding: 8px;">{{ date('d/m/Y', strtotime($row['fecha'])) }}</td>
                                            <td style="padding: 8px;">{{ $row['nombre'] }}</td>
                                            <td style="padding: 8px;">{{ $row['ocupacion'] }}</td>
                                            <td style="padding: 8px;">{{ $row['concepto'] }}</td>
                                            <td style="padding: 8px;">{{ $row['descripcion'] }}</td>
                                            <td style="padding: 8px;">{{ $row['no_recibo'] }}</td>
                                            <td class="text-right" style="padding: 8px;">@if($row['ing_efectivo'] > 0)${{ number_format($row['ing_efectivo'], 0, ',', '.') }}@endif</td>
                                            <td class="text-right" style="padding: 8px;">@if($row['ing_banco'] > 0)${{ number_format($row['ing_banco'], 0, ',', '.') }}@endif</td>
                                            <td class="text-right" style="padding: 8px;">@if($row['egr_efectivo'] > 0)${{ number_format($row['egr_efectivo'], 0, ',', '.') }}@endif</td>
                                            <td class="text-right" style="padding: 8px;">@if($row['egr_banco'] > 0)${{ number_format($row['egr_banco'], 0, ',', '.') }}@endif</td>
                                            <td class="text-right font-weight-bold" style="padding: 8px;">${{ number_format($row['saldo_efectivo'], 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold" style="padding: 8px;">${{ number_format($row['saldo_banco'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Resumen Final --}}
                        @if(isset($dataset['summary']))
                        <div class="card mt-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-calculator mr-2"></i> RESUMEN</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th class="bg-light">Total Ingresos Efectivo</th>
                                                <td class="text-right font-weight-bold">${{ number_format($dataset['summary']['total_ing_efectivo'], 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Total Ingresos Banco</th>
                                                <td class="text-right font-weight-bold">${{ number_format($dataset['summary']['total_ing_banco'], 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Total Egresos Efectivo</th>
                                                <td class="text-right font-weight-bold">${{ number_format($dataset['summary']['total_egr_efectivo'], 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Total Egresos Banco</th>
                                                <td class="text-right font-weight-bold">${{ number_format($dataset['summary']['total_egr_banco'], 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th class="bg-success text-white">Saldo Final Efectivo</th>
                                                <td class="text-right font-weight-bold text-success">${{ number_format($dataset['summary']['saldo_final_efectivo'], 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-success text-white">Saldo Final Banco</th>
                                                <td class="text-right font-weight-bold text-success">${{ number_format($dataset['summary']['saldo_final_banco'], 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-primary text-white">Saldo Final Total</th>
                                                <td class="text-right font-weight-bold text-primary">${{ number_format($dataset['summary']['saldo_final_total'] ?? ($dataset['summary']['saldo_final_efectivo'] + $dataset['summary']['saldo_final_banco']), 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>No se encontraron registros para la semana seleccionada.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
