@extends('dash.app')

@section('page')
    Ficha de Matrícula
@endsection

@php
    use Illuminate\Support\Facades\Storage;
    // Lista de departamentos de Colombia
    $departamentos = [
        'Amazonas', 'Antioquia', 'Arauca', 'Atlántico', 'Bolívar', 'Boyacá', 'Caldas', 'Caquetá',
        'Casanare', 'Cauca', 'Cesar', 'Chocó', 'Córdoba', 'Cundinamarca', 'Guainía', 'Guaviare',
        'Huila', 'La Guajira', 'Magdalena', 'Meta', 'Nariño', 'Norte de Santander', 'Putumayo',
        'Quindío', 'Risaralda', 'San Andrés y Providencia', 'Santander', 'Sucre', 'Tolima',
        'Valle del Cauca', 'Vaupés', 'Vichada', 'Bogotá D.C.'
    ];
    
    // Opciones estándar para estado civil
    $estadosCiviles = ['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Unión Libre', 'Separado'];
    
    // Opciones estándar para ocupación
    $ocupaciones = [
        'Estudiante', 'Empleado', 'Independiente', 'Desempleado', 'Jubilado', 'Ama de casa',
        'Comerciante', 'Profesional', 'Técnico', 'Obrero', 'Agricultor', 'Otro'
    ];
    
    // Opciones estándar para nivel de formación
    $nivelesFormacion = [
        'Primaria', 'Secundaria', 'Bachiller', 'Técnico', 'Tecnólogo', 'Universitario', 'Postgrado'
    ];
    
    // Lista de discapacidades
    $discapacidades = [
        'Física', 'Visual', 'Auditiva', 'Intelectual', 'Psicosocial', 'Múltiple', 'Otra'
    ];
    
    // Generar años para número de grupo (2015 hasta actual + 1)
    $anioActual = date('Y');
    $aniosGrupo = [];
    for ($i = 2015; $i <= $anioActual + 1; $i++) {
        $aniosGrupo[] = $i;
    }
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fa-solid fa-file-lines mr-2"></i>Formulario de Inscripción</h4>
                    <div class="ml-3">
                        <a href="{{ route('matricula.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fa-solid fa-arrow-left mr-2"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <x-error-modal :errors="$errors" />

                <!-- Sección de Fotografía del Estudiante -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fa-solid fa-camera mr-2"></i>Fotografía del Estudiante</h5>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-center">
                                        <div id="photoPreview" class="mb-3">
                                            @if($matricula->photo_path && Storage::disk('public')->exists($matricula->photo_path))
                                                <img src="{{ url('/api/v1/matriculas/' . $matricula->cod_alumno . '/foto') }}" 
                                                     alt="Foto del estudiante" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 200px; max-height: 200px; object-fit: cover;"
                                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3ESin foto%3C/text%3E%3C/svg%3E';">
                                            @else
                                                <div class="border rounded p-5 text-muted" style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                    <div class="text-center">
                                                        <i class="fa-solid fa-user fa-3x mb-2"></i>
                                                        <p class="mb-0">Sin foto</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <form id="photoUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="photo">Subir Fotografía</label>
                                                <input type="file" 
                                                       class="form-control-file" 
                                                       id="photo" 
                                                       name="photo" 
                                                       accept="image/jpeg,image/jpg,image/png,image/webp">
                                                <small class="form-text text-muted">
                                                    Formatos permitidos: JPG, PNG, WebP. Tamaño máximo: 2MB
                                                </small>
                                            </div>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-primary" id="btnUploadPhoto">
                                                    <i class="fa-solid fa-upload mr-2"></i>Subir Foto
                                                </button>
                                                @if($matricula->photo_path)
                                                    <button type="button" class="btn btn-danger" id="btnDeletePhoto">
                                                        <i class="fa-solid fa-trash mr-2"></i>Eliminar Foto
                                                    </button>
                                                @endif
                                            </div>
                                            <div id="photoUploadMessage" class="mt-2"></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('matricula.update', $cod_alumno) }}" id="formMatricula">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="cod_alumno" value="{{ $cod_alumno }}">

                    <h5 class="mb-3 text-primary"><i class="fa-solid fa-user mr-2"></i>Datos Personales</h5>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nombre_completo">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="nombre_completo" id="nombre_completo" class="form-control @error('nombre_completo') is-invalid @enderror" 
                                       value="{{ old('nombre_completo', $matricula->nombre_completo ?? '') }}" required
                                       aria-invalid="{{ $errors->has('nombre_completo') ? 'true' : 'false' }}">
                                @error('nombre_completo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="numero_documento">Número de Documento <span class="text-danger">*</span></label>
                                <input type="text" name="numero_documento" id="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror" 
                                       value="{{ old('numero_documento', $matricula->numero_documento ?? '') }}" required
                                       aria-invalid="{{ $errors->has('numero_documento') ? 'true' : 'false' }}">
                                @error('numero_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_documento">Tipo de Documento <span class="text-danger">*</span></label>
                                <select name="tipo_documento" id="tipo_documento" class="form-control @error('tipo_documento') is-invalid @enderror" required
                                        aria-invalid="{{ $errors->has('tipo_documento') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    <option value="CC" {{ old('tipo_documento', $matricula->tipo_documento ?? 'CC') == 'CC' ? 'selected' : '' }}>C.C</option>
                                    <option value="TI" {{ old('tipo_documento', $matricula->tipo_documento ?? '') == 'TI' ? 'selected' : '' }}>T.I</option>
                                    <option value="PPT" {{ old('tipo_documento', $matricula->tipo_documento ?? '') == 'PPT' ? 'selected' : '' }}>PPT</option>
                                </select>
                                @error('tipo_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lugar_expedicion_documento">Lugar de Expedición del Documento</label>
                                <input type="text" name="lugar_expedicion_documento" id="lugar_expedicion_documento" class="form-control" 
                                       value="{{ old('lugar_expedicion_documento', $matricula->lugar_expedicion_documento ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" 
                                       value="{{ old('fecha_nacimiento', $matricula->fecha_nacimiento ? $matricula->fecha_nacimiento->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4 text-primary"><i class="fa-solid fa-map-location-dot mr-2"></i>Datos de Residencia</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="direccion_barrio">Dirección y Barrio de donde vive</label>
                                <textarea name="direccion_barrio" id="direccion_barrio" class="form-control" rows="2">{{ old('direccion_barrio', $matricula->direccion_barrio ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ciudad_residencia">Ciudad de Residencia</label>
                                <input type="text" name="ciudad_residencia" id="ciudad_residencia" class="form-control" 
                                       value="{{ old('ciudad_residencia', $matricula->ciudad_residencia ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="departamento">Departamento</label>
                                <select name="departamento" id="departamento" class="form-control @error('departamento') is-invalid @enderror"
                                        aria-invalid="{{ $errors->has('departamento') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($departamentos as $dept)
                                        <option value="{{ $dept }}" {{ old('departamento', $matricula->departamento ?? '') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                    @endforeach
                                </select>
                                @error('departamento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correo_gmail">Correo de Gmail</label>
                                <input type="email" name="correo_gmail" id="correo_gmail" class="form-control" 
                                       value="{{ old('correo_gmail', $matricula->correo_gmail ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4 text-primary"><i class="fa-solid fa-phone mr-2"></i>Contacto</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono_personal">Número de Teléfono Personal</label>
                                <input type="text" name="telefono_personal" id="telefono_personal" class="form-control" 
                                       value="{{ old('telefono_personal', $matricula->telefono_personal ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono_emergencia">Número de Teléfono en caso de Emergencia</label>
                                <input type="text" name="telefono_emergencia" id="telefono_emergencia" class="form-control" 
                                       value="{{ old('telefono_emergencia', $matricula->telefono_emergencia ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4 text-primary"><i class="fa-solid fa-info-circle mr-2"></i>Información Adicional</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estado_civil">Estado Civil</label>
                                <select name="estado_civil" id="estado_civil" class="form-control @error('estado_civil') is-invalid @enderror"
                                        aria-invalid="{{ $errors->has('estado_civil') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($estadosCiviles as $estado)
                                        <option value="{{ $estado }}" {{ old('estado_civil', $matricula->estado_civil ?? '') == $estado ? 'selected' : '' }}>{{ $estado }}</option>
                                    @endforeach
                                </select>
                                @error('estado_civil')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ocupacion">Ocupación</label>
                                <select name="ocupacion" id="ocupacion" class="form-control @error('ocupacion') is-invalid @enderror"
                                        aria-invalid="{{ $errors->has('ocupacion') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($ocupaciones as $ocup)
                                        <option value="{{ $ocup }}" {{ old('ocupacion', $matricula->ocupacion ?? '') == $ocup ? 'selected' : '' }}>{{ $ocup }}</option>
                                    @endforeach
                                </select>
                                @error('ocupacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nivel_formacion">Nivel de Formación</label>
                                <select name="nivel_formacion" id="nivel_formacion" class="form-control @error('nivel_formacion') is-invalid @enderror"
                                        aria-invalid="{{ $errors->has('nivel_formacion') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($nivelesFormacion as $nivel)
                                        <option value="{{ $nivel }}" {{ old('nivel_formacion', $matricula->nivel_formacion ?? '') == $nivel ? 'selected' : '' }}>{{ $nivel }}</option>
                                    @endforeach
                                </select>
                                @error('nivel_formacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estrato">Estrato</label>
                                <select name="estrato" id="estrato" class="form-control">
                                    <option value="">Seleccione...</option>
                                    @for($i = 1; $i <= 6; $i++)
                                        <option value="{{ $i }}" {{ old('estrato', $matricula->estrato ?? '') == $i ? 'selected' : '' }}>Estrato {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nivel_sisben">Nivel Del Sisbén</label>
                                <input type="text" name="nivel_sisben" id="nivel_sisben" class="form-control" 
                                       value="{{ old('nivel_sisben', $matricula->nivel_sisben ?? '') }}" placeholder="Ej: A, B, C, D">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="eps">EPS</label>
                                <input type="text" name="eps" id="eps" class="form-control" 
                                       value="{{ old('eps', $matricula->eps ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grupo_sanguineo">Grupo Sanguíneo</label>
                                <select name="grupo_sanguineo" id="grupo_sanguineo" class="form-control">
                                    <option value="">Seleccione...</option>
                                    <option value="O+" {{ old('grupo_sanguineo', $matricula->grupo_sanguineo ?? '') == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('grupo_sanguineo', $matricula->grupo_sanguineo ?? '') == 'O-' ? 'selected' : '' }}>O-</option>
                                    <option value="A+" {{ old('grupo_sanguineo', $matricula->grupo_sanguineo ?? '') == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('grupo_sanguineo', $matricula->grupo_sanguineo ?? '') == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('grupo_sanguineo', $matricula->grupo_sanguineo ?? '') == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('grupo_sanguineo', $matricula->grupo_sanguineo ?? '') == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('grupo_sanguineo', $matricula->grupo_sanguineo ?? '') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('grupo_sanguineo', $matricula->grupo_sanguineo ?? '') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tiene_discapacidad">Discapacidad</label>
                                <select name="tiene_discapacidad" id="tiene_discapacidad" class="form-control @error('tiene_discapacidad') is-invalid @enderror"
                                        aria-invalid="{{ $errors->has('tiene_discapacidad') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    <option value="No" {{ old('tiene_discapacidad', $matricula->tiene_discapacidad ?? '') == 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Sí" {{ old('tiene_discapacidad', $matricula->tiene_discapacidad ?? '') == 'Sí' ? 'selected' : '' }}>Sí</option>
                                    <option value="Prefiero no decir" {{ old('tiene_discapacidad', $matricula->tiene_discapacidad ?? '') == 'Prefiero no decir' ? 'selected' : '' }}>Prefiero no decir</option>
                                </select>
                                @error('tiene_discapacidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row" id="discapacidad_detalle_row" style="display: {{ old('tiene_discapacidad', $matricula->tiene_discapacidad ?? '') == 'Sí' ? 'block' : 'none' }};">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_discapacidad">Tipo de Discapacidad</label>
                                <select name="tipo_discapacidad" id="tipo_discapacidad" class="form-control @error('tipo_discapacidad') is-invalid @enderror"
                                        aria-invalid="{{ $errors->has('tipo_discapacidad') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($discapacidades as $disc)
                                        <option value="{{ $disc }}" {{ old('tipo_discapacidad', $matricula->tipo_discapacidad ?? '') == $disc ? 'selected' : '' }}>{{ $disc }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_discapacidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="discapacidad_descripcion">Descripción de la Discapacidad</label>
                                <textarea name="discapacidad_descripcion" id="discapacidad_descripcion" class="form-control" rows="2">{{ old('discapacidad_descripcion', $matricula->discapacidad_descripcion ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4 text-primary"><i class="fa-solid fa-graduation-cap mr-2"></i>Información Académica</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="programa">Programa <span class="text-danger">*</span></label>
                                <select name="programa" id="programa" class="form-control @error('programa') is-invalid @enderror" required
                                        aria-invalid="{{ $errors->has('programa') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($programs ?? [] as $program)
                                        <option value="{{ $program->name }}" {{ old('programa', $matricula->programa ?? '') == $program->name ? 'selected' : '' }}>{{ $program->name }}</option>
                                    @endforeach
                                </select>
                                @error('programa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sede">Sede <span class="text-danger">*</span></label>
                                <select name="sede" id="sede" class="form-control @error('sede') is-invalid @enderror" required
                                        aria-invalid="{{ $errors->has('sede') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    <option value="Barrancabermeja" {{ old('sede', $matricula->sede ?? '') == 'Barrancabermeja' ? 'selected' : '' }}>Barrancabermeja</option>
                                    <option value="Aguachica" {{ old('sede', $matricula->sede ?? '') == 'Aguachica' ? 'selected' : '' }}>Aguachica</option>
                                    <option value="Virtual" {{ old('sede', $matricula->sede ?? '') == 'Virtual' ? 'selected' : '' }}>Virtual</option>
                                </select>
                                @error('sede')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horario">Horario <span class="text-danger">*</span></label>
                                <select name="horario" id="horario" class="form-control @error('horario') is-invalid @enderror" required
                                        aria-invalid="{{ $errors->has('horario') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($schedules ?? [] as $schedule)
                                        <option value="{{ $schedule->name }}" {{ old('horario', $matricula->horario ?? '') == $schedule->name ? 'selected' : '' }}>{{ $schedule->name }}</option>
                                    @endforeach
                                </select>
                                @error('horario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estado_estudiante">Estado del Estudiante <span class="text-danger">*</span></label>
                                <select name="estado_estudiante" id="estado_estudiante" class="form-control @error('estado_estudiante') is-invalid @enderror" required
                                        aria-invalid="{{ $errors->has('estado_estudiante') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    <option value="Activo" {{ old('estado_estudiante', $matricula->estado_estudiante ?? '') == 'Activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="Inactivo" {{ old('estado_estudiante', $matricula->estado_estudiante ?? '') == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="Por Certificar" {{ old('estado_estudiante', $matricula->estado_estudiante ?? '') == 'Por Certificar' ? 'selected' : '' }}>Por Certificar</option>
                                    <option value="Certificado" {{ old('estado_estudiante', $matricula->estado_estudiante ?? '') == 'Certificado' ? 'selected' : '' }}>Certificado</option>
                                    <option value="Retirado" {{ old('estado_estudiante', $matricula->estado_estudiante ?? '') == 'Retirado' ? 'selected' : '' }}>Retirado</option>
                                    <option value="Suspendido" {{ old('estado_estudiante', $matricula->estado_estudiante ?? '') == 'Suspendido' ? 'selected' : '' }}>Suspendido</option>
                                    <option value="Todos" {{ old('estado_estudiante', $matricula->estado_estudiante ?? '') == 'Todos' ? 'selected' : '' }}>Todos</option>
                                </select>
                                @error('estado_estudiante')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="semestre_actual">Semestre Actual <span class="text-danger">*</span></label>
                                <select name="semestre_actual" id="semestre_actual" class="form-control @error('semestre_actual') is-invalid @enderror" required
                                        aria-invalid="{{ $errors->has('semestre_actual') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    <option value="I" {{ old('semestre_actual', $matricula->semestre_actual ?? '') == 'I' ? 'selected' : '' }}>I</option>
                                    <option value="II" {{ old('semestre_actual', $matricula->semestre_actual ?? '') == 'II' ? 'selected' : '' }}>II</option>
                                    <option value="Ninguno (curso)" {{ old('semestre_actual', $matricula->semestre_actual ?? '') == 'Ninguno (curso)' ? 'selected' : '' }}>Ninguno (curso)</option>
                                </select>
                                @error('semestre_actual')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="anio">Año <span class="text-danger">*</span></label>
                                <select name="anio" id="anio" class="form-control @error('anio') is-invalid @enderror" required
                                        aria-invalid="{{ $errors->has('anio') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($aniosGrupo as $anio)
                                        <option value="{{ $anio }}" {{ old('anio', $matricula->anio ?? '') == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                                    @endforeach
                                </select>
                                @error('anio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="numero_grupo">Número de Grupo <span class="text-danger">*</span></label>
                                <select name="numero_grupo" id="numero_grupo" class="form-control @error('numero_grupo') is-invalid @enderror" required
                                        aria-invalid="{{ $errors->has('numero_grupo') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($groups ?? [] as $group)
                                        <option value="{{ $group->name }}" {{ old('numero_grupo', $matricula->numero_grupo ?? '') == $group->name ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                @error('numero_grupo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="talla_uniforme">Talla Uniforme</label>
                                <select name="talla_uniforme" id="talla_uniforme" class="form-control @error('talla_uniforme') is-invalid @enderror"
                                        aria-invalid="{{ $errors->has('talla_uniforme') ? 'true' : 'false' }}">
                                    <option value="">Seleccione...</option>
                                    <option value="XS" {{ old('talla_uniforme', $matricula->talla_uniforme ?? '') == 'XS' ? 'selected' : '' }}>XS</option>
                                    <option value="S" {{ old('talla_uniforme', $matricula->talla_uniforme ?? '') == 'S' ? 'selected' : '' }}>S</option>
                                    <option value="M" {{ old('talla_uniforme', $matricula->talla_uniforme ?? '') == 'M' ? 'selected' : '' }}>M</option>
                                    <option value="L" {{ old('talla_uniforme', $matricula->talla_uniforme ?? '') == 'L' ? 'selected' : '' }}>L</option>
                                    <option value="XL" {{ old('talla_uniforme', $matricula->talla_uniforme ?? '') == 'XL' ? 'selected' : '' }}>XL</option>
                                    <option value="XXL" {{ old('talla_uniforme', $matricula->talla_uniforme ?? '') == 'XXL' ? 'selected' : '' }}>XXL</option>
                                    <option value="XXXL" {{ old('talla_uniforme', $matricula->talla_uniforme ?? '') == 'XXXL' ? 'selected' : '' }}>XXXL</option>
                                </select>
                                @error('talla_uniforme')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contraseña_plataforma">Contraseña de Plataforma</label>
                                <input type="text" name="contraseña_plataforma" id="contraseña_plataforma" class="form-control" 
                                       value="{{ old('contraseña_plataforma', $matricula->contraseña_plataforma ?? '') }}"
                                       placeholder="Opcional">
                                <small class="form-text text-muted">Campo opcional</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-right mt-4">
                        <a href="{{ route('matricula.ficha.view', $cod_alumno) }}" class="btn btn-info mr-2" target="_blank">
                            <i class="fa-solid fa-eye mr-2"></i>Ver PDF
                        </a>
                        <a href="{{ route('matricula.ficha.download', $cod_alumno) }}" class="btn btn-success mr-2" target="_blank">
                            <i class="fa-solid fa-file-pdf mr-2"></i>Generar PDF
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save mr-2"></i>Actualizar Ficha de Matrícula
                        </button>
                        <button type="button" class="btn btn-danger ml-2" id="btnEliminar">
                            <i class="fa-solid fa-trash mr-2"></i>Eliminar
                        </button>
                        <a href="{{ route('matricula.index') }}" class="btn btn-secondary ml-2">
                            <i class="fa-solid fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar la ficha de matrícula de <strong>{{ $matricula->nombre_completo ?? '' }}</strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form method="POST" action="{{ route('matricula.destroy', $cod_alumno) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash mr-2"></i>Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
jQuery(document).ready(function($) {
    // Mostrar/ocultar campos de discapacidad
    $('#tiene_discapacidad').on('change', function() {
        if ($(this).val() == 'Sí') {
            $('#discapacidad_detalle_row').show();
            $('#tipo_discapacidad').prop('required', true);
        } else {
            $('#discapacidad_detalle_row').hide();
            $('#tipo_discapacidad').prop('required', false);
            $('#tipo_discapacidad').val('');
        }
    });
    
    // Abrir modal de confirmación al hacer clic en eliminar
    $('#btnEliminar').on('click', function() {
        $('#modalEliminar').modal('show');
    });
    
    // Validación del formulario
    $('#formMatricula').on('submit', function(e) {
        var isValid = true;
        
        // Validar solo campos requeridos (que tengan el atributo required)
        $(this).find('select[required], input[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
                if (!$(this).next('.invalid-feedback').length) {
                    $(this).after('<div class="invalid-feedback">Este campo es obligatorio</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Validar discapacidad solo si se seleccionó "Sí"
        if ($('#tiene_discapacidad').val() == 'Sí' && !$('#tipo_discapacidad').val()) {
            isValid = false;
            $('#tipo_discapacidad').addClass('is-invalid');
            if (!$('#tipo_discapacidad').next('.invalid-feedback').length) {
                $('#tipo_discapacidad').after('<div class="invalid-feedback">Debe seleccionar el tipo de discapacidad</div>');
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });

    // Manejar subida de foto
    $('#btnUploadPhoto').on('click', function() {
        var fileInput = $('#photo')[0];
        if (!fileInput.files || !fileInput.files[0]) {
            alert('Por favor seleccione una imagen');
            return;
        }

        var formData = new FormData();
        formData.append('photo', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("matricula.foto.upload", $cod_alumno) }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    $('#photoUploadMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    // Actualizar preview
                    $('#photoPreview').html('<img src="' + response.photo_url + '" alt="Foto del estudiante" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">');
                    // Mostrar botón eliminar si no existe
                    if($('#btnDeletePhoto').length === 0) {
                        $('#btnUploadPhoto').after('<button type="button" class="btn btn-danger ml-2" id="btnDeletePhoto"><i class="fa-solid fa-trash mr-2"></i>Eliminar Foto</button>');
                        // Agregar evento al nuevo botón
                        $('#btnDeletePhoto').on('click', function() {
                            deletePhoto();
                        });
                    }
                    setTimeout(function() {
                        $('#photoUploadMessage').html('');
                    }, 3000);
                }
            },
            error: function(xhr) {
                var message = 'Error al subir la foto';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#photoUploadMessage').html('<div class="alert alert-danger">' + message + '</div>');
            }
        });
    });

    // Manejar eliminación de foto
    function deletePhoto() {
        if(!confirm('¿Está seguro de eliminar la foto del estudiante?')) {
            return;
        }

        $.ajax({
            url: '{{ route("matricula.foto.delete", $cod_alumno) }}',
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    $('#photoUploadMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                    // Actualizar preview con placeholder
                    $('#photoPreview').html('<div class="border rounded p-5 text-muted" style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; margin: 0 auto;"><div class="text-center"><i class="fa-solid fa-user fa-3x mb-2"></i><p class="mb-0">Sin foto</p></div></div>');
                    // Ocultar botón eliminar
                    $('#btnDeletePhoto').remove();
                    setTimeout(function() {
                        $('#photoUploadMessage').html('');
                    }, 3000);
                }
            },
            error: function(xhr) {
                var message = 'Error al eliminar la foto';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#photoUploadMessage').html('<div class="alert alert-danger">' + message + '</div>');
            }
        });
    }

    // Agregar evento al botón eliminar si existe
    $(document).on('click', '#btnDeletePhoto', function() {
        deletePhoto();
    });
});
</script>
@endpush
@endsection
