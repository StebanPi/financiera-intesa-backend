@extends('dash.app')

@section('page')
    Configuración del Sistema
@endsection

@section('content')
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa-solid fa-triangle-exclamation mr-2"></i>
    <strong>Error</strong>
    <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)
            <li><small>{{ $error }}</small></li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if (session('error_message'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa-solid fa-triangle-exclamation mr-2"></i>
    <strong>Error</strong>
    <span>{{ session('error_message') }}</span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa-solid fa-check-circle mr-2"></i>
    <strong>Éxito</strong>
    <span>{{ session('success') }}</span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif


<style>
    .setting-row {
        display: flex;
        flex-wrap: wrap;
    }
    
    .setting-col {
        display: flex;
        flex-direction: column;
    }
    
    .setting-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 24px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        max-height: 500px; /* Altura máxima fija para todas las tarjetas */
    }
    
    .setting-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    
    .setting-card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 20px;
        overflow: hidden; /* Evitar que el contenido se desborde */
    }
    
    .setting-table-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0; /* Importante para que funcione el overflow */
        overflow-y: auto; /* Scroll vertical si hay muchos elementos */
        overflow-x: hidden;
        max-height: 350px; /* Altura máxima para el área de la tabla */
    }
    
    .setting-table-wrapper::-webkit-scrollbar {
        width: 6px;
    }
    
    .setting-table-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .setting-table-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .setting-table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .setting-table {
        margin: 0;
        width: 100%;
    }
    
    /* Colores pasteles para cada sección */
    .setting-card-header.conceptos-abonos {
        background-color: #a5d8ff; /* Azul pastel */
        color: #1e3a5f;
    }
    
    .setting-card-header.elaborado-por {
        background-color: #b5ead7; /* Verde pastel */
        color: #1e5f3a;
    }
    
    .setting-card-header.cuentas-debe {
        background-color: #ffb3d9; /* Rosa pastel */
        color: #5f1e3a;
    }
    
    .setting-card-header.cuentas-haber {
        background-color: #ffe5b4; /* Amarillo pastel */
        color: #5f3a1e;
    }
    
    .setting-card-header.otros-abonos {
        background-color: #d4b3ff; /* Morado pastel */
        color: #3a1e5f;
    }
    
    .setting-card-header {
        padding: 16px 20px;
        border-bottom: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .setting-card-header i {
        font-size: 20px;
        margin-right: 10px;
    }
    
    .setting-card-header h6 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
    }
    
    
    .btn-add-item.conceptos-abonos {
        background-color: #a5d8ff;
        color: #1e3a5f;
    }
    
    .btn-add-item.elaborado-por {
        background-color: #b5ead7;
        color: #1e5f3a;
    }
    
    .btn-add-item.cuentas-debe {
        background-color: #ffb3d9;
        color: #5f1e3a;
    }
    
    .btn-add-item.cuentas-haber {
        background-color: #ffe5b4;
        color: #5f3a1e;
    }
    
    .btn-add-item.otros-abonos {
        background-color: #d4b3ff;
        color: #3a1e5f;
    }
    
    .btn-add-item {
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-add-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        opacity: 0.9;
    }
    
    .setting-table {
        margin: 0;
    }
    
    .setting-table thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .setting-table tbody tr {
        transition: all 0.2s ease;
    }
    
    .setting-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .setting-table tbody td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .badge-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-active {
        background-color: #10b981;
        color: white;
    }
    
    .badge-inactive {
        background-color: #ef4444;
        color: white;
    }
    
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header.modal-conceptos-abonos {
        background-color: #a5d8ff;
        color: #1e3a5f;
    }
    
    .modal-header.modal-elaborado-por {
        background-color: #b5ead7;
        color: #1e5f3a;
    }
    
    .modal-header.modal-cuentas-debe {
        background-color: #ffb3d9;
        color: #5f1e3a;
    }
    
    .modal-header.modal-cuentas-haber {
        background-color: #ffe5b4;
        color: #5f3a1e;
    }
    
    .modal-header.modal-otros-abonos {
        background-color: #d4b3ff;
        color: #3a1e5f;
    }
    
    .modal-header {
        border-radius: 12px 12px 0 0;
        padding: 20px;
        border-bottom: none;
    }
    
    .modal-header .modal-title {
        font-weight: 600;
        font-size: 18px;
    }
    
    .modal-header .close {
        opacity: 0.7;
        font-size: 28px;
    }
    
    .modal-header .close:hover {
        opacity: 1;
    }
    
    .modal-body {
        padding: 24px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .form-control {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 10px 14px;
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .radio-inline {
        margin-right: 20px;
        font-weight: 500;
    }
    
    .radio-inline input[type="radio"] {
        margin-right: 6px;
    }
    
    /* Estilos para botones de eliminar */
    .btn-danger {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 4px;
    }
    
    .btn-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .setting-table th:last-child,
    .setting-table td:last-child {
        width: 120px;
        text-align: center;
    }
    
    .setting-table td:last-child {
        padding: 8px;
    }
    
    .delete-form {
        display: inline-block;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 4px;
    }
    
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
    
    .gap-1 {
        gap: 4px;
    }
    
    /* Estilos para secciones */
    .settings-section {
        margin-bottom: 40px;
    }
    
    .section-header {
        border-bottom: 3px solid #e5e7eb;
        padding-bottom: 15px;
        margin-bottom: 25px;
        margin-top: 30px;
    }
    
    .section-header:first-child {
        margin-top: 0;
    }
    
    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        margin: 0;
    }
    
    .section-title i {
        margin-right: 12px;
        font-size: 28px;
    }
    
    .section-description {
        color: #6b7280;
        font-size: 14px;
        margin-top: 8px;
        margin-bottom: 0;
    }
</style>

<!-- Sección: Contabilidad -->
<div class="settings-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-calculator text-primary"></i>
            Contabilidad y Finanzas
        </h3>
        <p class="section-description">Configuración de conceptos, cuentas contables y elaboradores para recibos y abonos</p>
    </div>
    <div class="row setting-row">
    <!-- Conceptos: Abonos -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header conceptos-abonos">
                <h6><i class="fa-solid fa-tags"></i>Conceptos: Abonos</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddConceptos" class="btn btn-add-item conceptos-abonos w-100" data-toggle="modal" data-target="#ModalConceptosIngresos">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Concepto
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($conceptos as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->nombre }}
                                </td>
                                <td>
                                    @if ($item->estado == "1")
                                        <span class="badge-status badge-active">Activo</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-concepto" onclick="event.stopPropagation(); editConcepto({{ $item->id }}, {{ json_encode($item->nombre) }}, '{{ $item->estado }}', '{{ $item->orderTable }}', '{{ $item->consecutivo }}');">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('concepto.destroy', $item->id) }}" method="POST" class="d-inline delete-form" id="delete-form-concepto-{{ $item->id }}" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar este concepto?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Elaborado Por -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header elaborado-por">
                <h6><i class="fa-solid fa-user-tie"></i>Elaborado Por</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddElaborado" class="btn btn-add-item elaborado-por w-100" data-toggle="modal" data-target="#ModalElaborado">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Elaborador
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($elaborados as $index => $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->nombre }}
                                </td>
                                <td>
                                    @if ($item->estado == "1")
                                        <span class="badge-status badge-active">Activo</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-elaborado" onclick="event.stopPropagation(); editElaborado({{ $item->id }}, {{ json_encode($item->nombre) }}, '{{ $item->estado }}');">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('elaborado.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar este elaborador?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cuentas DEBE -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header cuentas-debe">
                <h6><i class="fa-solid fa-arrow-down"></i>Cuentas DEBE</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddDebe" class="btn btn-add-item cuentas-debe w-100" data-toggle="modal" data-target="#ModalDebe">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Cuenta
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cuenta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($debe as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->cuenta." - ".$item->nombre }}
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-debe" onclick="event.stopPropagation(); editDebe({{ $item->id }}, '{{ $item->cuenta }}', {{ json_encode($item->nombre) }});">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('debe.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar esta cuenta debe?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cuentas HABER -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header cuentas-haber">
                <h6><i class="fa-solid fa-arrow-up"></i>Cuentas HABER</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddHaber" class="btn btn-add-item cuentas-haber w-100" data-toggle="modal" data-target="#ModalHaber">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Cuenta
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cuenta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($haber as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->cuenta." - ".$item->nombre }}
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-haber" onclick="event.stopPropagation(); editHaber({{ $item->id }}, '{{ $item->cuenta }}', {{ json_encode($item->nombre) }});">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('haber.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar esta cuenta haber?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Conceptos: Otros Abonos -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header otros-abonos">
                <h6><i class="fa-solid fa-receipt"></i>Conceptos: Otros Abonos</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddOConceptos" class="btn btn-add-item otros-abonos w-100" data-toggle="modal" data-target="#ModalOtrosAbonos">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Concepto
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($otros as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->nombre }}
                                </td>
                                <td>
                                    @if ($item->estado == "1")
                                        <span class="badge-status badge-active">Activo</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-otros" onclick="event.stopPropagation(); editOtros({{ $item->id }}, {{ json_encode($item->nombre) }}, '{{ $item->estado }}', '{{ $item->debe ?? '' }}', '{{ $item->haber ?? '' }}');">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('otrosConceptos.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar este concepto?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
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
</div>

