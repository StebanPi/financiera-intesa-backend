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
                <form method="GET" action="{{ route('accounting.arqueo-semanal') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha (el sistema calculará lunes a domingo de esa semana)</label>
                                <input type="date" name="fecha" class="form-control" value="{{ request('fecha', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fas fa-download mr-2"></i> Generar y Descargar Excel</button>
                    <a href="{{ route('accounting.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i> Volver</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
