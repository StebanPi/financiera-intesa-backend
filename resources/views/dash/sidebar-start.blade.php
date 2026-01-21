<div class="deznav">
    <div class="deznav-scroll" style="overflow-y: auto; display: flex; flex-direction: column; height: 100%;">
        <ul class="metismenu px-3" id="menu" style="flex: 1;">
            @if(auth()->check() && auth()->user()->hasPermission('access.core'))
            <!-- Inicio -->
            <li>
                <a href="/home" aria-expanded="false">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Inicio</span>
                </a>
            </li>

            <!-- Configuración -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Configuración</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('setting.index') }}"><i class="fas fa-sliders-h"></i>Ajustes del Sistema</a></li>
                    <li><a href="{{ route('consecutive.index') }}"><i class="fas fa-list-ol"></i>Consecutivos</a></li>
                </ul>
            </li>

            <!-- Gestión Académica -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="nav-text">Gestión Académica</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('matricula.create') }}"><i class="fas fa-user-plus"></i>Matricular Estudiante</a></li>
                    <li><a href="{{ route('matricula.index') }}"><i class="fas fa-file-alt"></i>Fichas de Matrícula</a></li>
                    <li><a href="{{ route('gestion-academica.planillas.asistencia.create') }}"><i class="fas fa-clipboard-check"></i>Planilla de Asistencia</a></li>
                </ul>
            </li>

            <!-- Terceros -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
<i class="fas fa-users"></i>
                    <span class="nav-text">Terceros</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('third.entry') }}"><i class="fas fa-plus-circle"></i>Gestionar Terceros</a></li>
                    <li><a href="{{ route('third.receipts.index') }}"><i class="fas fa-list"></i>Recibos de Terceros</a></li>
                    <li><a href="/receipts/third/entry/"><i class="fas fa-file-invoice-dollar"></i>Nuevo Recibo de Terceros</a></li>
                </ul>
            </li>

            <!-- Egresos -->
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-arrow-down"></i>
                    <span class="nav-text">Egresos</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('egreso.providers.index') }}"><i class="fas fa-building"></i>Gestionar Egresos</a></li>
                    <li><a href="{{ route('egreso.receipts.index') }}"><i class="fas fa-file-invoice"></i>Recibos de Egreso</a></li>
                    <li><a href="{{ route('egreso.receipts.create') }}"><i class="fas fa-plus-circle"></i>Nuevo Recibo de Egreso</a></li>
                </ul>
            </li>
            @endif

            <!-- Contabilidad -->
            @if(auth()->check() && auth()->user()->hasPermission('access.accounting'))
            <li>
                <a href="{{ route('accounting.index') }}" aria-expanded="false">
                    <i class="fas fa-calculator"></i>
                    <span class="nav-text">Contabilidad</span>
                </a>
            </li>
            @endif

            <!-- Administración -->
            @if(auth()->check() && (auth()->user()->hasPermission('users.manage') || auth()->user()->hasPermission('roles.manage')))
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-user-shield"></i>
                    <span class="nav-text">Administración</span>
                </a>
                <ul aria-expanded="false">
                    @if(auth()->check() && auth()->user()->hasPermission('users.manage'))
                    <li><a href="{{ route('admin.users.index') }}"><i class="fas fa-users"></i>Usuarios</a></li>
                    @endif
                    @if(auth()->check() && auth()->user()->hasPermission('roles.manage'))
                    <li><a href="{{ route('admin.roles.index') }}"><i class="fas fa-user-shield"></i>Roles y Permisos</a></li>
                    @endif
                </ul>
            </li>
            @endif

            <!-- Herramientas de Mantenimiento - Solo Super Admin -->
            @if(auth()->check() && auth()->user()->hasRole('super-admin'))
            <li>
                <a href="{{ route('maintenance.index') }}" aria-expanded="false">
                    <i class="fas fa-tools"></i>
                    <span class="nav-text">Mantenimiento</span>
                </a>
            </li>
            @endif
        </ul>
        
        <!-- User Logout Section -->
        @auth
        <div class="sidebar-user-logout px-3 pb-3">
            <a href="#" 
               data-toggle="modal" 
               data-target="#modalLogout"
               class="user-logout-link">
                <div class="user-avatar">
                    @php
                        $name = Auth::user()->name;
                        $initials = '';
                        $words = explode(' ', $name);
                        if (count($words) >= 2) {
                            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                        } else {
                            $initials = strtoupper(substr($name, 0, 2));
                        }
                    @endphp
                    <span class="user-initials">{{ $initials }}</span>
                </div>
                <div class="user-info">
                    <div class="user-name">{{ $name }}</div>
                    <div class="logout-text">
                        <i class="fas fa-sign-out-alt"></i> CERRAR SESIÓN
                    </div>
                </div>
            </a>
        </div>
        @endauth
    </div>