<!-- Sección: Gestión Académica -->
<div class="settings-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-graduation-cap text-success"></i>
            Gestión Académica
        </h3>
        <p class="section-description">Catálogos para programas académicos, horarios, grupos, docentes y módulos</p>
    </div>
    <div class="row setting-row">
    <!-- Programas -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header" style="background-color: #c5d8ff; color: #1e3a5f;">
                <h6><i class="fas fa-book"></i>Programas</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddProgram" class="btn btn-add-item w-100" style="background-color: #c5d8ff; color: #1e3a5f;" data-toggle="modal" data-target="#ModalProgram">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Programa
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($programs as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->name }}
                                </td>
                                <td>
                                    @if ($item->active)
                                        <span class="badge-status badge-active">Activo</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-program" onclick="event.stopPropagation(); editProgram({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->code ?? '' }}', '{{ $item->active ? '1' : '0' }}');">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('program.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar este programa?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay programas registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Horarios -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header" style="background-color: #ffd8c5; color: #5f3a1e;">
                <h6><i class="fas fa-clock"></i>Horarios</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddSchedule" class="btn btn-add-item w-100" style="background-color: #ffd8c5; color: #5f3a1e;" data-toggle="modal" data-target="#ModalSchedule">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Horario
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schedules as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->name }}
                                </td>
                                <td>
                                    @if ($item->active)
                                        <span class="badge-status badge-active">Activo</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-schedule" onclick="event.stopPropagation(); editSchedule({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->active ? '1' : '0' }}');">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('schedule.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar este horario?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay horarios registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Grupos -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header" style="background-color: #d5ffc5; color: #1e5f3a;">
                <h6><i class="fas fa-users"></i>Grupos</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddGroup" class="btn btn-add-item w-100" style="background-color: #d5ffc5; color: #1e5f3a;" data-toggle="modal" data-target="#ModalGroup">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Grupo
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groups as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->name }}
                                </td>
                                <td>
                                    @if ($item->active)
                                        <span class="badge-status badge-active">Activo</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-group" onclick="event.stopPropagation(); editGroup({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->active ? '1' : '0' }}');">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('group.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar este grupo?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay grupos registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Docentes -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header" style="background-color: #ffc5e5; color: #5f1e3a;">
                <h6><i class="fas fa-chalkboard-teacher"></i>Docentes</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddTeacher" class="btn btn-add-item w-100" style="background-color: #ffc5e5; color: #5f1e3a;" data-toggle="modal" data-target="#ModalTeacher">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Docente
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($teachers as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->name }}
                                </td>
                                <td>
                                    @if ($item->active)
                                        <span class="badge-status badge-active">Activo</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-teacher" onclick="event.stopPropagation(); editTeacher({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->active ? '1' : '0' }}');">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('teacher.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar este docente?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay docentes registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulos -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header" style="background-color: #e5c5ff; color: #3a1e5f;">
                <h6><i class="fas fa-book-open"></i>Módulos</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="AddModule" class="btn btn-add-item w-100" style="background-color: #e5c5ff; color: #3a1e5f;" data-toggle="modal" data-target="#ModalModule">
                    <i class="fa-solid fa-plus mr-2"></i>Agregar Módulo
                </button>
                <div class="setting-table-wrapper">
                    <table class="table setting-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($modules as $item)
                            <tr>
                                <td>
                                    <strong>#{{ $loop->iteration }}</strong>
                                </td>
                                <td>
                                    {{ $item->name }}
                                </td>
                                <td>
                                    @if ($item->active)
                                        <span class="badge-status badge-active">Activo</span>
                                    @else
                                        <span class="badge-status badge-inactive">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary btn-edit-module" onclick="event.stopPropagation(); editModule({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->code ?? '' }}', '{{ $item->active ? '1' : '0' }}');">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <form action="{{ route('module.destroy', $item->id) }}" method="POST" class="d-inline delete-form" onsubmit="event.preventDefault(); const form = this; showConfirmModal('¿Está seguro de eliminar este módulo?', 'Confirmar Eliminación', 'Eliminar', 'Cancelar', 'btn-danger').then(confirmed => { if(confirmed) { form.submit(); } }); return false;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="event.stopPropagation();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay módulos registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Sección: Configuración General -->
