@extends('dash.app')

@section('page', 'Eliminar Abonos Problemáticos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Eliminar Abonos Problemáticos
                    </h4>
                </div>
                <div class="card-body">

                    <!-- Formulario de búsqueda -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('accounting.eliminar-abonos-problematicos') }}" class="form-inline">
                                <div class="form-group mr-3">
                                    <label for="fecha_inicio" class="mr-2">Fecha Inicio:</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ $fecha_inicio }}" required>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="fecha_fin" class="mr-2">Fecha Fin:</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fecha_fin }}" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-2"></i>Buscar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Resumen -->
                    @if($total > 0)
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i>Advertencia</h5>
                            <p>Se encontraron <strong>{{ $total }}</strong> abono(s) problemático(s) con un valor total de <strong>${{ number_format($totalValor, 0, ',', '.') }}</strong>.</p>
                            <p class="mb-0"><strong>Estos abonos no aparecerán en los reportes porque no tienen estudiante válido o no tienen programa asignado.</strong></p>
                        </div>
                    @endif

                    <!-- Formulario de eliminación -->
                    @if(count($abonosProblematicos) > 0)
                        <form method="POST" action="{{ route('accounting.eliminar-abonos-problematicos') }}" id="formEliminar" onsubmit="event.preventDefault(); showConfirmModal('¿Está seguro de eliminar los abonos seleccionados? Esta acción NO se puede deshacer.', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { this.submit(); } });">
                            @csrf
                            <input type="hidden" name="fecha_inicio" value="{{ $fecha_inicio }}">
                            <input type="hidden" name="fecha_fin" value="{{ $fecha_fin }}">
                            
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="seleccionarTodos()">
                                    <i class="fas fa-check-square mr-2"></i>Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="deseleccionarTodos()">
                                    <i class="fas fa-square mr-2"></i>Deseleccionar Todos
                                </button>
                                <button type="submit" class="btn btn-danger btn-sm" id="btnEliminar" disabled>
                                    <i class="fas fa-trash-alt mr-2"></i>Eliminar Seleccionados
                                </button>
                                <span class="ml-3" id="contadorSeleccionados">0 seleccionados</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="checkTodos" onchange="toggleTodos()">
                                            </th>
                                            <th>No. Recibo</th>
                                            <th>Fecha</th>
                                            <th>Valor</th>
                                            <th>Cod. Alumno</th>
                                            <th>Razón</th>
                                            <th>Concepto</th>
                                            <th>Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($abonosProblematicos as $abono)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" 
                                                           name="eliminar_ids[]" 
                                                           value="{{ $abono['id'] }}"
                                                           class="checkAbono"
                                                           onchange="actualizarContador()">
                                                </td>
                                                <td>
                                                    <strong>{{ $abono['no_recibo'] }}</strong>
                                                    <br>
                                                    <small class="text-muted">ID: {{ $abono['id'] }}</small>
                                                </td>
                                                <td>{{ date('d/m/Y', strtotime($abono['fecha_recibo'])) }}</td>
                                                <td>${{ number_format($abono['valor'], 0, ',', '.') }}</td>
                                                <td><code>{{ $abono['cod_alumno'] }}</code></td>
                                                <td>
                                                    <span class="badge badge-{{ $abono['razon'] == 'Sin estudiante válido' ? 'danger' : 'warning' }}">
                                                        {{ $abono['razon'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $abono['concepto'] }}</td>
                                                <td>{{ $abono['descripcion'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <th colspan="3" class="text-right">Total:</th>
                                            <th>${{ number_format($totalValor, 0, ',', '.') }}</th>
                                            <th colspan="4"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i>
                            <strong>¡Excelente!</strong> No se encontraron abonos problemáticos en el rango de fechas seleccionado.
                        </div>
                    @endif

                    <!-- Botón para volver -->
                    <div class="mt-4">
                        <a href="{{ route('accounting.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Volver a Contabilidad
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleTodos() {
    var checkTodos = document.getElementById('checkTodos');
    var checks = document.querySelectorAll('.checkAbono');
    checks.forEach(function(check) {
        check.checked = checkTodos.checked;
    });
    actualizarContador();
}

function seleccionarTodos() {
    var checks = document.querySelectorAll('.checkAbono');
    checks.forEach(function(check) {
        check.checked = true;
    });
    document.getElementById('checkTodos').checked = true;
    actualizarContador();
}

function deseleccionarTodos() {
    var checks = document.querySelectorAll('.checkAbono');
    checks.forEach(function(check) {
        check.checked = false;
    });
    document.getElementById('checkTodos').checked = false;
    actualizarContador();
}

function actualizarContador() {
    var checks = document.querySelectorAll('.checkAbono:checked');
    var contador = document.getElementById('contadorSeleccionados');
    var btnEliminar = document.getElementById('btnEliminar');
    
    var seleccionados = checks.length;
    contador.textContent = seleccionados + ' seleccionado(s)';
    
    if (seleccionados > 0) {
        btnEliminar.disabled = false;
    } else {
        btnEliminar.disabled = true;
    }
}

// Actualizar contador cuando cambie cualquier checkbox
document.addEventListener('DOMContentLoaded', function() {
    var checks = document.querySelectorAll('.checkAbono');
    checks.forEach(function(check) {
        check.addEventListener('change', actualizarContador);
    });
    actualizarContador();
});
</script>
@endpush
@endsection
