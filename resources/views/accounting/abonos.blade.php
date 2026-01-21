@extends('dash.app')

@section('page', 'Informe de Abonos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i> Informe de Abonos</h4>
            </div>
            <div class="card-body">

                <form method="GET" action="{{ route('accounting.abonos') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Inicio <small class="text-muted">(Opcional)</small></label>
                                <input type="date" name="fecha_inicio" class="form-control" value="{{ $fecha_inicio ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Fin <small class="text-muted">(Opcional)</small></label>
                                <input type="date" name="fecha_fin" class="form-control" value="{{ $fecha_fin ?? '' }}">
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
                    <div class="row">
                        <div class="col-12">
                            @if(isset($fecha_inicio) && isset($fecha_fin))
                            <a href="{{ route('accounting.abonos.download', ['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]) }}" 
                               class="btn btn-success" id="btnDownload">
                                <i class="fas fa-file-excel mr-2"></i> Descargar Excel
                            </a>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>Para descargar Excel, primero aplica un filtro de fechas.
                            </div>
                            @endif
                            <a href="{{ route('accounting.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i> Volver
                            </a>
                        </div>
                    </div>
                </form>

                @if(isset($dataset))
                    @if(!isset($fecha_inicio) && !isset($fecha_fin))
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle mr-2"></i>Mostrando todos los registros. Usa los filtros de fecha para ver un rango específico.
                        </div>
                    @endif

                    @if($dataset['is_partial'])
                        <div class="alert alert-warning">
                            <strong>Vista previa parcial.</strong> Se muestran solo las primeras {{ \App\Services\AccountingReportService::MAX_PREVIEW_ROWS }} filas de un total de {{ number_format($dataset['total_rows'], 0, ',', '.') }}. Descarga el Excel para ver el informe completo.
                        </div>
                    @endif

                    @if(count($dataset['grouped']) > 0)
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
                                <thead class="thead-secondary" style="position: sticky; top: 0; z-index: 10; background-color: #6c757d !important;">
                                    <tr>
                                        <th style="width: 10%;">No Recibo</th>
                                        <th style="width: 12%;">Fecha Recibo</th>
                                        <th style="width: 12%;">Cédula</th>
                                        <th style="width: 25%;">Nombre(s)</th>
                                        <th style="width: 10%;">Tipo</th>
                                        <th style="width: 21%;">Descripción</th>
                                        <th style="width: 10%;" class="text-right">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataset['grouped'] as $programa => $items)
                                        <tr style="background-color: #0dcaf0; color: #000;">
                                            <td colspan="7" class="font-weight-bold" style="padding: 12px;">
                                                <i class="fas fa-folder-open mr-2"></i>{{ $programa }}
                                            </td>
                                        </tr>
                                        @foreach($items as $item)
                                            <tr>
                                                <td style="padding: 8px;">{{ $item['no_recibo'] }}</td>
                                                <td style="padding: 8px;">{{ date('d/m/Y', strtotime($item['fecha_recibo'])) }}</td>
                                                <td style="padding: 8px;">{{ $item['cedula'] }}</td>
                                                <td style="padding: 8px;">{{ $item['nombre'] }}</td>
                                                <td style="padding: 8px;">{{ $item['tipo'] }}</td>
                                                <td style="padding: 8px;">{{ $item['descripcion'] }}</td>
                                                <td class="text-right" style="padding: 8px;">${{ number_format($item['valor'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr style="background-color: #e9ecef; font-weight: bold;">
                                            <td colspan="6" class="text-right" style="padding: 10px;">Suma:</td>
                                            <td class="text-right" style="padding: 10px;">
                                                ${{ number_format(array_sum(array_column($items, 'valor')), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        <tr><td colspan="7" style="padding: 5px; border: none;"></td></tr>
                                    @endforeach
                                    <tr style="background-color: #d1e7dd; font-weight: bold; font-size: 1.05em;">
                                        <td colspan="6" class="text-right" style="padding: 12px;">TOTAL GENERAL</td>
                                        <td class="text-right" style="padding: 12px;">
                                            ${{ number_format($dataset['total'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>No se encontraron registros para el rango de fechas seleccionado.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
