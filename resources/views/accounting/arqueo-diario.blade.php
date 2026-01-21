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
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Nota:</strong> El Arqueo Diario utiliza <strong>bases diarias</strong> (una base por cada día). 
                    Si faltan bases diarias, se mostrará un mensaje de error con un enlace para registrarlas.
                    <br><small class="text-muted">Este reporte NO utiliza la base inicial. La base inicial solo se usa para Informes Semanal y Mensual.</small>
                </div>

                <form method="GET" action="{{ route('accounting.arqueo-diario') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha</label>
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
