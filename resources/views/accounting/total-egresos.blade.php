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
                <form method="GET" action="{{ route('accounting.total-egresos') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-download mr-2"></i> Generar y Descargar Excel</button>
                    <a href="{{ route('accounting.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i> Volver</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