<div class="settings-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="fas fa-cog text-warning"></i>
            Configuración General
        </h3>
        <p class="section-description">Información general de la institución</p>
    </div>
    <div class="row setting-row">
    <!-- Configuración de Institución -->
    <div class="col-lg-4 col-md-6 mb-4 setting-col">
        <div class="card setting-card">
            <div class="setting-card-header" style="background-color: #fff4c5; color: #5f3a1e;">
                <h6><i class="fas fa-building"></i>Configuración de Institución</h6>
            </div>
            <div class="setting-card-body">
                <button type="button" id="EditInstitution" class="btn btn-add-item w-100" style="background-color: #fff4c5; color: #5f3a1e;" data-toggle="modal" data-target="#ModalInstitution">
                    <i class="fa-solid fa-edit mr-2"></i>Editar Configuración
                </button>
                <div class="info-box mt-3" style="padding: 15px; background-color: #f8f9fa; border-radius: 8px;">
                    <p><strong>Nombre:</strong> {{ $institucion->name ?? 'No configurado' }}</p>
                    @if($institucion->nit)
                        <p><strong>NIT:</strong> {{ $institucion->nit }}</p>
                    @endif
                    @if($institucion->address)
                        <p><strong>Dirección:</strong> {{ $institucion->address }}</p>
                    @endif
                    @if($institucion->phone)
                        <p><strong>Teléfono:</strong> {{ $institucion->phone }}</p>
                    @endif
                    @if($institucion->website)
                        <p><strong>Sitio Web:</strong> {{ $institucion->website }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Modal Conceptos: Abonos -->
<div class="modal fade" id="ModalConceptosIngresos" tabindex="-1" role="dialog" aria-labelledby="ModalConceptosIngresosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-conceptos-abonos">
                <h5 class="modal-title" id="ModalConceptosIngresosLabel">
                    <i class="fa-solid fa-tags mr-2"></i>Conceptos: Abonos
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('concepto.store') }}">
                    @csrf
                    <input type="hidden" id="concepto_id" name="id" value="">
                    <div class="form-group">
                        <label for="concepto_nombre">Nombre</label>
                        <input type="text" name="nombre" id="concepto_nombre" class="form-control @error('concepto_nombre') is-invalid @enderror" placeholder="Ingrese el nombre del concepto" value="{{ old('nombre') }}">
                        @error('concepto_nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="radio-inline">
                                <input id="concepto_estado1" type="radio" value="1" name="estado"> Activo
                            </label>
                            <label class="radio-inline">
                                <input id="concepto_estado2" type="radio" value="0" name="estado"> Inactivo
                            </label>
                        </div>
                    </div>
                    <div class="form-group" id="OrderBy">
                        <label>¿Orden en la tabla?</label>
                        <div>
                            @if ($count == 0)
                                <label class="radio-inline mr-3 OrderBy1la">
                                    <input id="OrderBy1" type="radio" value="1" name="orderTable"> Primero
                                </label>
                            @endif
                            <label class="radio-inline mr-3 OrderBy2la">
                                <input id="OrderBy2" checked type="radio" value="0" name="orderTable"> No importa
                            </label>
                        </div>
                    </div>
                    <div class="form-group" id="ConsecutivoP">
                        <label>¿Utiliza Consecutivo?</label>
                        <div>
                            <label class="radio-inline mr-3">
                                <input id="consecutivoSi" checked type="radio" value="1" name="consecutivo"> Sí
                            </label>
                            <label class="radio-inline mr-3">
                                <input id="consecutivoNo" type="radio" value="0" name="consecutivo"> No
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" id="resetConceptos" class="btn btn-warning btn-sm mr-2 clearButton">
                            <i class="fa-solid fa-broom mr-2"></i>Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Elaborado -->
<div class="modal fade" id="ModalElaborado" tabindex="-1" role="dialog" aria-labelledby="ModalElaboradoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-elaborado-por">
                <h5 class="modal-title" id="ModalElaboradoLabel">
                    <i class="fa-solid fa-user-tie mr-2"></i>Elaborado Por
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('elaborado.store') }}">
                    @csrf
                    <input type="hidden" id="elaborado_id" name="id" value="">
                    <div class="form-group">
                        <label for="elaborado_nombre">Nombre</label>
                        <input type="text" name="nombre" id="elaborado_nombre" class="form-control @error('elaborado_nombre') is-invalid @enderror" placeholder="Ingrese el nombre" value="{{ old('nombre') }}">
                        @error('elaborado_nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="radio-inline mr-3">
                                <input id="elaborado_estado1" type="radio" value="1" name="estado"> Activo
                            </label>
                            <label class="radio-inline mr-3">
                                <input id="elaborado_estado2" type="radio" value="0" name="estado"> Inactivo
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" id="resetElaborado" class="btn btn-warning btn-sm mr-2">
                            <i class="fa-solid fa-broom mr-2"></i>Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal DEBE -->
<div class="modal fade" id="ModalDebe" tabindex="-1" role="dialog" aria-labelledby="ModalDebeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-cuentas-debe">
                <h5 class="modal-title" id="ModalDebeLabel">
                    <i class="fa-solid fa-arrow-down mr-2"></i>Cuentas DEBE
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('debe.store') }}">
                    @csrf
                    <input type="hidden" id="debe_id" name="id" value="">
                    <div class="form-group">
                        <label for="debe_cuenta">Código</label>
                        <input name="cuenta" id="debe_cuenta" type="number" class="form-control @error('debe_cuenta') is-invalid @enderror" placeholder="Ingrese el código de la cuenta" value="{{ old('cuenta') }}">
                        @error('debe_cuenta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="debe_nombre">Nombre</label>
                        <input name="nombre" id="debe_nombre" type="text" class="form-control @error('debe_cuenta') is-invalid @enderror" placeholder="Ingrese el nombre de la cuenta" value="{{ old('nombre') }}">
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" id="resetDebe" class="btn btn-warning btn-sm mr-2">
                            <i class="fa-solid fa-broom mr-2"></i>Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal HABER -->
<div class="modal fade" id="ModalHaber" tabindex="-1" role="dialog" aria-labelledby="ModalHaberLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-cuentas-haber">
                <h5 class="modal-title" id="ModalHaberLabel">
                    <i class="fa-solid fa-arrow-up mr-2"></i>Cuentas HABER
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('haber.store') }}">
                    @csrf
                    <input type="hidden" id="haber_id" name="id" value="">
                    <div class="form-group">
                        <label for="haber_cuenta">Código</label>
                        <input type="number" id="haber_cuenta" name="cuenta" class="form-control @error('haber_cuenta') is-invalid @enderror" placeholder="Ingrese el código de la cuenta" value="{{ old('cuenta') }}">
                        @error('haber_cuenta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="haber_nombre">Nombre</label>
                        <input type="text" id="haber_nombre" name="nombre" class="form-control @error('haber_cuenta') is-invalid @enderror" placeholder="Ingrese el nombre de la cuenta" value="{{ old('nombre') }}">
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" id="resetHaber" class="btn btn-warning btn-sm mr-2">
                            <i class="fa-solid fa-broom mr-2"></i>Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Otros Abonos -->
<div class="modal fade" id="ModalOtrosAbonos" tabindex="-1" role="dialog" aria-labelledby="ModalOtrosAbonosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-otros-abonos">
                <h5 class="modal-title" id="ModalOtrosAbonosLabel">
                    <i class="fa-solid fa-receipt mr-2"></i>Conceptos: Otros Abonos
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('otrosConceptos.store') }}">
                    @csrf
                    <input type="hidden" id="Oconcepto_id" name="id" value="">
                    <div class="form-group">
                        <label for="Oconcepto_nombre">Nombre</label>
                        <input type="text" name="nombre" id="Oconcepto_nombre" class="form-control @error('otros_concepto_nombre') is-invalid @enderror" placeholder="Ingrese el nombre del concepto" value="{{ old('nombre') }}">
                        @error('otros_concepto_nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="Oconcepto_debe">Debe <span class="text-danger">*</span></label>
                        <select name="debe" id="Oconcepto_debe" class="form-control @error('debe') is-invalid @enderror" required>
                            <option value="">Seleccione una cuenta</option>
                            @foreach ($debe as $item)
                                <option value="{{ $item->id }}" {{ old('debe') == $item->id ? 'selected' : '' }}>
                                    {{ $item->cuenta }} - {{ $item->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('debe')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="Oconcepto_haber">Haber <span class="text-danger">*</span></label>
                        <select name="haber" id="Oconcepto_haber" class="form-control @error('haber') is-invalid @enderror" required>
                            <option value="">Seleccione una cuenta</option>
                            @foreach ($haber as $item)
                                <option value="{{ $item->id }}" {{ old('haber') == $item->id ? 'selected' : '' }}>
                                    {{ $item->cuenta }} - {{ $item->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('haber')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="radio-inline mr-3">
                                <input id="Oconcepto_estado1" type="radio" value="1" name="estado"> Activo
                            </label>
                            <label class="radio-inline mr-3">
                                <input id="Oconcepto_estado2" type="radio" value="0" name="estado"> Inactivo
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" id="resetOConceptos" class="btn btn-warning btn-sm mr-2 clearButton">
                            <i class="fa-solid fa-broom mr-2"></i>Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Programa -->
<div class="modal fade" id="ModalProgram" tabindex="-1" role="dialog" aria-labelledby="ModalProgramLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #c5d8ff; color: #1e3a5f;">
                <h5 class="modal-title" id="ModalProgramLabel">
                    <i class="fas fa-book mr-2"></i>Programa
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('program.store') }}">
                    @csrf
                    <input type="hidden" id="program_id" name="id" value="">
                    <div class="form-group">
                        <label for="program_name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="program_name" class="form-control @error('program_name') is-invalid @enderror" placeholder="Ingrese el nombre del programa" required value="{{ old('name') }}">
                        @error('program_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="program_code">Código</label>
                        <input type="text" name="code" id="program_code" class="form-control" placeholder="Ingrese el código (opcional)">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="radio-inline mr-3">
                                <input id="program_active1" type="radio" value="1" name="active" checked> Activo
                            </label>
                            <label class="radio-inline mr-3">
                                <input id="program_active2" type="radio" value="0" name="active"> Inactivo
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Horario -->
<div class="modal fade" id="ModalSchedule" tabindex="-1" role="dialog" aria-labelledby="ModalScheduleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #ffd8c5; color: #5f3a1e;">
                <h5 class="modal-title" id="ModalScheduleLabel">
                    <i class="fas fa-clock mr-2"></i>Horario
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('schedule.store') }}">
                    @csrf
                    <input type="hidden" id="schedule_id" name="id" value="">
                    <div class="form-group">
                        <label for="schedule_name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="schedule_name" class="form-control @error('schedule_name') is-invalid @enderror" placeholder="Ingrese el nombre del horario" required value="{{ old('name') }}">
                        @error('schedule_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="radio-inline mr-3">
                                <input id="schedule_active1" type="radio" value="1" name="active" checked> Activo
                            </label>
                            <label class="radio-inline mr-3">
                                <input id="schedule_active2" type="radio" value="0" name="active"> Inactivo
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Grupo -->
<div class="modal fade" id="ModalGroup" tabindex="-1" role="dialog" aria-labelledby="ModalGroupLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #d5ffc5; color: #1e5f3a;">
                <h5 class="modal-title" id="ModalGroupLabel">
                    <i class="fas fa-users mr-2"></i>Grupo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('group.store') }}">
                    @csrf
                    <input type="hidden" id="group_id" name="id" value="">
                    <div class="form-group">
                        <label for="group_name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="group_name" class="form-control @error('group_name') is-invalid @enderror" placeholder="Ingrese el nombre del grupo" required value="{{ old('name') }}">
                        @error('group_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="radio-inline mr-3">
                                <input id="group_active1" type="radio" value="1" name="active" checked> Activo
                            </label>
                            <label class="radio-inline mr-3">
                                <input id="group_active2" type="radio" value="0" name="active"> Inactivo
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Docente -->
<div class="modal fade" id="ModalTeacher" tabindex="-1" role="dialog" aria-labelledby="ModalTeacherLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #ffc5e5; color: #5f1e3a;">
                <h5 class="modal-title" id="ModalTeacherLabel">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>Docente
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('teacher.store') }}">
                    @csrf
                    <input type="hidden" id="teacher_id" name="id" value="">
                    <div class="form-group">
                        <label for="teacher_name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="teacher_name" class="form-control @error('teacher_name') is-invalid @enderror" placeholder="Ingrese el nombre del docente" required value="{{ old('name') }}">
                        @error('teacher_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="radio-inline mr-3">
                                <input id="teacher_active1" type="radio" value="1" name="active" checked> Activo
                            </label>
                            <label class="radio-inline mr-3">
                                <input id="teacher_active2" type="radio" value="0" name="active"> Inactivo
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Módulo -->
<div class="modal fade" id="ModalModule" tabindex="-1" role="dialog" aria-labelledby="ModalModuleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #e5c5ff; color: #3a1e5f;">
                <h5 class="modal-title" id="ModalModuleLabel">
                    <i class="fas fa-book-open mr-2"></i>Módulo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('module.store') }}">
                    @csrf
                    <input type="hidden" id="module_id" name="id" value="">
                    <div class="form-group">
                        <label for="module_name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="module_name" class="form-control @error('module_name') is-invalid @enderror" placeholder="Ingrese el nombre del módulo" required value="{{ old('name') }}">
                        @error('module_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="module_code">Código</label>
                        <input type="text" name="code" id="module_code" class="form-control" placeholder="Ingrese el código (opcional)">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <div>
                            <label class="radio-inline mr-3">
                                <input id="module_active1" type="radio" value="1" name="active" checked> Activo
                            </label>
                            <label class="radio-inline mr-3">
                                <input id="module_active2" type="radio" value="0" name="active"> Inactivo
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Institución -->
<div class="modal fade" id="ModalInstitution" tabindex="-1" role="dialog" aria-labelledby="ModalInstitutionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff4c5; color: #5f3a1e;">
                <h5 class="modal-title" id="ModalInstitutionLabel">
                    <i class="fas fa-building mr-2"></i>Configuración de Institución
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('institution.store') }}" enctype="multipart/form-data">
                    @csrf
                    <h6 class="mb-3" style="color: #495057; font-weight: 600;">Información General</h6>
                    <div class="form-group">
                        <label for="institution_name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="institution_name" class="form-control" value="{{ $institucion->name ?? '' }}" placeholder="Ingrese el nombre de la institución" required>
                        <small class="form-text text-muted">Nombre principal de la institución</small>
                    </div>
                    <div class="form-group">
                        <label for="institution_subtitulo">Nombre Comercial / Subtítulo</label>
                        <input type="text" name="institucion_subtitulo" id="institution_subtitulo" class="form-control" value="{{ $institucion->institucion_subtitulo ?? '' }}" placeholder="Ej: INSTITUTO TÉCNICO DEL SABER o INTESA">
                        <small class="form-text text-muted">Aparece en recibos POS (debajo del logo)</small>
                    </div>
                    <div class="form-group">
                        <label for="institution_sede">Sede</label>
                        <input type="text" name="sede" id="institution_sede" class="form-control" value="{{ $institucion->sede ?? '' }}" placeholder="Ej: Sede Barrancabermeja">
                        <small class="form-text text-muted">Sede que aparece en recibos POS</small>
                    </div>
                    <div class="form-group">
                        <label for="institution_logo_path">Logo</label>
                        <input type="file" name="logo" id="institution_logo_path" class="form-control-file" accept="image/*">
                        @if($institucion->logo_path)
                            <small class="form-text text-muted">Logo actual: {{ $institucion->logo_path }}</small>
                        @else
                            <small class="form-text text-muted">Ruta del logo (ej: dimages/LogoIntesa.png o uploads/logo.png)</small>
                        @endif
                        <div class="mt-2">
                            <input type="text" name="logo_path" id="institution_logo_path_text" class="form-control" value="{{ $institucion->logo_path ?? '' }}" placeholder="O ingrese la ruta manualmente (ej: dimages/LogoIntesa.png)">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="institution_nit">NIT</label>
                        <input type="text" name="nit" id="institution_nit" class="form-control" value="{{ $institucion->nit ?? '' }}" placeholder="Ingrese el NIT">
                    </div>
                    
                    <hr style="margin: 20px 0; border-top: 1px solid #dee2e6;">
                    <h6 class="mb-3" style="color: #495057; font-weight: 600;">Contacto</h6>
                    <div class="form-group">
                        <label for="institution_address">Dirección</label>
                        <textarea name="address" id="institution_address" class="form-control" rows="2" placeholder="Ingrese la dirección">{{ $institucion->address ?? '' }}</textarea>
                        <small class="form-text text-muted">Aparece en recibos POS</small>
                    </div>
                    <div class="form-group">
                        <label for="institution_phone">Teléfono 1</label>
                        <input type="text" name="phone" id="institution_phone" class="form-control" value="{{ $institucion->phone ?? '' }}" placeholder="Ingrese el teléfono principal">
                        <small class="form-text text-muted">Teléfono principal que aparece en recibos POS</small>
                    </div>
                    <div class="form-group">
                        <label for="institution_telefono2">Teléfono 2</label>
                        <input type="text" name="telefono2" id="institution_telefono2" class="form-control" value="{{ $institucion->telefono2 ?? '' }}" placeholder="Ej: 322 364 7768">
                        <small class="form-text text-muted">Segundo teléfono (opcional)</small>
                    </div>
                    <div class="form-group">
                        <label for="institution_telefono3">Teléfono 3</label>
                        <input type="text" name="telefono3" id="institution_telefono3" class="form-control" value="{{ $institucion->telefono3 ?? '' }}" placeholder="Ej: 318 305 3937">
                        <small class="form-text text-muted">Tercer teléfono (opcional)</small>
                    </div>
                    <div class="form-group">
                        <label for="institution_email">Email</label>
                        <input type="email" name="email" id="institution_email" class="form-control" value="{{ $institucion->email ?? '' }}" placeholder="Ingrese el email">
                    </div>
                    <div class="form-group">
                        <label for="institution_website">Sitio Web</label>
                        <input type="text" name="website" id="institution_website" class="form-control" value="{{ $institucion->website ?? '' }}" placeholder="Ej: www.institutointesa.edu.co">
                    </div>
                    
                    <hr style="margin: 20px 0; border-top: 1px solid #dee2e6;">
                    <h6 class="mb-3" style="color: #495057; font-weight: 600;">Configuración del Footer de PDF</h6>
                    
                    <div class="form-group">
                        <label for="institution_footer_licencia_texto">Texto de Licencia (Footer PDF)</label>
                        <textarea name="footer_licencia_texto" id="institution_footer_licencia_texto" class="form-control" rows="2" placeholder="Ej: Licencia de Funcionamiento según Resolución No. 3021 del 15 de diciembre de 2015">{{ $institucion->footer_licencia_texto ?? '' }}</textarea>
                        <small class="form-text text-muted">Este texto aparecerá en la primera línea del footer del PDF</small>
                    </div>
                    <div class="form-group">
                        <label for="institution_footer_ciudad">Ciudad (Footer PDF)</label>
                        <input type="text" name="footer_ciudad" id="institution_footer_ciudad" class="form-control" value="{{ $institucion->footer_ciudad ?? '' }}" placeholder="Ej: Barrancabermeja - Santander">
                        <small class="form-text text-muted">La fecha se añadirá automáticamente después de este texto</small>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="footer_mostrar_ubicacion_fecha" id="institution_footer_mostrar_ubicacion_fecha" class="form-check-input" value="1" {{ (isset($institucion->footer_mostrar_ubicacion_fecha) && $institucion->footer_mostrar_ubicacion_fecha) ? 'checked' : '' }}>
                            <label class="form-check-label" for="institution_footer_mostrar_ubicacion_fecha">
                                Mostrar ubicación y fecha en el footer
                            </label>
                        </div>
                        <small class="form-text text-muted">Si está desactivado, solo se mostrará el texto de licencia</small>
                    </div>
                    
                    <hr style="margin: 20px 0; border-top: 1px solid #dee2e6;">
                    <h6 class="mb-3" style="color: #495057; font-weight: 600;">Configuración de Recibos POS</h6>
                    
                    <div class="form-group">
                        <label for="institution_footer_firma">Firma del Footer (Recibos POS)</label>
                        <input type="text" name="footer_firma" id="institution_footer_firma" class="form-control" value="{{ $institucion->footer_firma ?? '' }}" placeholder="Ej: by IngELopez">
                        <small class="form-text text-muted">Texto que aparece en la línea secundaria del recibo POS (debajo de teléfonos)</small>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Conceptos
    $(document).on('click', '.clickconcepto', function() {
        var id = $(this).attr('id_attr');
        var nombre = $(this).attr('nombre');
        var estado = $(this).attr('estado');
        var orderTable = $(this).attr('orderTable');
        var consecutivo = $(this).attr('consecutivo');
        
        $('#concepto_id').val(id || '');
        $('#concepto_nombre').val(nombre || '');
        
        // Resetear radios antes de establecer el valor
        $('#concepto_estado1, #concepto_estado2').prop('checked', false);
        if(estado == '1') {
            $('#concepto_estado1').prop('checked', true);
        } else {
            $('#concepto_estado2').prop('checked', true);
        }
        
        $('#OrderBy1, #OrderBy2').prop('checked', false);
        if(orderTable == '1') {
            $('#OrderBy1').prop('checked', true);
        } else {
            $('#OrderBy2').prop('checked', true);
        }
        
        $('#consecutivoSi, #consecutivoNo').prop('checked', false);
        if(consecutivo == '1') {
            $('#consecutivoSi').prop('checked', true);
        } else {
            $('#consecutivoNo').prop('checked', true);
        }
    });

    // Elaborados
    $(document).on('click', '.clickelaborado', function() {
        var id = $(this).attr('id_attr');
        var nombre = $(this).attr('nombre');
        var estado = $(this).attr('estado');
        
        $('#elaborado_id').val(id || '');
        $('#elaborado_nombre').val(nombre || '');
        
        // Resetear radios antes de establecer el valor
        $('#elaborado_estado1, #elaborado_estado2').prop('checked', false);
        if(estado == '1') {
            $('#elaborado_estado1').prop('checked', true);
        } else {
            $('#elaborado_estado2').prop('checked', true);
        }
    });

    // Debe
    $(document).on('click', '.clickdebe', function() {
        var id = $(this).attr('id_attr');
        var cuenta = $(this).attr('cuenta');
        var nombre = $(this).attr('nombre');
        
        $('#debe_id').val(id || '');
        $('#debe_cuenta').val(cuenta || '');
        $('#debe_nombre').val(nombre || '');
    });

    // Haber
    $(document).on('click', '.clickhaber', function() {
        var id = $(this).attr('id_attr');
        var cuenta = $(this).attr('cuenta');
        var nombre = $(this).attr('nombre');
        
        $('#haber_id').val(id || '');
        $('#haber_cuenta').val(cuenta || '');
        $('#haber_nombre').val(nombre || '');
    });

    // Programas
    $(document).on('click', '.clickProgram', function() {
        var id = $(this).attr('id_attr');
        var nombre = $(this).attr('nombre');
        var code = $(this).attr('code') || '';
        var active = $(this).attr('active');
        
        $('#program_id').val(id || '');
        $('#program_name').val(nombre || '');
        $('#program_code').val(code || '');
        
        // Resetear radios antes de establecer el valor
        $('#program_active1, #program_active2').prop('checked', false);
        if(active == '1') {
            $('#program_active1').prop('checked', true);
        } else {
            $('#program_active2').prop('checked', true);
        }
    });

    // Horarios
    $(document).on('click', '.clickSchedule', function() {
        var id = $(this).attr('id_attr');
        var nombre = $(this).attr('nombre');
        var active = $(this).attr('active');
        
        $('#schedule_id').val(id || '');
        $('#schedule_name').val(nombre || '');
        
        // Resetear radios antes de establecer el valor
        $('#schedule_active1, #schedule_active2').prop('checked', false);
        if(active == '1') {
            $('#schedule_active1').prop('checked', true);
        } else {
            $('#schedule_active2').prop('checked', true);
        }
    });

    // Grupos
    $(document).on('click', '.clickGroup', function() {
        var id = $(this).attr('id_attr');
        var nombre = $(this).attr('nombre');
        var active = $(this).attr('active');
        
        $('#group_id').val(id || '');
        $('#group_name').val(nombre || '');
        
        // Resetear radios antes de establecer el valor
        $('#group_active1, #group_active2').prop('checked', false);
        if(active == '1') {
            $('#group_active1').prop('checked', true);
        } else {
            $('#group_active2').prop('checked', true);
        }
    });

    // Docentes
    $(document).on('click', '.clickTeacher', function() {
        var id = $(this).attr('id_attr');
        var nombre = $(this).attr('nombre');
        var active = $(this).attr('active');
        
        $('#teacher_id').val(id || '');
        $('#teacher_name').val(nombre || '');
        
        // Resetear radios antes de establecer el valor
        $('#teacher_active1, #teacher_active2').prop('checked', false);
        if(active == '1') {
            $('#teacher_active1').prop('checked', true);
        } else {
            $('#teacher_active2').prop('checked', true);
        }
    });

    // Módulos
    $(document).on('click', '.clickModule', function() {
        var id = $(this).attr('id_attr');
        var nombre = $(this).attr('nombre');
        var code = $(this).attr('code') || '';
        var active = $(this).attr('active');
        
        $('#module_id').val(id || '');
        $('#module_name').val(nombre || '');
        $('#module_code').val(code || '');
        
        // Resetear radios antes de establecer el valor
        $('#module_active1, #module_active2').prop('checked', false);
        if(active == '1') {
            $('#module_active1').prop('checked', true);
        } else {
            $('#module_active2').prop('checked', true);
        }
    });

    // Otros Conceptos
    $(document).on('click', '.clickOtros', function() {
        var id = $(this).attr('id_attr');
        var nombre = $(this).attr('nombre');
        var estado = $(this).attr('estado');
        var debe = $(this).attr('debe') || '';
        var haber = $(this).attr('haber') || '';
        
        $('#Oconcepto_id').val(id || '');
        $('#Oconcepto_nombre').val(nombre || '');
        $('#Oconcepto_debe').val(debe || '');
        $('#Oconcepto_haber').val(haber || '');
        
        // Resetear radios antes de establecer el valor
        $('#Oconcepto_estado1, #Oconcepto_estado2').prop('checked', false);
        if(estado == '1') {
            $('#Oconcepto_estado1').prop('checked', true);
        } else {
            $('#Oconcepto_estado2').prop('checked', true);
        }
    });

    // Limpiar formularios al hacer clic en "Agregar"
    $('#AddConceptos, #AddElaborado, #AddDebe, #AddHaber, #AddProgram, #AddSchedule, #AddGroup, #AddTeacher, #AddModule, #AddOConceptos').on('click', function() {
        var modalId = $(this).data('target');
        $(modalId + ' input[type="text"], ' + modalId + ' input[type="email"], ' + modalId + ' input[type="number"], ' + modalId + ' input[type="hidden"], ' + modalId + ' select, ' + modalId + ' textarea').val('');
        $(modalId + ' input[type="radio"][value="1"]').prop('checked', true);
        if(modalId === '#ModalOtrosAbonos') {
            $('#Oconcepto_debe').val('');
            $('#Oconcepto_haber').val('');
        }
        if(modalId === '#ModalConceptosIngresos') {
            $('#OrderBy2').prop('checked', true);
            $('#consecutivoSi').prop('checked', true);
        }
    });
});

