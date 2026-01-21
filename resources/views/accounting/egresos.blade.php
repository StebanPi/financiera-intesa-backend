@extends('dash.app')

@section('page', 'Informe Total Egresos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-arrow-down mr-2"></i> Informe Total Egresos</h4>
            </div>
            <div class="card-body">

                <form method="GET" action="{{ route('accounting.total-egresos') }}" class="mb-4">
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
                            <a href="{{ route('accounting.total-egresos.download', ['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]) }}" 
                               class="btn btn-success">
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

                    @if(count($dataset['items']) > 0)
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
                                <thead class="thead-secondary" style="position: sticky; top: 0; z-index: 10; background-color: #6c757d !important;">
                                    <tr>
                                        <th style="width: 10%;">FECHA</th>
                                        <th style="width: 20%;">PROVEEDOR</th>
                                        <th style="width: 8%;">TIPO</th>
                                        <th style="width: 15%;">CONCEPTO</th>
                                        <th style="width: 22%;">DESCRIPCIÓN</th>
                                        <th style="width: 10%;">N°RECIBO</th>
                                        <th style="width: 7%;" class="text-right">VALOR</th>
                                        <th style="width: 8%;" class="text-right">SUMA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataset['items'] as $item)
                                        <tr>
                                            <td style="padding: 8px;">{{ date('d/m/Y', strtotime($item['fecha'])) }}</td>
                                            <td style="padding: 8px;">{{ $item['proveedor'] }}</td>
                                            <td style="padding: 8px;">{{ $item['tipo'] }}</td>
                                            <td style="padding: 8px;">{{ $item['concepto'] }}</td>
                                            <td style="padding: 8px;">{{ $item['descripcion'] }}</td>
                                            <td style="padding: 8px;">{{ $item['no_recibo'] }}</td>
                                            <td class="text-right" style="padding: 8px;">${{ number_format($item['valor'], 0, ',', '.') }}</td>
                                            <td class="text-right font-weight-bold" style="padding: 8px;">${{ number_format($item['suma'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr style="background-color: #f8d7da; font-weight: bold; font-size: 1.05em;">
                                        <td colspan="6" class="text-right" style="padding: 12px;">SUMA TOTAL DE EGRESOS</td>
                                        <td colspan="2" class="text-right" style="padding: 12px;">
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
