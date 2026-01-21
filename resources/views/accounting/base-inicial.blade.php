@extends('dash.app')

@section('page', 'Base Inicial')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-coins mr-2"></i> Configurar Base Inicial</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if($initialBalance)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Base inicial configurada:</strong> 
                        Fecha de inicio: {{ date('d/m/Y', strtotime($initialBalance->start_date)) }}, 
                        Efectivo: ${{ number_format($initialBalance->base_efectivo, 0, ',', '.') }}, 
                        Banco: ${{ number_format($initialBalance->base_banco, 0, ',', '.') }}
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Atención:</strong> No se ha configurado la base inicial. Es necesario configurarla para generar los informes semanales y mensuales.
                    </div>
                @endif

                <form method="POST" action="{{ route('accounting.base-inicial.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="start_date">Fecha de Inicio <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" 
                                       value="{{ old('start_date', $initialBalance ? $initialBalance->start_date->format('Y-m-d') : '') }}" required>
                                <small class="form-text text-muted">Fecha desde la cual se considera la base inicial</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="base_efectivo">Base de Efectivo <span class="text-danger">*</span></label>
                                <input type="number" name="base_efectivo" id="base_efectivo" class="form-control" 
                                       step="0.01" min="0" placeholder="0.00" 
                                       value="{{ old('base_efectivo', $initialBalance ? $initialBalance->base_efectivo : '') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="base_banco">Base de Banco <span class="text-danger">*</span></label>
                                <input type="number" name="base_banco" id="base_banco" class="form-control" 
                                       step="0.01" min="0" placeholder="0.00" 
                                       value="{{ old('base_banco', $initialBalance ? $initialBalance->base_banco : '') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> {{ $initialBalance ? 'Actualizar' : 'Guardar' }} Base Inicial
                        </button>
                        <a href="{{ route('accounting.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
