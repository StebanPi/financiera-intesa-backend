@extends('dash.app')

@php
    $condicion = '';
@endphp
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Lista de Estudiantes <span class="badge light badge-danger">
            @php
                $condicion = '';
                switch ($estado) {
                case '1':
                    $condicion = "Activo";
                    echo "Activo";
                    break;
                case '2':
                    $condicion = "Inactivo";
                    echo 'Inactivo';
                    break;
                case '3':
                    $condicion = "Por Certificar";
                    echo 'Por Certificar';
                    break;
                case '4':
                    $condicion = "Certificado";
                    echo 'Certificado';
                    break;
                case '5':
                    $condicion = "Retirado";
                    echo 'Retirado';
                    break;
                case '6':
                    $condicion = "Suspendido";
                    echo 'Suspendido';
                    break;
                default:
                    # code...
                    $condicion = "Todos";
                    echo 'Todos';
                    break;
                }  
            @endphp
        </span></h4>
    </div>

    <div class="card-body">
        <p>
            <i class="fa-solid fa-circle-exclamation mr-2"></i>Si no has modificado el estado, los estudiantes que aparecen en la lista son los activos. 
            <div class="btn-group mb-1">
                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-success btn-sm px-3 light dropdown-toggle" type="button">Estado <span class="caret"></span>
                </button>
                <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 40px, 0px);">
                    <a href="{{ route('view.student.index',1) }}" class="dropdown-item">Activo</a> 
                    <a href="{{ route('view.student.index',2) }}" class="dropdown-item">Inactivo</a>
                    <a href="{{ route('view.student.index',3) }}" class="dropdown-item">Por Certificar</a>
                    <a href="{{ route('view.student.index',4) }}" class="dropdown-item">Certificado</a>
                    <a href="{{ route('view.student.index',5) }}" class="dropdown-item">Retirado</a>
                    <a href="{{ route('view.student.index',6) }}" class="dropdown-item">Suspendido</a>
                    <a href="{{ route('view.student.index',7) }}" class="dropdown-item">Todos</a>
                </div>
            </div>
        </p>
        <div class="table-responsive">
            <div id="example3_wrapper" class="dataTables_wrapper no-footer">
                @php 
                $head = [
                    [
                        "Columna" => "",
                        "Origen" => "",
                        "EsEnlace" => false,
                        "EsVacio" => true,
                    ],
                    [
                        "Columna" => "Cedula",
                        "Origen" => "cedula",
                        "EsEnlace" => true,
                        "Ruta" => [
                            "Nombre" => "student.view",
                            "Parametro" => "cod_alumno"
                        ],
                        "EsVacio" => false,
                    ],
                    [
                        "Columna" => "Nombre",
                        "Origen" => "nombre",
                        "EsEnlace" => false,
                        "EsVacio" => false,
                    ],
                    [
                        "Columna" => "Tecnico",
                        "Origen" => "nombre_programa",
                        "EsEnlace" => false,
                        "EsVacio" => false,
                    ]
                ]; 
                @endphp
                <x-table :thead="json_encode($head)" :tbody="$all" />
        </div>
    </div>
    
</div>


@endsection

@section('page')
    @php
        echo "Listado de estudiantes ".strtoupper($condicion);
    @endphp
@endsection