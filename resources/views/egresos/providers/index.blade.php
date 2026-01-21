@extends('dash.app')
@section('content')
<style>
    .container-fluid.egresos-providers-page {
        padding-top: 0px !important;
    }
    
    #providersTable_wrapper {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    #providersTable_wrapper .dataTables_length,
    #providersTable_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }
    
    #providersTable_wrapper .dataTables_length label {
        color: #374151;
        font-weight: 500;
        margin-right: 10px;
    }
    
    #providersTable_wrapper .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #d1d5db;
        padding: 5px 10px;
        margin: 0 5px;
    }
    
    #providersTable_wrapper .dataTables_filter label {
        color: #374151;
        font-weight: 500;
    }
    
    #providersTable_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #d1d5db;
        padding: 8px 12px;
        margin-left: 10px;
    }
    
    #providersTable {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    #providersTable thead th {
        background-color: #f9fafb;
        color: #374151;
        font-weight: 600;
        padding: 12px 15px;
        border-bottom: 2px solid #e5e7eb;
        text-align: left;
    }
    
    #providersTable tbody td {
        padding: 12px 15px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
    }
    
    #providersTable tbody tr:hover {
        background-color: #f9fafb;
    }
    
    #providersTable_wrapper .dataTables_info {
        color: #6b7280;
        padding-top: 12px;
    }
    
    #providersTable_wrapper .dataTables_paginate {
        padding-top: 12px;
    }
    
    #providersTable_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px;
        padding: 6px 12px;
        margin: 0 2px;
        border: 1px solid #d1d5db;
    }
    
    #providersTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6 !important;
        color: white !important;
        border-color: #3b82f6 !important;
    }
    
    #providersTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
</style>

<x-error-modal :errors="$errors" />

