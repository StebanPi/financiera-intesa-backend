@extends('dash.app')

@section('page')
    Generar Planilla de Asistencia
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-file-pdf mr-2"></i>Generar Planilla de Asistencia
                </h4>
                <div class="ml-auto">
                    <a href="{{ route('matricula.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
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

                <form method="POST" action="{{ route('gestion-academica.planillas.asistencia.generate') }}" id="formPlanillaAsistencia">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="programa_id">Programa <span class="text-danger">*</span></label>
                                <select name="programa_id" id="programa_id" class="form-control @error('programa_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un programa...</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ old('programa_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('programa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horario_id">Horario <span class="text-danger">*</span></label>
                                <select name="horario_id" id="horario_id" class="form-control @error('horario_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un horario...</option>
                                    @foreach($schedules as $schedule)
                                        <option value="{{ $schedule->id }}" {{ old('horario_id') == $schedule->id ? 'selected' : '' }}>
                                            {{ $schedule->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('horario_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grupo_id">Grupo <span class="text-danger">*</span></label>
                                <select name="grupo_id" id="grupo_id" class="form-control @error('grupo_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un grupo...</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ old('grupo_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('grupo_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="docente_id">Docente <span class="text-danger">*</span></label>
                                <select name="docente_id" id="docente_id" class="form-control @error('docente_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un docente...</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('docente_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('docente_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modulo_id">Módulo <span class="text-danger">*</span></label>
                                <select name="modulo_id" id="modulo_id" class="form-control @error('modulo_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un módulo...</option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module->id }}" {{ old('modulo_id') == $module->id ? 'selected' : '' }}>
                                            {{ $module->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('modulo_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                       value="{{ old('fecha_inicio') }}" required>
                                @error('fecha_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fecha_final">Fecha Final <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_final" id="fecha_final" class="form-control @error('fecha_final') is-invalid @enderror" 
                                       value="{{ old('fecha_final') }}" required>
                                @error('fecha_final')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_clase">Fecha de Clase <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_clase" id="fecha_clase" class="form-control @error('fecha_clase') is-invalid @enderror" 
                                       value="{{ old('fecha_clase') }}" required>
                                @error('fecha_clase')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-file-pdf mr-2"></i>Generar PDF
                            </button>
                            <a href="{{ route('matricula.index') }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
