@extends('dash.app')

@section('page')
    Fichas de Matrícula
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0"><i class="fa-solid fa-file-lines mr-2"></i>Lista de Fichas de Matrícula</h4>
            <a href="{{ route('matricula.create') }}" class="btn btn-primary btn-sm ml-3">
                <i class="fa-solid fa-user-plus mr-2"></i>Matricular Estudiante
            </a>
        </div>
    </div>
    <div class="card-body">
        @if (session('warning') && session('cod_alumno_pendiente') && session('relaciones'))
            @php
                $warningMessage = session('warning');
                $relacionesHtml = '<div class=\"mt-3 pt-3 border-top\"><p class=\"mb-2\"><strong>Datos relacionados que se eliminarán:</strong></p><ul style=\"margin-bottom: 15px;\">';
                if(session('relaciones')['entries'] > 0) {
                    $relacionesHtml .= '<li>' . session('relaciones')['entries'] . ' abono(s)</li>';
                }
                if(session('relaciones')['other_entries'] > 0) {
                    $relacionesHtml .= '<li>' . session('relaciones')['other_entries'] . ' otro(s) ingreso(s)</li>';
                }
                if(session('relaciones')['purses'] > 0) {
                    $relacionesHtml .= '<li>' . session('relaciones')['purses'] . ' cuota(s)</li>';
                }
                $relacionesHtml .= '</ul>';
                $relacionesHtml .= '<form method=\"POST\" action=\"' . route('matricula.destroy', session('cod_alumno_pendiente')) . '\" class=\"mt-2\" id=\"formEliminarCascada\" onsubmit=\"event.preventDefault(); showConfirmModal(\'¿Está seguro de eliminar la matrícula y TODOS sus datos relacionados? Esta acción NO se puede deshacer.\', \'Confirmar Eliminación\', \'Eliminar Todo\', \'Cancelar\', \'btn-danger\').then(confirmed => { if(confirmed) { document.getElementById(\'formEliminarCascada\').submit(); } });\">';
                $relacionesHtml .= csrf_field();
                $relacionesHtml .= method_field('DELETE');
                $relacionesHtml .= '<input type=\"hidden\" name=\"confirmar_cascada\" value=\"1\">';
                $relacionesHtml .= '<button type=\"submit\" class=\"btn btn-danger btn-sm\"><i class=\"fa-solid fa-trash mr-2\"></i>Eliminar Todo (Matrícula + Datos Relacionados)</button>';
                $relacionesHtml .= '</form></div>';
                $fullMessage = $warningMessage . $relacionesHtml;
            @endphp
            <x-alert-modal type="warning" :message="$fullMessage" title="Advertencia" id="warning-cascade" />
        @endif

        <!-- Botones de exportación (se llenarán automáticamente por DataTables) -->
        <div class="mb-3" id="botonesExportacion"></div>

        <!-- Tabla con DataTables -->
        <div class="table-responsive">
            <div id="tablaMatriculas_wrapper" class="dataTables_wrapper no-footer">
            <table id="tablaMatriculas" class="display dataTable no-footer tablep" style="min-width: 845px" role="grid">
                <thead class="thead-secondary text-primary text-center">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Documento</th>
                        <th>Tipo Doc.</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Programa</th>
                        <th>Sede</th>
                        <th>Horario</th>
                        <th>Ciudad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matriculas as $matricula)
                        <tr>
                            <td>{{ $matricula->id }}</td>
                            <td>{{ $matricula->nombre_completo }}</td>
                            <td>{{ $matricula->numero_documento }}</td>
                            <td>{{ $matricula->tipo_documento }}</td>
                            <td>{{ $matricula->correo_gmail ?? '-' }}</td>
                            <td>{{ $matricula->telefono_personal ?? '-' }}</td>
                            <td>{{ $matricula->programa ?? '-' }}</td>
                            <td>{{ $matricula->sede ?? '-' }}</td>
                            <td>{{ $matricula->horario ?? '-' }}</td>
                            <td>{{ $matricula->ciudad_residencia ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('matricula.estudiante', $matricula->cod_alumno) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Ver Estudiante">
                                        <i class="fa-solid fa-dollar-sign"></i>
                                    </a>
                                    <a href="{{ route('matricula.ficha', $matricula->cod_alumno) }}" 
                                       class="btn btn-sm btn-info ml-1" 
                                       title="Editar Ficha">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <form action="{{ route('matricula.destroy', $matricula->cod_alumno) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="event.preventDefault(); showConfirmModal('¿Está seguro de eliminar esta matrícula? Si tiene abonos o cuotas, deberá confirmar la eliminación en cascada.', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { this.submit(); } });">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger ml-1" title="Eliminar Matrícula">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                        </tr>
                    @empty
                        <tr>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td class="text-center">-</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
jQuery(document).ready(function($) {
    // Esperar a que todas las librerías de DataTables estén cargadas y que datatables.init.js termine
    setTimeout(function() {
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables no está cargado');
            return;
        }
        
        if (typeof $.fn.DataTable.Buttons === 'undefined') {
            console.error('DataTables Buttons no está cargado');
            return;
        }
        
        // Verificar que la tabla existe y tiene la estructura correcta
        var $table = $('#tablaMatriculas');
        if ($table.length === 0) {
            console.error('La tabla #tablaMatriculas no existe');
            return;
        }
        
        // Verificar que la tabla tenga el thead y tbody
        if ($table.find('thead').length === 0 || $table.find('tbody').length === 0) {
            console.error('La tabla no tiene la estructura correcta (thead/tbody)');
            return;
        }
        
        // Verificar que todas las filas tengan el mismo número de celdas que las columnas del thead
        var headerCells = $table.find('thead tr:first th').length;
        var $rows = $table.find('tbody tr');
        $rows.each(function() {
            var cells = $(this).find('td').length;
            if (cells !== headerCells) {
                console.error('La fila no tiene el número correcto de celdas. Esperado: ' + headerCells + ', Encontrado: ' + cells);
            }
        });
        
        // Destruir cualquier instancia previa
        if ($.fn.DataTable.isDataTable('#tablaMatriculas')) {
            $('#tablaMatriculas').DataTable().destroy();
        }
        
        // Idioma español definido directamente para evitar CORS
        var spanishLanguage = {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": ">",
                "sPrevious": "<"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        };
        
        try {
            // Inicializar DataTables con botones
            var table = $('#tablaMatriculas').DataTable({
                "language": spanishLanguage,
                "responsive": true,
                "dom": 'lBfrtip',
                "buttons": [{
                    extend: 'excel',
                    text: '<i class="mdi mdi-file-excel"></i> Excel',
                    titleAttr: 'Exportar a Excel',
                    className: 'ml-3 btn btn-success btn-xs',
                    excelStyles: {
                        template: 'blue_medium'
                    },
                    title: 'Fichas de Matrícula - ' + new Date().toLocaleDateString(),
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="mdi mdi-file-pdf"></i> PDF',
                    titleAttr: 'Exportar a Pdf',
                    className: 'ml-3 btn btn-danger btn-xs',
                    title: 'Fichas de Matrícula - ' + new Date().toLocaleDateString(),
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="mdi mdi-cloud-print"></i> Imprimir',
                    titleAttr: 'Imprimir',
                    className: 'ml-3 btn btn-info btn-xs',
                    title: 'Fichas de Matrícula - ' + new Date().toLocaleDateString(),
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                    }
                }],
                "pageLength": 25,
                "order": [[1, 'asc']],
                "initComplete": function(settings, json) {
                    // Mover los botones al contenedor personalizado
                    setTimeout(function() {
                        var buttonsContainer = $('.dt-buttons');
                        if (buttonsContainer.length > 0) {
                            buttonsContainer.appendTo('#botonesExportacion');
                            buttonsContainer.show();
                            console.log('Botones movidos al contenedor personalizado');
                        }
                    }, 100);
                    
                    // Agregar filtros personalizados al lado del campo de búsqueda
                    var searchContainer = $('.dataTables_filter');
                    if (searchContainer.length > 0) {
                        // Crear contenedor para los filtros
                        var filtersHtml = '<div class="d-inline-block ml-3">' +
                            '<label class="mr-2">Sede:</label>' +
                            '<select id="filtroSede" class="form-control form-control-sm d-inline-block" style="width: auto; min-width: 150px;">' +
                            '<option value="">Todos</option>' +
                            '<option value="Barrancabermeja">Barrancabermeja</option>' +
                            '<option value="Aguachica">Aguachica</option>' +
                            '<option value="Virtual">Virtual</option>' +
                            '</select>' +
                            '</div>' +
                            '<div class="d-inline-block ml-3">' +
                            '<label class="mr-2">Programa:</label>' +
                            '<select id="filtroPrograma" class="form-control form-control-sm d-inline-block" style="width: auto; min-width: 200px;">' +
                            '<option value="">Todos</option>' +
                            '<option value="Auxiliar de Primera Infancia">Auxiliar de Primera Infancia</option>' +
                            '<option value="Auxiliar Administrativo">Auxiliar Administrativo</option>' +
                            '<option value="Seguridad en el Trabajo">Seguridad en el Trabajo</option>' +
                            '<option value="Operador de Maquinaria Pesada">Operador de Maquinaria Pesada</option>' +
                            '<option value="Mecánica Diesel Automotriz">Mecánica Diesel Automotriz</option>' +
                            '</select>' +
                            '</div>';
                        
                        searchContainer.append(filtersHtml);
                        
                        // Aplicar filtros cuando cambien los select
                        $('#filtroSede, #filtroPrograma').on('change', function() {
                            var sedeValue = $('#filtroSede').val();
                            var programaValue = $('#filtroPrograma').val();
                            
                            // Filtrar por Programa (columna índice 6)
                            table.column(6).search(programaValue);
                            
                            // Filtrar por Sede (columna índice 7)
                            table.column(7).search(sedeValue);
                            
                            // Aplicar los filtros
                            table.draw();
                        });
                    }
                }
            });
            
            // Mover los botones después de la inicialización (por si acaso)
            setTimeout(function() {
                var buttonsContainer = $('.dt-buttons');
                if (buttonsContainer.length > 0) {
                    buttonsContainer.appendTo('#botonesExportacion');
                    buttonsContainer.show();
                } else {
                    console.error('Los botones de DataTables no se crearon');
                }
            }, 500);
        } catch (error) {
            console.error('Error al inicializar DataTables:', error);
        }
    }, 1000); // Aumentar el tiempo de espera para asegurar que datatables.init.js termine
});
</script>
@endpush
@endsection

