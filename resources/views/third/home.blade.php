@extends('dash.app')
@section('content')
<style>
    .container-fluid.third-entry-page {
        padding-top: 0px !important;
    }
</style>
<div class="container-fluid third-entry-page">
    <div class="row" style="margin-top: 0;">
        <div class="col-md-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0" style="color: #374151; font-weight: 600;">
                    <i class="fa-solid fa-users text-success mr-2"></i>
                    Gestionar Terceros
                </h4>
                <div>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalAddThird" style="border-radius: 8px; padding: 10px 20px; font-weight: 600;">
                        <i class="fa-solid fa-plus mr-2"></i>Agregar Tercero
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <!-- Tarjeta de Conceptos -->
            <div class="card shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; border-radius: 12px 12px 0 0; padding: 20px;">
                    <h5 class="mb-0 d-flex align-items-center" style="color: #374151; font-weight: 600;">
                        <i class="fa-solid fa-file-invoice-dollar text-info mr-2" style="font-size: 20px;"></i>
                        Conceptos de Recibos de Ingreso
                    </h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div class="d-flex flex-row gap-2 mb-3">
                        <button type="button" class="btn btn-primary btn-sm flex-fill" data-toggle="modal" data-target="#modalAddConceptEntry" style="border-radius: 8px; padding: 10px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-plus-circle"></i>
                            Agregar Concepto
                        </button>
                        <button type="button" class="btn btn-warning btn-sm flex-fill" data-toggle="modal" data-target="#modalEditConceptEntry" style="border-radius: 8px; padding: 10px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-pencil"></i>
                            Editar Conceptos
                        </button>
                    </div>
                    <!-- Listado de Conceptos -->
                    <div style="max-height: 400px; overflow-y: auto; border-top: 1px solid #e5e7eb; padding-top: 15px;">
                        @if(isset($conceptsEntry) && count($conceptsEntry) > 0)
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr style="background-color: #f9fafb;">
                                        <th style="font-size: 14px; padding: 12px 10px; font-weight: 600; color: #374151;">Nombre</th>
                                        <th style="font-size: 14px; padding: 12px 10px; font-weight: 600; text-align: center; color: #374151;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($conceptsEntry as $concept)
                                    <tr style="cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor=''" onclick="document.getElementById('modalEditConceptEntry').querySelector('input[name=\"name\"][value=\"{{ $concept->name }}\"]')?.closest('form').scrollIntoView({behavior: 'smooth', block: 'center'}); $('#modalEditConceptEntry').modal('show');">
                                        <td style="font-size: 14px; padding: 12px 10px; color: #1f2937;">{{ $concept->name }}</td>
                                        <td style="font-size: 14px; padding: 12px 10px; text-align: center;">
                                            @if($concept->state)
                                                <span class="badge badge-success" style="font-size: 12px; padding: 6px 10px;">Activo</span>
                                            @else
                                                <span class="badge badge-secondary" style="font-size: 12px; padding: 6px 10px;">Inactivo</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center text-muted" style="padding: 30px; font-size: 14px;">
                                <i class="fa-solid fa-inbox mb-2" style="font-size: 32px; opacity: 0.5;"></i>
                                <p class="mb-0">No hay conceptos registrados</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Actividades -->
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; border-radius: 12px 12px 0 0; padding: 20px;">
                    <h5 class="mb-0 d-flex align-items-center" style="color: #374151; font-weight: 600;">
                        <i class="fa-solid fa-briefcase text-success mr-2" style="font-size: 20px;"></i>
                        Actividades de Terceros
                    </h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div class="d-flex flex-row gap-2 mb-3">
                        <button type="button" class="btn btn-success btn-sm flex-fill" data-toggle="modal" data-target="#exampleModal" style="border-radius: 8px; padding: 10px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-plus-circle"></i>
                            Agregar Actividad
                        </button>
                        <button type="button" class="btn btn-warning btn-sm flex-fill" data-toggle="modal" data-target="#editModalActivity" style="border-radius: 8px; padding: 10px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-pencil"></i>
                            Editar Actividades
                        </button>
                    </div>
                    <!-- Listado de Actividades -->
                    <div style="max-height: 400px; overflow-y: auto; border-top: 1px solid #e5e7eb; padding-top: 15px;">
                        @if(isset($thirdActivity) && count($thirdActivity) > 0)
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr style="background-color: #f9fafb;">
                                        <th style="font-size: 14px; padding: 12px 10px; font-weight: 600; color: #374151;">Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($thirdActivity as $activity)
                                    <tr style="cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor=''" onclick="document.getElementById('editModalActivity').querySelector('input[name=\"nombre\"][value=\"{{ $activity->nombre }}\"]')?.closest('form').scrollIntoView({behavior: 'smooth', block: 'center'}); $('#editModalActivity').modal('show');">
                                        <td style="font-size: 14px; padding: 12px 10px; color: #1f2937;">{{ $activity->nombre }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center text-muted" style="padding: 30px; font-size: 14px;">
                                <i class="fa-solid fa-inbox mb-2" style="font-size: 32px; opacity: 0.5;"></i>
                                <p class="mb-0">No hay actividades registradas</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
          @include('third.table')
        </div>
    </div>
</div>

<!-- Modal Agregar Tercero -->
<div class="modal fade" id="modalAddThird" tabindex="-1" role="dialog" aria-labelledby="modalAddThirdLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAddThirdLabel">
                    <i class="fa-solid fa-user-plus mr-2"></i>Agregar Nuevo Tercero
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <div id="msgError" class="d-none">
                    <div class="alert alert-danger mt-2">
                        <ul id="msgErrorList">
                            <li>- La <b>cedula</b> es Obligaria</li>
                            <li>- El <b>nombre</b> es Obligatorio</li>
                        </ul>
                    </div>
                </div>
                <form id="addThirdEntry" method="POST" action="{{ route('third.entry.add') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="cedula" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                            Cédula o NIT <small class="text-success">(Obligatorio)</small>
                        </label>
                        <input type="number" name="cedula" id="cedula" class="form-control" placeholder="Ingrese cédula o NIT" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-3">
                        <label for="nombre" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                            Nombre y apellido <small class="text-success">(Obligatorio)</small>
                        </label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ingrese nombre completo" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-3">
                        <label for="direccion" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Dirección</label>
                        <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Ingrese dirección" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-3">
                        <label for="telefono" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Teléfono</label>
                        <input type="number" name="telefono" id="telefono" class="form-control" placeholder="Ingrese teléfono" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-3">
                        <label for="actividad" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                            Actividad
                        </label>
                        <select name="actividad" id="actividad" class="form-control" data-none-selected-text="Seleccione una actividad" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                            <option value="">Seleccione una actividad</option>
                            @foreach ($thirdActivity as $item)
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label for="mas" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Más descripción</label>
                        <textarea name="mas" id="mas" class="form-control" rows="4" placeholder="Agregue información adicional..." style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px; resize: vertical;"></textarea>
                    </div>
                    <div class="modal-footer px-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px; padding: 10px 20px;">Cancelar</button>
                        <button type="submit" class="btn btn-success" style="border-radius: 8px; padding: 10px 20px; font-weight: 600;">
                            <i class="fa-solid fa-plus mr-2"></i>Agregar Tercero
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Agregar Actividad</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="msgError1" class="d-none">
            <div class="alert alert-danger mt-2">
                <ul id="msgErrorList1">
                    <li>- La <b>cedula</b> es Obligaria</li>
                    <li>- El <b>nombre</b> es Obligatorio</li>
                </ul>
            </div>
          </div>
            <form id="formAddThirdActivity" method="POST" action="{{ route('third.activity.add') }}">
                @csrf
                <div class="form-group">
                  <label for="exampleInputEmail1">Nombre de la Actividad</label>
                  <input type="text" id="nombre" name="nombre" class="form-control form-control-sm number-lg">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Agregar</button>
              </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm btnClose" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal -->
<div class="modal fade" id="editModalActivity" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar Actividades</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        @foreach ($thirdActivity as $item)
          <form action="{{ route('third.activity.update',$item->id)}}" class="mt-2" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-8">
                <input type="text" name="nombre" class="form-control form-control-sm number-lg" value="{{ $item->nombre }}">
              </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-primary mr-1" title="Guardar">
                  <i class="fa-solid fa-floppy-disk"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteActivity({{ $item->id }})" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </div>
          </form>
        @endforeach
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm btnClose" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Agregar Concepto de Ingreso -->
<div class="modal fade" id="modalAddConceptEntry" tabindex="-1" role="dialog" aria-labelledby="modalAddConceptEntryLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalAddConceptEntryLabel">
          <i class="fa-solid fa-plus-circle mr-2"></i>Agregar Concepto de Ingreso
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <form id="formAddConceptEntry" method="POST" action="{{ route('third.concept.entry.add') }}">
          @csrf
          <div class="form-group">
            <label for="conceptEntryName">Nombre del Concepto <small class="text-danger">*</small></label>
            <input type="text" id="conceptEntryName" name="name" class="form-control" required placeholder="Ej: Financieros, Particulares, etc.">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="conceptEntryDebe">Debe <small class="text-danger">*</small></label>
                <select id="conceptEntryDebe" name="debe" class="form-control" required>
                  <option value="">Seleccione...</option>
                  @foreach ($debe ?? [] as $item)
                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="conceptEntryHaber">Haber <small class="text-danger">*</small></label>
                <select id="conceptEntryHaber" name="haber" class="form-control" required>
                  <option value="">Seleccione...</option>
                  @foreach ($haber ?? [] as $item)
                    <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="conceptEntryState" name="state" value="1" checked>
              <label class="form-check-label" for="conceptEntryState">
                Activo
              </label>
            </div>
          </div>
          <div class="modal-footer px-0">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="fa-solid fa-save mr-2"></i>Guardar Concepto
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Conceptos de Ingreso -->
<div class="modal fade" id="modalEditConceptEntry" tabindex="-1" role="dialog" aria-labelledby="modalEditConceptEntryLabel" aria-hidden="true" style="z-index: 1050;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="modalEditConceptEntryLabel">
          <i class="fa-solid fa-pencil mr-2"></i>Editar Conceptos de Ingreso
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
        @if(isset($conceptsEntry) && count($conceptsEntry) > 0)
          @foreach ($conceptsEntry as $item)
            <form action="{{ route('third.concept.entry.update', $item->id) }}" method="POST" class="mb-3 p-3 border rounded">
              @csrf
              @method('POST')
              <div class="form-group mb-2">
                <label class="font-weight-bold">Nombre del Concepto</label>
                <input type="text" name="name" class="form-control form-control-sm" value="{{ $item->name }}" required>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-2">
                    <label>Debe</label>
                    <select name="debe" class="form-control form-control-sm" required>
                      @foreach ($debe ?? [] as $debeItem)
                        <option value="{{ $debeItem->id }}" {{ $item->debe == $debeItem->id ? 'selected' : '' }}>
                          {{ $debeItem->cuenta }} - {{ $debeItem->nombre }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-2">
                    <label>Haber</label>
                    <select name="haber" class="form-control form-control-sm" required>
                      @foreach ($haber ?? [] as $haberItem)
                        <option value="{{ $haberItem->id }}" {{ $item->haber == $haberItem->id ? 'selected' : '' }}>
                          {{ $haberItem->cuenta }} - {{ $haberItem->nombre }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="row align-items-center">
                <div class="col-md-6">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="stateEntry{{ $item->id }}" name="state" value="1" {{ $item->state ? 'checked' : '' }}>
                    <label class="form-check-label" for="stateEntry{{ $item->id }}">
                      Activo
                    </label>
                  </div>
                </div>
                <div class="col-md-6 text-right">
                  <button type="submit" class="btn btn-sm btn-primary mr-2">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar
                  </button>
                  <button type="button" class="btn btn-sm btn-danger" onclick="deleteConceptEntry({{ $item->id }})">
                    <i class="fa-solid fa-trash mr-1"></i>Eliminar
                  </button>
                </div>
              </div>
            </form>
          @endforeach
        @else
          <div class="alert alert-info">
            <i class="fa-solid fa-info-circle mr-2"></i>No hay conceptos de ingresos registrados.
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<form id="deleteConceptEntryForm" method="POST" style="display: none;">
  @csrf
</form>

<form id="deleteActivityForm" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<form id="deleteThirdForm" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<script src="{{ asset('js/confirm-modal.js') }}"></script>
<script>
function deleteConceptEntry(id) {
  showConfirmModal(
    '¿Está seguro de eliminar este concepto de ingreso?',
    'Confirmar Eliminación',
    'Eliminar',
    'Cancelar',
    'btn-danger'
  ).then(confirmed => {
    if (confirmed) {
      const form = document.getElementById('deleteConceptEntryForm');
      form.action = '{{ url("/third/concept-entry/delete") }}/' + id;
      form.submit();
    }
  });
}

function deleteActivity(id) {
  showConfirmModal(
    '¿Está seguro de eliminar esta actividad? Esta acción no se puede deshacer.',
    'Confirmar Eliminación',
    'Eliminar',
    'Cancelar',
    'btn-danger'
  ).then(confirmed => {
    if (confirmed) {
      const form = document.getElementById('deleteActivityForm');
      form.action = '{{ url("/third/activity/delete") }}/' + id;
      form.submit();
    }
  });
}

function deleteThird(id) {
  showConfirmModal(
    '¿Está seguro de eliminar este tercero? Esta acción no se puede deshacer.',
    'Confirmar Eliminación',
    'Eliminar',
    'Cancelar',
    'btn-danger'
  ).then(confirmed => {
    if (confirmed) {
      const form = document.getElementById('deleteThirdForm');
      form.action = '{{ url("/third/entry/delete") }}/' + id;
      form.submit();
    }
  });
}

// Inicializar DataTables para la tabla de terceros
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#example3')) {
        $('#example3').DataTable().destroy();
    }
    $('#example3').DataTable({
        language: window.DataTablesSpanish || {},
        order: [[1, 'asc']],
        pageLength: 25
    });

    // Configurar bootstrap-select para mostrar texto en español
    $('#actividad').selectpicker({
        noneSelectedText: 'Seleccione una actividad',
        noneResultsText: 'No se encontraron resultados',
        countSelectedText: '{0} seleccionados'
    });

    // Limpiar formulario cuando se cierra el modal de agregar tercero
    $('#modalAddThird').on('hidden.bs.modal', function () {
        $('#addThirdEntry')[0].reset();
        $('#msgError').addClass('d-none');
        // Refrescar el selectpicker después de resetear
        $('#actividad').selectpicker('refresh');
    });

    // Manejar envío exitoso del formulario
    $('#addThirdEntry').on('submit', function(e) {
        // El formulario se enviará normalmente, pero si hay errores de validación
        // Laravel los mostrará. Si es exitoso, redirigirá con mensaje de éxito.
    });
});
</script>

@endsection

@section('page')
    @php
        echo "Gestionar Terceros";
    @endphp
@endsection
