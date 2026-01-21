@extends('dash.app')

@section('page', 'Informe Mensual')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-calendar-alt mr-2"></i> Informe Mensual</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.arqueo-mensual') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mes</label>
                                <select name="mes" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="1" {{ request('mes') == '1' ? 'selected' : '' }}>Enero</option>
                                    <option value="2" {{ request('mes') == '2' ? 'selected' : '' }}>Febrero</option>
                                    <option value="3" {{ request('mes') == '3' ? 'selected' : '' }}>Marzo</option>
                                    <option value="4" {{ request('mes') == '4' ? 'selected' : '' }}>Abril</option>
                                    <option value="5" {{ request('mes') == '5' ? 'selected' : '' }}>Mayo</option>
                                    <option value="6" {{ request('mes') == '6' ? 'selected' : '' }}>Junio</option>
                                    <option value="7" {{ request('mes') == '7' ? 'selected' : '' }}>Julio</option>
                                    <option value="8" {{ request('mes') == '8' ? 'selected' : '' }}>Agosto</option>
                                    <option value="9" {{ request('mes') == '9' ? 'selected' : '' }}>Septiembre</option>
                                    <option value="10" {{ request('mes') == '10' ? 'selected' : '' }}>Octubre</option>
                                    <option value="11" {{ request('mes') == '11' ? 'selected' : '' }}>Noviembre</option>
                                    <option value="12" {{ request('mes') == '12' ? 'selected' : '' }}>Diciembre</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Año</label>
                                <input type="number" name="anio" class="form-control" value="{{ request('anio', date('Y')) }}" min="2020" max="2100" required>
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