// Funciones para editar desde botones
function editConcepto(id, nombre, estado, orderTable, consecutivo) {
    $('#concepto_id').val(id || '');
    $('#concepto_nombre').val(nombre || '');
    
    // Resetear radios antes de establecer el valor
    $('#concepto_estado1, #concepto_estado2').prop('checked', false);
    if(estado == '1') {
        $('#concepto_estado1').prop('checked', true);
    } else {
        $('#concepto_estado2').prop('checked', true);
    }
    
    // Resetear radios de orderTable
    $('#OrderBy1, #OrderBy2').prop('checked', false);
    if(orderTable == '1') {
        $('#OrderBy1').prop('checked', true);
    } else {
        $('#OrderBy2').prop('checked', true);
    }
    
    // Resetear radios de consecutivo
    $('#consecutivoSi, #consecutivoNo').prop('checked', false);
    if(consecutivo == '1') {
        $('#consecutivoSi').prop('checked', true);
    } else {
        $('#consecutivoNo').prop('checked', true);
    }
    
    $('#ModalConceptosIngresos').modal('show');
}

function editElaborado(id, nombre, estado) {
    $('#elaborado_id').val(id || '');
    $('#elaborado_nombre').val(nombre || '');
    
    // Resetear radios antes de establecer el valor
    $('#elaborado_estado1, #elaborado_estado2').prop('checked', false);
    if(estado == '1') {
        $('#elaborado_estado1').prop('checked', true);
    } else {
        $('#elaborado_estado2').prop('checked', true);
    }
    
    $('#ModalElaborado').modal('show');
}

