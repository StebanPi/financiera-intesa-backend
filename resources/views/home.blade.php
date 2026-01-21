@extends('dash.app')

@section('page')
    Inicio
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Bienvenido de nuevo, {{ auth()->user()->name }}!</h2>
                    <p class="text-muted mb-0">Accesos directos a las secciones principales</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if(auth()->check() && auth()->user()->hasPermission('access.core'))
        <!-- Matricular Estudiante -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('matricula.create') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-success-light mb-3">
                            <i class="fas fa-user-plus text-success"></i>
                        </div>
                        <h5 class="card-title mb-2">Matricular Estudiante</h5>
                        <p class="text-muted small mb-0">Nuevo registro</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Estudiantes -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('matricula.index') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-success-light mb-3">
                            <i class="fas fa-user text-success"></i>
                        </div>
                        <h5 class="card-title mb-2">Estudiantes</h5>
                        <p class="text-muted small mb-0">Gestión de estudiantes</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Terceros -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('third.entry') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-info-light mb-3">
                            <i class="fas fa-users text-info"></i>
                        </div>
                        <h5 class="card-title mb-2">Terceros</h5>
                        <p class="text-muted small mb-0">Gestión de terceros</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Egresos -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('egreso.receipts.index') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-warning-light mb-3">
                            <i class="fas fa-arrow-down text-warning"></i>
                        </div>
                        <h5 class="card-title mb-2">Egresos</h5>
                        <p class="text-muted small mb-0">Gestión de egresos</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Consecutivos -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('consecutive.index') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-secondary-light mb-3">
                            <i class="fas fa-list-ol text-secondary"></i>
                        </div>
                        <h5 class="card-title mb-2">Consecutivos</h5>
                        <p class="text-muted small mb-0">Gestión de consecutivos</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Nuevo Recibo de Egreso -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('egreso.receipts.create') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-warning-light mb-3">
                            <i class="fas fa-file-invoice text-warning"></i>
                        </div>
                        <h5 class="card-title mb-2">Nuevo Recibo</h5>
                        <p class="text-muted small mb-0">Recibo de egreso</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Configuración / Otros -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('setting.index') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-secondary-light mb-3">
                            <i class="fas fa-cog text-secondary"></i>
                        </div>
                        <h5 class="card-title mb-2">Configuración</h5>
                        <p class="text-muted small mb-0">Ajustes del sistema</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        @if(auth()->check() && auth()->user()->hasPermission('access.accounting'))
        <!-- Contabilidad -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('accounting.index') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-danger-light mb-3">
                            <i class="fas fa-calculator text-danger"></i>
                        </div>
                        <h5 class="card-title mb-2">Contabilidad</h5>
                        <p class="text-muted small mb-0">Reportes contables</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        @if(auth()->check() && (auth()->user()->hasPermission('users.manage') || auth()->user()->hasPermission('roles.manage')))
        <!-- Administración -->
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
            <a href="{{ route('admin.users.index') }}" class="shortcut-card">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="shortcut-icon bg-primary-light mb-3">
                            <i class="fas fa-user-shield text-primary"></i>
                        </div>
                        <h5 class="card-title mb-2">Administración</h5>
                        <p class="text-muted small mb-0">Usuarios y roles</p>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>
</div>

<style>
.shortcut-card {
    text-decoration: none;
    color: inherit;
    display: block;
}

.shortcut-card:hover {
    text-decoration: none;
    color: inherit;
}

/* Bordes redondeados para las tarjetas */
.container-fluid .shortcut-card .card,
.container-fluid .shortcut-card .hover-lift,
.container-fluid .shortcut-card .card.hover-lift,
.shortcut-card .card.h-100,
.shortcut-card .card.border-0,
.shortcut-card .card.shadow-sm {
    border-radius: 12px !important;
    overflow: hidden !important;
}

.hover-lift {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.shortcut-icon {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.3s ease;
}

.hover-lift:hover .shortcut-icon {
    transform: scale(1.1);
}

.shortcut-icon i {
    font-size: 28px;
}

.bg-primary-light {
    background-color: rgba(47, 76, 221, 0.1);
}

.bg-success-light {
    background-color: rgba(40, 167, 69, 0.1);
}

.bg-info-light {
    background-color: rgba(23, 162, 184, 0.1);
}

.bg-warning-light {
    background-color: rgba(255, 193, 7, 0.1);
}

.bg-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
}

.bg-secondary-light {
    background-color: rgba(108, 117, 125, 0.1);
}

.text-primary {
    color: #2f4cdd !important;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.text-secondary {
    color: #6c757d !important;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.shortcut-card:hover .card-title {
    color: #111827;
}
</style>
@endsection
