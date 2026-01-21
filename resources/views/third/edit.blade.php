@extends('dash.app')
@section('content')
<style>
    .container-fluid.third-edit-page {
        padding-top: 0px !important;
    }
</style>
<div class="container-fluid third-edit-page">
    <div class="row" style="margin-top: 0;">
        <div class="col-md-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0" style="color: #374151; font-weight: 600;">
                    <i class="fa-solid fa-user-edit text-warning mr-2"></i>
                    Editar Tercero
                </h4>
                <div>
                    <a href="{{ route('third.entry') }}" class="btn btn-secondary" style="border-radius: 8px; padding: 10px 20px; font-weight: 600;">
                        <i class="fa-solid fa-left-long mr-2"></i> Volver
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                <div class="card-header bg-warning text-white" style="border-bottom: 1px solid #f59e0b; border-radius: 12px 12px 0 0; padding: 20px;">

                </div>
                <div class="card-body" style="padding: 25px;">
                    <div id="msgError" class="d-none">
                        <div class="alert alert-danger mt-2" style="border-radius: 8px;">
                            <ul id="msgErrorList" class="mb-0">
                                <li>- La <b>cedula</b> es Obligaria</li>
                                <li>- El <b>nombre</b> es Obligatorio</li>
                            </ul>
                        </div>
                    </div>
                    <form id="addThirdEntry" method="POST" action="{{ route('third.entry.update',$third->id) }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="cedula" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                                Cédula o NIT <small class="text-danger">(Obligatorio)</small>
                            </label>
                            <input type="number" value="{{ $third->cedula }}" name="cedula" id="cedula" class="form-control" placeholder="Ingrese cédula o NIT" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                        </div>
                        <div class="form-group mb-3">
                            <label for="nombre" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                                Nombre y apellido <small class="text-danger">(Obligatorio)</small>
                            </label>
                            <input type="text" value="{{ $third->nombre }}" name="nombre" id="nombre" class="form-control" placeholder="Ingrese nombre completo" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                        </div>
                        <div class="form-group mb-3">
                            <label for="direccion" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Dirección</label>
                            <input type="text" value="{{ $third->direccion }}" name="direccion" id="direccion" class="form-control" placeholder="Ingrese dirección" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                        </div>
                        <div class="form-group mb-3">
                            <label for="telefono" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Teléfono</label>
                            <input type="number" value="{{ $third->telefono }}" name="telefono" id="telefono" class="form-control" placeholder="Ingrese teléfono" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                        </div>
                        <div class="form-group mb-3">
                            <label for="actividad" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Actividad</label>
                            <select id="actividad" name="actividad" class="form-control" data-none-selected-text="Seleccione una actividad" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                                <option value="">Seleccione una actividad</option>
                                @foreach ($thirdActivity as $item)
                                    <option @if ($third->actividad == $item->id) selected @endif value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label for="mas" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Más descripción</label>
                            <textarea name="mas" id="mas" class="form-control" rows="4" placeholder="Agregue información adicional..." style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px; resize: vertical;">{{ $third->mas }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100" style="border-radius: 8px; padding: 12px 20px; font-weight: 600; color: white;">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            @include('third.table')
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
                  <button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-floppy-disk"></i></button>
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

<script>
$(document).ready(function() {
    // Configurar bootstrap-select para mostrar texto en español
    $('#actividad').selectpicker({
        noneSelectedText: 'Seleccione una actividad',
        noneResultsText: 'No se encontraron resultados',
        countSelectedText: '{0} seleccionados'
    });
});
</script>

@endsection

@section('page')
    @php
        echo "Ingreso de Terceros";
    @endphp
@endsection