function editDebe(id, cuenta, nombre) {
    $('#debe_id').val(id || '');
    $('#debe_cuenta').val(cuenta || '');
    $('#debe_nombre').val(nombre || '');
    $('#ModalDebe').modal('show');
}

function editHaber(id, cuenta, nombre) {
    $('#haber_id').val(id || '');
    $('#haber_cuenta').val(cuenta || '');
    $('#haber_nombre').val(nombre || '');
    $('#ModalHaber').modal('show');
}

function editOtros(id, nombre, estado, debe, haber) {
    $('#Oconcepto_id').val(id || '');
    $('#Oconcepto_nombre').val(nombre || '');
    $('#Oconcepto_debe').val(debe || '');
    $('#Oconcepto_haber').val(haber || '');
    
    // Resetear radios antes de establecer el valor
    $('#Oconcepto_estado1, #Oconcepto_estado2').prop('checked', false);
    if(estado == '1') {
        $('#Oconcepto_estado1').prop('checked', true);
    } else {
        $('#Oconcepto_estado2').prop('checked', true);
    }
    
    $('#ModalOtrosAbonos').modal('show');
}

function editProgram(id, nombre, code, active) {
    $('#program_id').val(id || '');
    $('#program_name').val(nombre || '');
    $('#program_code').val(code || '');
    
    // Resetear radios antes de establecer el valor
    $('#program_active1, #program_active2').prop('checked', false);
    if(active == '1') {
        $('#program_active1').prop('checked', true);
    } else {
        $('#program_active2').prop('checked', true);
    }
    
    $('#ModalProgram').modal('show');
}

