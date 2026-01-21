@extends('dash.app')

@section('page', 'Investigar Abonos Problemáticos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-search mr-2"></i>
                        Investigación de Abonos Problemáticos
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Formulario de búsqueda -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('accounting.investigar-abonos') }}" class="form-inline">
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

                    <!-- Estadísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Abonos</h5>
                                    <h2>{{ $estadisticas['total'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Válidos</h5>
                                    <h2>{{ $estadisticas['validos'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Sin Estudiante</h5>
                                    <h2>{{ $estadisticas['sin_estudiante'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Sin Programa</h5>
                                    <h2>{{ $estadisticas['sin_programa'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de problemas -->
                    @if(count($problemas) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No. Recibo</th>
                                        <th>Fecha</th>
                                        <th>Valor</th>
                                        <th>Cod. Alumno</th>
                                        <th>Tipo Problema</th>
                                        <th>Información</th>
                                        <th>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($problemas as $problema)
                                        <tr class="{{ $problema['tipo_problema'] == 'SIN_ESTUDIANTE' ? 'table-danger' : 'table-warning' }}">
                                            <td>
                                                <strong>{{ $problema['no_recibo'] }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $problema['entry_id'] }}</small>
                                            </td>
                                            <td>{{ date('d/m/Y', strtotime($problema['fecha_recibo'])) }}</td>
                                            <td>${{ number_format($problema['valor'], 0, ',', '.') }}</td>
                                            <td>
                                                <code>{{ $problema['cod_alumno'] }}</code>
                                            </td>
                                            <td>
                                                @if($problema['tipo_problema'] == 'SIN_ESTUDIANTE')
                                                    <span class="badge badge-danger">Sin Estudiante</span>
                                                @else
                                                    <span class="badge badge-warning">Sin Programa</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($problema['nombre_estudiante']))
                                                    <strong>Nombre:</strong> {{ $problema['nombre_estudiante'] }}<br>
                                                @endif
                                                @if(isset($problema['cedula_estudiante']))
                                                    <strong>Cédula:</strong> {{ $problema['cedula_estudiante'] }}<br>
                                                @endif
                                                @if(isset($problema['nombre_mysql2']))
                                                    <strong>MySQL2:</strong> {{ $problema['nombre_mysql2'] }}<br>
                                                @endif
                                                @if(isset($problema['nombre_matricula']))
                                                    <strong>Matrícula:</strong> {{ $problema['nombre_matricula'] }}<br>
                                                @endif
                                                @if(isset($problema['programa_mysql2']))
                                                    <strong>Programa MySQL2:</strong> {{ $problema['programa_mysql2'] }}<br>
                                                @endif
                                                @if(isset($problema['programa_matricula']))
                                                    <strong>Programa Matrícula:</strong> {{ $problema['programa_matricula'] }}<br>
                                                @endif
                                                <strong>Concepto:</strong> {{ $problema['concepto'] }}<br>
                                                <strong>Descripción:</strong> {{ $problema['descripcion'] }}
                                            </td>
                                            <td>
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($problema['detalles'] as $detalle)
                                                        <li>
                                                            @if(strpos($detalle, '✓') !== false)
                                                                <span class="text-success">{{ $detalle }}</span>
                                                            @elseif(strpos($detalle, '✗') !== false)
                                                                <span class="text-danger">{{ $detalle }}</span>
                                                            @elseif(strpos($detalle, '⚠') !== false)
                                                                <span class="text-warning">{{ $detalle }}</span>
                                                            @else
                                                                {{ $detalle }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
@endsection
