@extends('login.app')

@section('content')

<div class="authincation h-100" style="background-image: url('https://institutointesa.edu.co/login2/images/MesaIntesa7.png')">
    <div class="container h-100">
        <div class="row justify-content-center h-100 align-items-center">
            <div class="col-12 col-lg-11 col-xl-10">
                <div class="main-login-card">
                    <div class="row g-0">
                        <!-- Panel Izquierdo - Branding -->
                        <div class="col-lg-5 login-brand-panel">
                            <div class="brand-content">
                                <div class="brand-logo">
                                    <img src="{{ asset('dimages/LogoIntesa.png') }}" alt="INTESA" class="intesa-logo">
                                </div>
                                <div class="brand-welcome">
                                    <h1 class="welcome-title h-4">Bienvenido</h1>
                                    <p class="welcome-text">Sistema de gestión para la administración de carteras de estudiantes.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Panel Derecho - Formulario -->
                        <div class="col-lg-7 login-form-panel">
                            <div class="login-card">
                                <div class="login-header">
                                    <h1 class="login-title">Iniciar sesión</h1>
                                    <p class="login-subtitle">Ingresa tus credenciales para acceder</p>
                                </div>
        
                                <div class="login-body">
                                    <form method="POST" action="{{ route('login') }}" class="login-form">
                                        @csrf
        
                                        <div class="form-group">
                                            <label for="email" class="form-label">
                                                Correo electrónico
                                            </label>
                                            <div class="input-wrapper">
                                                <input 
                                                    id="email" 
                                                    type="email" 
                                                    class="form-input @error('email') form-input-error @enderror" 
                                                    name="email" 
                                                    value="{{ old('email') }}" 
                                                    required 
                                                    autocomplete="email" 
                                                    autofocus
                                                    placeholder="Correo electrónico"
                                                >
                                                @error('email')
                                                    <div class="form-error">
                                                        <span>{{ $message }}</span>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
        
                                        <div class="form-group">
                                            <label for="password" class="form-label">
                                                Contraseña
                                            </label>
                                            <div class="input-wrapper">
                                                <input 
                                                    id="password" 
                                                    type="password" 
                                                    class="form-input @error('password') form-input-error @enderror" 
                                                    name="password" 
                                                    required 
                                                    autocomplete="current-password"
                                                    placeholder="Contraseña"
                                                >
                                                @error('password')
                                                    <div class="form-error">
                                                        <span>{{ $message }}</span>
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
        
                                        <button type="submit" class="login-button">
                                            Iniciar sesión
                                        </button>
                                        
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Centered Card Login Design with Green Theme */
.authincation {
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    min-height: 100vh;
}

.authincation::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(3px);
    z-index: 0;
}

.authincation .container {
    position: relative;
    z-index: 1;
}

/* Main Card Container */
.main-login-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    min-height: 650px;
    max-width: 1200px;
    width: 100%;
    margin: 0 auto;
}

/* Panel Izquierdo - Branding */
.login-brand-panel {
    background: linear-gradient(135deg, #188c52 0%, #156f42 100%);
    position: relative;
    overflow: hidden;
    min-height: 650px;
    display: flex;
    align-items: stretch;
}

.login-brand-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.1);
    z-index: 0;
}

.brand-content {
    position: relative;
    z-index: 1;
    padding: 3.5rem;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: #ffffff;
}

.brand-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    margin-bottom: 3rem;
}

.brand-logo img {
    display: block;
}

.intesa-logo {
    width: 180px;
    height: auto;
    max-height: 140px;
    object-fit: contain;
    display: block;
    margin: 0 auto 1rem;
}

.brand-title {
    font-size: 2rem;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
    letter-spacing: 1px;
}

.brand-welcome {
    max-width: 400px;
}

.welcome-title {
    font-size: 2.3rem;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 1.5rem 0;
    line-height: 1.2;
}

.welcome-text {
    font-size: 1.125rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
    margin: 0;
}

/* Panel Derecho - Formulario */
.login-form-panel {
    background-color: #ffffff;
    padding: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 650px;
}

.login-card {
    width: 100%;
    max-width: 500px;
    background: #ffffff;
    border-radius: 0;
    box-shadow: none;
    border: none;
}

.login-header {
    padding: 0 0 1.5rem 0;
    margin-bottom: 2rem;
}

.login-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.login-subtitle {
    font-size: 0.9375rem;
    color: #6b7280;
    margin: 0;
    font-weight: 400;
}

.login-body {
    padding: 0;
    background-color: transparent;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.9375rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.input-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.875rem 1rem;
    font-size: 0.9375rem;
    line-height: 1.5;
    color: #1f2937;
    background-color: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    transition: all 0.2s ease;
    outline: none;
}

.form-input::placeholder {
    color: #9ca3af;
}

.form-input:focus {
    color: #1f2937;
    background-color: #ffffff;
    border-color: #188c52;
    outline: none;
    box-shadow: 0 0 0 3px rgba(24, 140, 82, 0.1);
}

.form-input:hover:not(:focus) {
    border-color: #9ca3af;
}

.form-input-error {
    border-color: #ef4444;
}

.form-input-error:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-error {
    font-size: 0.8125rem;
    color: #ef4444;
    margin-top: 0.25rem;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-direction: row;
    gap: 1rem;
    margin-top: 0.5rem;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-check-input {
    width: 1rem;
    height: 1rem;
    margin: 0;
    cursor: pointer;
    accent-color: #188c52;
}

.form-check-label {
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    margin: 0;
}

.forgot-password-link {
    font-size: 0.875rem;
    color: #188c52;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.forgot-password-link:hover {
    color: #156f42;
    text-decoration: underline;
}

.login-button {
    width: 100%;
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
    background-color: #188c52;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 0.5rem;
}

.login-button:hover {
    background-color: #156f42;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(24, 140, 82, 0.3);
}

.login-button:active {
    background-color: #125a35;
    transform: translateY(0);
}

.login-button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(24, 140, 82, 0.2);
}

.login-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.footer-text {
    font-size: 0.8125rem;
    color: #9ca3af;
    margin: 0;
}

/* Responsive */
@media (max-width: 991px) {
    .main-login-card {
        min-height: auto;
        max-width: 100%;
    }
    
    .login-brand-panel {
        min-height: 350px;
    }
    
    .brand-content {
        padding: 2.5rem;
    }
    
    .login-form-panel {
        min-height: auto;
        padding: 2.5rem;
    }
    
    .welcome-title {
        font-size: 2.25rem;
    }
    
    .welcome-text {
        font-size: 1rem;
    }
}

@media (max-width: 768px) {
    .main-login-card {
        border-radius: 16px;
        margin: 1rem;
        min-height: auto;
    }
    
    .login-brand-panel {
        min-height: 300px;
    }
    
    .brand-content {
        padding: 2rem;
    }
    
    .brand-logo {
        margin-bottom: 2rem;
    }
    
    .intesa-logo {
        width: 100px;
    }
    
    .brand-title {
        font-size: 1.75rem;
    }
    
    .welcome-title {
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    
    .welcome-text {
        font-size: 0.9375rem;
    }
    
    .login-form-panel {
        padding: 2rem;
    }
    
    .login-title {
        font-size: 1.5rem;
    }
}
</style>

@endsection