<div class="container-fluid egresos-providers-page">
    <div class="row" style="margin-top: 0;">
        <div class="col-md-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0" style="color: #374151; font-weight: 600;">
                    <i class="fa-solid fa-building text-primary mr-2"></i>
                    Gestionar Proveedores de Egresos
                </h4>
                <div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProveedor" style="border-radius: 8px; padding: 10px 20px; font-weight: 600;">
                        <i class="fa-solid fa-plus mr-2"></i>Agregar Proveedor
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <!-- Tarjeta de Conceptos -->
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; border-radius: 12px 12px 0 0; padding: 20px;">
                    <h5 class="mb-0 d-flex align-items-center" style="color: #374151; font-weight: 600;">
                        <i class="fa-solid fa-tag text-success mr-2" style="font-size: 20px;"></i>
                        Conceptos de Egresos
                    </h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div class="d-flex flex-row gap-2 mb-3">
                        <button type="button" class="btn btn-success btn-sm flex-fill" data-toggle="modal" data-target="#modalAgregarConcepto" style="border-radius: 8px; padding: 10px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-plus-circle"></i>
                            Agregar Concepto
                        </button>
                        <button type="button" class="btn btn-warning btn-sm flex-fill" data-toggle="modal" data-target="#modalEditarConcepto" style="border-radius: 8px; padding: 10px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-pencil"></i>
                            Editar Conceptos
                        </button>
                    </div>
                    <!-- Listado de Conceptos -->
                    <div style="max-height: 400px; overflow-y: auto; border-top: 1px solid #e5e7eb; padding-top: 15px;">
                        @if(isset($concepts) && count($concepts) > 0)
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr style="background-color: #f9fafb;">
                                        <th style="font-size: 14px; padding: 12px 10px; font-weight: 600; color: #374151;">Nombre</th>
                                        <th style="font-size: 14px; padding: 12px 10px; font-weight: 600; text-align: center; color: #374151;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($concepts as $concept)
                                    <tr style="cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor=''" onclick="document.getElementById('modalEditarConcepto').querySelector('input[name=\"nombre\"][value=\"{{ $concept->nombre }}\"]')?.closest('form').scrollIntoView({behavior: 'smooth', block: 'center'}); $('#modalEditarConcepto').modal('show');">
                                        <td style="font-size: 14px; padding: 12px 10px; color: #1f2937;">{{ $concept->nombre }}</td>
                                        <td style="font-size: 14px; padding: 12px 10px; text-align: center;">
                                            @if($concept->state ?? true)
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
        </div>
        <div class="col-md-8">
            <div class="table-responsive">
                <div id="providersTable_wrapper" class="dataTables_wrapper no-footer">
                    <table id="providersTable" class="display dataTable no-footer" style="min-width: 845px" role="grid">
                        <thead>
                            <tr>
                                <th class="sorting" tabindex="0" aria-controls="providersTable" rowspan="1" colspan="1">
                                    ID ↑↓
                                </th>
                                <th class="sorting" tabindex="0" aria-controls="providersTable" rowspan="1" colspan="1">
                                    Cédula/NIT ↑↓
                                </th>
                                <th class="sorting" tabindex="0" aria-controls="providersTable" rowspan="1" colspan="1">
                                    Nombre ↑↓
                                </th>
                                <th class="sorting" tabindex="0" aria-controls="providersTable" rowspan="1" colspan="1">
                                    Dirección ↑↓
                                </th>
                                <th class="sorting" tabindex="0" aria-controls="providersTable" rowspan="1" colspan="1">
                                    Teléfono ↑↓
                                </th>
                                <th class="sorting" tabindex="0" aria-controls="providersTable" rowspan="1" colspan="1">
                                    Acciones ↑↓
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($providers as $provider)
                                <tr>
                                    <td>{{ $provider->id }}</td>
                                    <td>{{ $provider->cedula ?? '-' }}</td>
                                    <td>{{ $provider->nombre }}</td>
                                    <td>{{ $provider->direccion ?? '-' }}</td>
                                    <td>{{ $provider->telefono ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm mr-1" data-toggle="modal" data-target="#modalEditarProveedor{{ $provider->id }}" title="Editar">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('egreso.providers.destroy', $provider->id) }}" style="display: inline;" onsubmit="event.preventDefault(); showConfirmModal('¿Está seguro de eliminar este proveedor?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { this.submit(); } });">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Proveedor -->
<div class="modal fade" id="modalAgregarProveedor" tabindex="-1" role="dialog" aria-labelledby="modalAgregarProveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAgregarProveedorLabel">
                    <i class="fa-solid fa-user-plus mr-2"></i>Agregar Nuevo Proveedor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <div id="msgError" class="d-none">
                    <div class="alert alert-danger mt-2">
                        <ul id="msgErrorList">
                            <li>- El <b>nombre</b> es Obligatorio</li>
                        </ul>
                    </div>
                </div>
                <form method="POST" action="{{ route('egreso.providers.store') }}" id="formAgregarProveedor">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="cedula" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                            Cédula o NIT
                        </label>
                        <input type="text" name="cedula" id="cedula" class="form-control" placeholder="Ingrese cédula o NIT" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-3">
                        <label for="nombre" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                            Nombre <small class="text-success">(Obligatorio)</small>
                        </label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ingrese nombre del proveedor" required style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-3">
                        <label for="direccion" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Dirección</label>
                        <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Ingrese dirección" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-4">
                        <label for="telefono" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="form-control" placeholder="Ingrese teléfono" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="modal-footer px-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px; padding: 10px 20px;">Cancelar</button>
                        <button type="submit" class="btn btn-primary" style="border-radius: 8px; padding: 10px 20px; font-weight: 600;">
                            <i class="fa-solid fa-plus mr-2"></i>Agregar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Concepto -->
<div class="modal fade" id="modalAgregarConcepto" tabindex="-1" role="dialog" aria-labelledby="modalAgregarConceptoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAgregarConceptoLabel">
                    <i class="fa-solid fa-plus-circle mr-2"></i>Agregar Concepto de Egreso
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('egreso.concepts.store') }}" id="formAgregarConcepto">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="concepto_nombre" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                            Nombre del Concepto <small class="text-success">(Obligatorio)</small>
                        </label>
                        <input type="text" name="nombre" id="concepto_nombre" class="form-control" placeholder="Ej: Servicios, Materiales, etc." required style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="concepto_debe" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                                    Debe <small class="text-success">(Obligatorio)</small>
                                </label>
                                <select id="concepto_debe" name="debe" class="form-control" required style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($debe ?? [] as $item)
                                        <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="concepto_haber" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                                    Haber <small class="text-success">(Obligatorio)</small>
                                </label>
                                <select id="concepto_haber" name="haber" class="form-control" required style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($haber ?? [] as $item)
                                        <option value="{{ $item->id }}">{{ $item->cuenta }} - {{ $item->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="concepto_descripcion" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Descripción</label>
                        <textarea name="descripcion" id="concepto_descripcion" class="form-control" rows="4" placeholder="Agregue información adicional sobre el concepto..." style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px; resize: vertical;"></textarea>
                    </div>
                    <div class="form-group mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="concepto_state" name="state" value="1" checked>
                            <label class="form-check-label" for="concepto_state" style="color: #374151; font-weight: 500;">
                                Activo
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer px-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px; padding: 10px 20px;">Cancelar</button>
                        <button type="submit" class="btn btn-success" style="border-radius: 8px; padding: 10px 20px; font-weight: 600;">
                            <i class="fa-solid fa-save mr-2"></i>Guardar Concepto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Conceptos -->
<div class="modal fade" id="modalEditarConcepto" tabindex="-1" role="dialog" aria-labelledby="modalEditarConceptoLabel" aria-hidden="true" style="z-index: 1050;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarConceptoLabel">
                    <i class="fa-solid fa-pencil mr-2"></i>Editar Conceptos de Egreso
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto; padding: 25px;">
                @if(isset($concepts) && count($concepts) > 0)
                    @foreach ($concepts as $concept)
                        <form action="{{ route('egreso.concepts.update', $concept->id) }}" method="POST" class="mb-3 p-3 border rounded" style="border-radius: 8px;">
                            @csrf
                            @method('PUT')
                            <div class="form-group mb-2">
                                <label class="font-weight-bold" style="color: #374151;">Nombre del Concepto</label>
                                <input type="text" name="nombre" class="form-control form-control-sm" value="{{ $concept->nombre }}" required style="border-radius: 6px; border: 1px solid #d1d5db; padding: 8px;">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label style="color: #374151;">Debe</label>
                                        <select name="debe" class="form-control form-control-sm" required style="border-radius: 6px; border: 1px solid #d1d5db; padding: 8px;">
                                            @foreach ($debe ?? [] as $debeItem)
                                                <option value="{{ $debeItem->id }}" {{ $concept->debe == $debeItem->id ? 'selected' : '' }}>
                                                    {{ $debeItem->cuenta }} - {{ $debeItem->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label style="color: #374151;">Haber</label>
                                        <select name="haber" class="form-control form-control-sm" required style="border-radius: 6px; border: 1px solid #d1d5db; padding: 8px;">
                                            @foreach ($haber ?? [] as $haberItem)
                                                <option value="{{ $haberItem->id }}" {{ $concept->haber == $haberItem->id ? 'selected' : '' }}>
                                                    {{ $haberItem->cuenta }} - {{ $haberItem->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-2">
                                <label style="color: #374151;">Descripción</label>
                                <textarea name="descripcion" class="form-control form-control-sm" rows="2" style="border-radius: 6px; border: 1px solid #d1d5db; padding: 8px; resize: vertical;">{{ $concept->descripcion ?? '' }}</textarea>
                            </div>
                            <div class="form-group mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="stateConcept{{ $concept->id }}" name="state" value="1" {{ ($concept->state ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="stateConcept{{ $concept->id }}" style="color: #374151;">
                                        Activo
                                    </label>
                                </div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-sm btn-primary mr-2" style="border-radius: 6px;">
                                        <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteConcept({{ $concept->id }})" style="border-radius: 6px;">
                                        <i class="fa-solid fa-trash mr-1"></i>Eliminar
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endforeach
                @else
                    <div class="alert alert-info">
                        <i class="fa-solid fa-info-circle mr-2"></i>No hay conceptos de egresos registrados.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius: 8px;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Proveedor -->
@foreach ($providers as $provider)
<div class="modal fade" id="modalEditarProveedor{{ $provider->id }}" tabindex="-1" role="dialog" aria-labelledby="modalEditarProveedorLabel{{ $provider->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="modalEditarProveedorLabel{{ $provider->id }}">
                    <i class="fa-solid fa-edit mr-2"></i>Editar Proveedor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <form method="POST" action="{{ route('egreso.providers.update', $provider->id) }}" id="formEditarProveedor{{ $provider->id }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-3">
                        <label for="cedula_edit{{ $provider->id }}" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                            Cédula o NIT
                        </label>
                        <input type="text" name="cedula" id="cedula_edit{{ $provider->id }}" value="{{ $provider->cedula }}" class="form-control" placeholder="Ingrese cédula o NIT" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-3">
                        <label for="nombre_edit{{ $provider->id }}" style="color: #374151; font-weight: 500; margin-bottom: 8px;">
                            Nombre <small class="text-success">(Obligatorio)</small>
                        </label>
                        <input type="text" name="nombre" id="nombre_edit{{ $provider->id }}" value="{{ $provider->nombre }}" class="form-control" placeholder="Ingrese nombre del proveedor" required style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-3">
                        <label for="direccion_edit{{ $provider->id }}" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Dirección</label>
                        <input type="text" name="direccion" id="direccion_edit{{ $provider->id }}" value="{{ $provider->direccion }}" class="form-control" placeholder="Ingrese dirección" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="form-group mb-4">
                        <label for="telefono_edit{{ $provider->id }}" style="color: #374151; font-weight: 500; margin-bottom: 8px;">Teléfono</label>
                        <input type="text" name="telefono" id="telefono_edit{{ $provider->id }}" value="{{ $provider->telefono }}" class="form-control" placeholder="Ingrese teléfono" style="border-radius: 8px; border: 1px solid #d1d5db; padding: 10px;">
                    </div>
                    <div class="modal-footer px-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px; padding: 10px 20px;">Cancelar</button>
                        <button type="submit" class="btn btn-warning" style="border-radius: 8px; padding: 10px 20px; font-weight: 600;">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<form id="deleteConceptForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script src="{{ asset('js/confirm-modal.js') }}"></script>
<script>
function deleteConcept(id) {
    showConfirmModal(
        '¿Está seguro de eliminar este concepto de egreso?',
        'Confirmar Eliminación',
        'Eliminar',
        'Cancelar',
        'btn-danger'
    ).then(confirmed => {
        if (confirmed) {
            const form = document.getElementById('deleteConceptForm');
            form.action = '{{ url("/egresos/conceptos") }}/' + id;
            form.submit();
        }
    });
}

// Inicializar DataTables para la tabla de proveedores
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#providersTable')) {
        $('#providersTable').DataTable().destroy();
    }
    $('#providersTable').DataTable({
        language: window.DataTablesSpanish || {},
        order: [[2, 'asc']],
        pageLength: 25
    });

    // Limpiar formulario cuando se cierra el modal de agregar proveedor
    $('#modalAgregarProveedor').on('hidden.bs.modal', function () {
        $('#formAgregarProveedor')[0].reset();
        $('#msgError').addClass('d-none');
        $('#formAgregarProveedor .is-invalid').removeClass('is-invalid');
        $('#formAgregarProveedor .invalid-feedback').remove();
    });

    // Limpiar formulario cuando se cierra el modal de agregar concepto
    $('#modalAgregarConcepto').on('hidden.bs.modal', function () {
        $('#formAgregarConcepto')[0].reset();
        $('#formAgregarConcepto .is-invalid').removeClass('is-invalid');
        $('#formAgregarConcepto .invalid-feedback').remove();
    });

    // Si hay errores de validación, mostrar el modal correspondiente
    @if($errors->any())
        @if(request()->has('concepto'))
            $('#modalAgregarConcepto').modal('show');
        @else
            $('#modalAgregarProveedor').modal('show');
        @endif
    @endif
});
</script>

@endsection

@section('page')
    @php
        echo "Gestionar Proveedores de Egresos";
    @endphp
@endsection
