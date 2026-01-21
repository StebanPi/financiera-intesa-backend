@extends('dash.app')

@section('page')
    Acceso Denegado
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 text-center">
            <div class="error-content">
                <div class="error-code mb-4">
                    <h1 class="display-1 font-weight-bold text-danger">403</h1>
                </div>
                <div class="error-message mb-4">
                    <h2 class="h3 mb-3">Acceso Denegado</h2>
                    <p class="text-muted mb-4">
                        No tienes permiso para acceder a esta sección del sistema.
                    </p>
                    <p class="text-muted small">
                        Si crees que esto es un error, contacta al administrador del sistema.
                    </p>
                </div>
                <div class="error-actions">
                    <a href="{{ route('home') }}" class="btn btn-primary mr-2">
                        <i class="fas fa-home mr-2"></i>Ir al Inicio
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Volver Atrás
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