function editSchedule(id, nombre, active) {
    $('#schedule_id').val(id || '');
    $('#schedule_name').val(nombre || '');
    
    // Resetear radios antes de establecer el valor
    $('#schedule_active1, #schedule_active2').prop('checked', false);
    if(active == '1') {
        $('#schedule_active1').prop('checked', true);
    } else {
        $('#schedule_active2').prop('checked', true);
    }
    
    $('#ModalSchedule').modal('show');
}

function editGroup(id, nombre, active) {
    $('#group_id').val(id || '');
    $('#group_name').val(nombre || '');
    
    // Resetear radios antes de establecer el valor
    $('#group_active1, #group_active2').prop('checked', false);
    if(active == '1') {
        $('#group_active1').prop('checked', true);
    } else {
        $('#group_active2').prop('checked', true);
    }
    
    $('#ModalGroup').modal('show');
}

function editTeacher(id, nombre, active) {
    $('#teacher_id').val(id || '');
    $('#teacher_name').val(nombre || '');
    
    // Resetear radios antes de establecer el valor
    $('#teacher_active1, #teacher_active2').prop('checked', false);
    if(active == '1') {
        $('#teacher_active1').prop('checked', true);
    } else {
        $('#teacher_active2').prop('checked', true);
    }
    
    $('#ModalTeacher').modal('show');
}

function editModule(id, nombre, code, active) {
    $('#module_id').val(id || '');
    $('#module_name').val(nombre || '');
    $('#module_code').val(code || '');
    
    // Resetear radios antes de establecer el valor
    $('#module_active1, #module_active2').prop('checked', false);
    if(active == '1') {
        $('#module_active1').prop('checked', true);
    } else {
        $('#module_active2').prop('checked', true);
    }
    
    $('#ModalModule').modal('show');
}
</script>

<script src="{{ asset('js/confirm-modal.js') }}"></script>

@endsection