</div>

<!-- Modal de Confirmación de Cerrar Sesión -->
@auth
<div class="modal fade" id="modalLogout" tabindex="-1" role="dialog" aria-labelledby="modalLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLogoutLabel">
                    <i class="fas fa-sign-out-alt text-danger mr-2"></i>Cerrar Sesión
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="mr-3">
                        <div class="user-avatar-modal">
                            @php
                                $name = Auth::user()->name;
                                $initials = '';
                                $words = explode(' ', $name);
                                if (count($words) >= 2) {
                                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                } else {
                                    $initials = strtoupper(substr($name, 0, 2));
                                }
                            @endphp
                            <span class="user-initials-modal">{{ $initials }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="mb-1"><strong>{{ $name }}</strong></p>
                        <p class="text-muted mb-0" style="font-size: 14px;">¿Estás seguro de que deseas cerrar sesión?</p>
                    </div>
                </div>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <small>Serás redirigido a la página de inicio de sesión.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i>Sí, cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endauth

<style>
/* Modern Sidebar Design - White theme with higher specificity */
.deznav,
[data-sidebar-style] .deznav,
[data-layout] .deznav {
    background-color: #ffffff !important; /* White background */
    border-right: 1px solid #e5e7eb !important;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05) !important;
}

/* Menu Items - Override old styles */
.deznav .metismenu,
.metismenu {
    padding-top: 30px !important; /* Separación desde la parte superior (línea del nav-header) */
    padding-bottom: 20px !important; /* Espacio inferior antes del logout */
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}

/* Ensure deznav-scroll has proper height */
.deznav-scroll {
    min-height: 0 !important;
}

.deznav .metismenu > li,
.metismenu > li {
    margin-bottom: 4px !important;
    padding: 0 12px !important;
}

.deznav .metismenu > li > a,
.metismenu > li > a {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 12px 16px !important;
    color: #374151 !important; /* Dark grey text */
    text-decoration: none !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
    position: relative !important;
    margin: 0 !important;
    background-color: transparent !important;
}

.deznav .metismenu > li > a:hover,
.metismenu > li > a:hover {
    background-color: #f3f4f6 !important; /* Light grey on hover */
    color: #111827 !important;
}

.deznav .metismenu > li > a.mm-active,
.deznav .metismenu > li.mm-active > a,
.metismenu > li > a.mm-active {
    background-color: #f3f4f6 !important;
    color: #111827 !important;
}

.deznav .metismenu > li > a i,
.metismenu > li > a i {
    width: 22px !important;
    text-align: center !important;
    font-size: 18px !important;
    flex-shrink: 0 !important;
    margin: 0 !important;
    color: #6b7280 !important; /* Medium grey for icons */
    transition: color 0.2s ease !important;
}

.deznav .metismenu > li > a:hover i,
.metismenu > li > a:hover i {
    color: #111827 !important;
}

.deznav .metismenu > li > a.mm-active i,
.deznav .metismenu > li.mm-active > a i,
.metismenu > li > a.mm-active i {
    color: #111827 !important;
}

.deznav .metismenu > li > a .nav-text,
.metismenu > li > a .nav-text {
    flex: 1 !important;
    font-size: 15.4px !important; /* Increased 10% */
    margin-top: 0 !important;
    font-weight: 500 !important;
}

/* Submenu - Override old styles */
.deznav .metismenu ul,
.metismenu ul {
    list-style: none !important;
    padding: 4px 0 4px 32px !important;
    margin: 0 !important;
    background: transparent !important;
    position: static !important;
    left: auto !important;
    right: auto !important;
    top: auto !important;
    bottom: auto !important;
    width: auto !important;
    border: none !important;
    box-shadow: none !important;
}

.deznav .metismenu ul li,
.metismenu ul li {
    margin-bottom: 2px !important;
}

.deznav .metismenu ul li a,
.metismenu ul li a {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 10px 12px 10px 44px !important; /* More left padding for submenu items */
    color: #6b7280 !important; /* Medium grey for submenu */
    text-decoration: none !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
    font-size: 14.3px !important; /* Increased 10% */
    background-color: transparent !important;
}

.deznav .metismenu ul li a:hover,
.metismenu ul li a:hover {
    background-color: #f3f4f6 !important;
    color: #111827 !important;
}

.deznav .metismenu ul li a:hover i,
.metismenu ul li a:hover i {
    color: #111827 !important;
}

.deznav .metismenu ul li a.mm-active,
.deznav .metismenu ul li.mm-active > a,
.metismenu ul li a.mm-active {
    background-color: #e0e7ff !important; /* Light blue for active submenu */
    color: #3730a3 !important;
}

.deznav .metismenu ul li a.mm-active i,
.deznav .metismenu ul li.mm-active > a i,
.metismenu ul li a.mm-active i {
    color: #3730a3 !important;
}

.deznav .metismenu ul li a i,
.metismenu ul li a i {
    font-size: 13.2px !important; /* Increased 10% */
    width: 20px !important;
    text-align: center !important;
    color: #6b7280 !important;
    flex-shrink: 0 !important;
    transition: color 0.2s ease !important;
}

/* Chevron Icon - Down pointing chevron like in the image */
.deznav .metismenu .has-arrow::after,
.deznav .metismenu > li > a.has-arrow::after,
.has-arrow::after {
    content: "\f078" !important; /* fa-chevron-down */
    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
    position: absolute !important;
    right: 16px !important;
    transition: transform 0.3s ease !important;
    font-size: 11px !important;
    color: #9ca3af !important;
    width: auto !important;
    height: auto !important;
    border: none !important;
    border-color: transparent !important;
    -webkit-transform: translateY(-50%) !important;
    transform: translateY(-50%) !important;
    top: 50% !important;
    line-height: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.deznav .metismenu .has-arrow:hover::after,
.deznav .metismenu > li > a.has-arrow:hover::after,
.has-arrow:hover::after {
    color: #111827 !important;
}

.deznav .metismenu .has-arrow[aria-expanded="true"]::after,
.deznav .metismenu .mm-active > .has-arrow::after,
.deznav .metismenu > li > a.has-arrow[aria-expanded="true"]::after,
.has-arrow[aria-expanded="true"]::after {
    transform: translateY(-50%) rotate(180deg) !important; /* Rotate 180deg to point up when expanded */
    -webkit-transform: translateY(-50%) rotate(180deg) !important;
}

.deznav .metismenu .has-arrow.ai-icon::before,
.deznav .metismenu > li > a.has-arrow.ai-icon::before,
.has-arrow.ai-icon::before {
    display: none !important;
    content: none !important;
}

/* Collapsed State (menu-toggle) */
.menu-toggle .deznav {
    width: 5rem !important;
    transition: width 0.2s ease !important;
}

.menu-toggle .deznav-scroll {
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

.menu-toggle .metismenu > li {
    padding: 0 8px !important;
}

.menu-toggle .metismenu > li > a {
    justify-content: center !important;
    padding: 12px 8px !important;
    gap: 0 !important;
}

.menu-toggle .metismenu > li > a i {
    width: auto !important;
    font-size: 20px !important;
    margin: 0 auto !important;
}

.menu-toggle .metismenu > li > a .nav-text {
    display: none !important;
}

.menu-toggle .has-arrow::after {
    display: none !important;
}

.menu-toggle .metismenu ul {
    display: none !important;
}

.menu-toggle .metismenu.px-3 {
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
    padding-top: 30px !important;
}

/* Mismos colores cuando está colapsado que cuando está normal */
.menu-toggle .metismenu > li > a:hover {
    background-color: #f3f4f6 !important;
    color: #111827 !important;
}

.menu-toggle .metismenu > li > a.mm-active {
    background-color: #f3f4f6 !important;
    color: #111827 !important;
}

/* Nav Header cuando está colapsado */
.menu-toggle .nav-header {
    width: 5rem !important;
    transition: width 0.2s ease !important;
}

.menu-toggle .nav-header .brand-logo {
    display: none !important; /* Ocultar el logo cuando está colapsado */
}

.menu-toggle .nav-header .logo-container {
    display: none !important;
}

.menu-toggle .nav-header .brand-title {
    display: none !important;
}

.menu-toggle .nav-header .logo-abbr {
    display: none !important;
}

.menu-toggle .nav-header .nav-control {
    position: absolute !important;
    right: 50% !important;
    left: auto !important;
    transform: translateX(50%) translateY(-50%) !important;
    top: 50% !important;
    width: auto !important;
}

.menu-toggle .nav-header .hamburger-btn {
    width: 36px !important;
    height: 36px !important;
    padding: 8px !important;
}

.menu-toggle .nav-header .hamburger-line {
    width: 20px !important;
}

/* Scrollbar styling for white sidebar */
.deznav-scroll::-webkit-scrollbar {
    width: 6px;
}

.deznav-scroll::-webkit-scrollbar-track {
    background: #f9fafb;
}

.deznav-scroll::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.deznav-scroll::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* User Logout Section - Bottom Left */
.sidebar-user-logout {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.user-logout-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 8px;
    text-decoration: none;
    color: #374151;
    transition: all 0.2s ease;
    border-radius: 8px;
    cursor: pointer;
}

.user-logout-link:hover {
    background-color: #fef2f2;
    text-decoration: none;
    color: #111827;
    border-left: 3px solid #dc2626;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background-color 0.2s ease;
}

.user-logout-link:hover .user-avatar {
    background-color: #e5e7eb;
}

.user-initials {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    letter-spacing: 0.3px;
}

.user-logout-link:hover .user-initials {
    color: #111827;
}

.user-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.user-name {
    font-size: 15px;
    font-weight: 500;
    color: #374151;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-logout-link:hover .user-name {
    color: #111827;
}

.logout-text {
    font-size: 12px;
    font-weight: 500;
    color: #dc2626;
    letter-spacing: 0.3px;
    line-height: 1.3;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: color 0.2s ease;
}

.logout-text i {
    font-size: 11px;
    color: #dc2626;
    transition: color 0.2s ease;
}

.user-logout-link:hover .logout-text {
    color: #b91c1c;
}

.user-logout-link:hover .logout-text i {
    color: #b91c1c;
}

/* Collapsed State - Hide user info when menu is collapsed */
.menu-toggle .sidebar-user-logout {
    display: none !important;
}

/* Responsive adjustments */
@media (max-width: 767px) {
    .sidebar-user-logout {
        padding: 15px 12px;
    }
    
    .user-avatar {
        width: 36px;
        height: 36px;
    }
    
    .user-initials {
        font-size: 13px;
    }
    
    .user-name {
        font-size: 14px;
    }
    
    .logout-text {
        font-size: 11px;
    }
}

/* Modal User Avatar */
.user-avatar-modal {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-initials-modal {
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    letter-spacing: 0.5px;
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
}

.modal-content {
    border-radius: 10px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}
</style>
