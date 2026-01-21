@extends('dash.app')

@section('page')
    Mantenimiento
@endsection

@push('styles')
<style>
    .btn-purple {
        background-color: #8b5cf6 !important;
        border-color: #8b5cf6 !important;
        color: white !important;
    }
    .btn-purple:hover {
        background-color: #7c3aed !important;
        border-color: #7c3aed !important;
        color: white !important;
    }
    .text-purple {
        color: #8b5cf6 !important;
    }
    .bg-purple {
        background-color: #8b5cf6 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-tools"></i> Herramientas de Investigación y Limpieza
                    </h4>
                </div>
                <div class="card-body">
                    <!-- ========== SECCIÓN 1: HERRAMIENTAS DE INVESTIGACIÓN Y LIMPIEZA ========== -->
                    <div class="mb-5">
                        <h3 class="mb-4" style="color: #374151; font-weight: 700; border-bottom: 3px solid #f59e0b; padding-bottom: 10px;">
                            <i class="fas fa-search text-warning mr-2"></i>1. Herramientas de Investigación y Limpieza
                        </h3>
                        
                        <div class="alert alert-warning" style="border: 1px solid #f59e0b; border-radius: 8px; background-color: #fffbeb;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-search" style="font-size: 24px; color: #f59e0b; margin-right: 15px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 8px 0; color: #000; font-weight: 700;">
                                        Abonos Problemáticos
                                    </h5>
                                    <p style="margin: 0; color: #000; font-size: 14px;">
                                        Investiga y elimina abonos problemáticos (sin estudiante o sin programa)
                                    </p>
                                </div>
                            </div>
                        </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <button type="button" id="btnInvestigarAbonos" class="btn btn-warning btn-lg btn-block" style="background-color: #f59e0b; border-color: #f59e0b; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-search mr-2"></i>Investigar Abonos Problemáticos
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="btnEliminarAbonos" class="btn btn-danger btn-lg btn-block" style="background-color: #dc2626; border-color: #dc2626; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-trash mr-2"></i>Eliminar Abonos Problemáticos
                            </button>
                        </div>
                    </div>

                    <!-- Resultados de Investigación -->
                    <div id="resultadosInvestigacion" style="display: none; margin-top: 20px;">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Resultados de la Investigación</h5>
                            </div>
                            <div class="card-body" id="contenidoResultados">
                                <!-- Contenido dinámico -->
                            </div>
                        </div>
                    </div>

                    </div>

                    <!-- ========== SECCIÓN 2: HERRAMIENTAS DE REPARACIÓN DE MATRÍCULAS ========== -->
                    <div class="mb-5">
                        <h3 class="mb-4" style="color: #374151; font-weight: 700; border-bottom: 3px solid #3b82f6; padding-bottom: 10px;">
                            <i class="fas fa-graduation-cap text-info mr-2"></i>2. Herramientas de Reparación de Matrículas
                        </h3>
                        
                        <div class="alert alert-info" style="border: 1px solid #3b82f6; border-radius: 8px; background-color: #eff6ff;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-graduation-cap" style="font-size: 24px; color: #3b82f6; margin-right: 15px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 8px 0; color: #000; font-weight: 700;">
                                        Matrículas Problemáticas
                                    </h5>
                                    <p style="margin: 0; color: #000; font-size: 14px;">
                                        Investiga y repara matrículas problemáticas (sin costo, sin programa, etc.)
                                    </p>
                                </div>
                            </div>
                        </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <button type="button" id="btnInvestigarMatriculas" class="btn btn-info btn-lg btn-block" style="background-color: #3b82f6; border-color: #3b82f6; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-search mr-2"></i>Investigar Matrículas Problemáticas
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="btnRepararMatriculas" class="btn btn-success btn-lg btn-block" style="background-color: #10b981; border-color: #10b981; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-wrench mr-2"></i>Reparar Matrículas Problemáticas
                            </button>
                        </div>
                    </div>

                    <!-- Resultados de Matrículas -->
                    <div id="resultadosMatriculas" style="display: none; margin-top: 20px;">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Resultados de la Investigación de Matrículas</h5>
                            </div>
                            <div class="card-body" id="contenidoResultadosMatriculas">
                                <!-- Contenido dinámico -->
                            </div>
                        </div>
                    </div>

                    </div>

                    <!-- ========== SECCIÓN 3: LIMPIEZA DE COSTOS ========== -->
                    <div class="mb-5">
                        <h3 class="mb-4" style="color: #374151; font-weight: 700; border-bottom: 3px solid #dc2626; padding-bottom: 10px;">
                            <i class="fas fa-dollar-sign text-danger mr-2"></i>3. Limpieza de Información de Costos
                        </h3>
                        
                        <div class="alert alert-danger" style="border: 1px solid #dc2626; border-radius: 8px; background-color: #fef2f2;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: #dc2626; margin-right: 15px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 8px 0; color: #000; font-weight: 700;">
                                        ⚠️ ADVERTENCIA: Esta acción es IRREVERSIBLE
                                    </h5>
                                    <p style="margin: 0; color: #000; font-size: 14px;">
                                        Esta herramienta eliminará TODA la información de costos, incluyendo: Costos, Cartera, Historial de Cartera, Abonos y Otros Abonos. Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <button type="button" id="btnVerStatsCostos" class="btn btn-info btn-lg btn-block" style="background-color: #3b82f6; border-color: #3b82f6; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                    <i class="fas fa-chart-bar mr-2"></i>Ver Estadísticas de Costos
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" id="btnEliminarCostos" class="btn btn-danger btn-lg btn-block" style="background-color: #dc2626; border-color: #dc2626; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                    <i class="fas fa-trash-alt mr-2"></i>Eliminar Todos los Costos
                                </button>
                            </div>
                        </div>

                        <!-- Resultados de Costos -->
                        <div id="resultadosCostos" style="display: none; margin-top: 20px;">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Estadísticas de Costos</h5>
                                </div>
                                <div class="card-body" id="contenidoResultadosCostos">
                                    <!-- Contenido dinámico -->
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ========== SECCIÓN 4: REVISIÓN DE DUPLICADOS ========== -->
                    <div class="mb-5">
                        <h3 class="mb-4" style="color: #374151; font-weight: 700; border-bottom: 3px solid #f59e0b; padding-bottom: 10px;">
                            <i class="fas fa-copy text-warning mr-2"></i>3. Revisión de Duplicados en Configuración
                        </h3>
                        
                        <div class="alert alert-warning" style="border: 1px solid #f59e0b; border-radius: 8px; background-color: #fffbeb;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-copy" style="font-size: 24px; color: #f59e0b; margin-right: 15px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 8px 0; color: #000; font-weight: 700;">
                                        Duplicados en Tablas de Configuración
                                    </h5>
                                    <p style="margin: 0; color: #000; font-size: 14px;">
                                        Revisa si hay registros duplicados en las tablas de Debe, Haber y Elaborado Por
                                    </p>
                                </div>
                            </div>
                        </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <button type="button" id="btnRevisarDuplicados" class="btn btn-warning btn-lg btn-block" style="background-color: #f59e0b; border-color: #f59e0b; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-search mr-2"></i>Revisar Duplicados
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="btnLimpiarDuplicados" class="btn btn-danger btn-lg btn-block" style="background-color: #dc2626; border-color: #dc2626; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-trash mr-2"></i>Limpiar Duplicados
                            </button>
                        </div>
                    </div>

                    <!-- Resultados de Duplicados -->
                    <div id="resultadosDuplicados" style="display: none; margin-top: 20px;">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Resultados de la Revisión de Duplicados</h5>
                            </div>
                            <div class="card-body" id="contenidoResultadosDuplicados">
                                <!-- Contenido dinámico -->
                            </div>
                        </div>
                    </div>

                    </div>

                    <!-- ========== SECCIÓN 5: GENERACIÓN DE PLANILLAS DE ASISTENCIA ========== -->
                    <div class="mb-5">
                        <h3 class="mb-4" style="color: #374151; font-weight: 700; border-bottom: 3px solid #3b82f6; padding-bottom: 10px;">
                            <i class="fas fa-file-pdf text-info mr-2"></i>4. Generación de Planillas de Asistencia de Prueba
                        </h3>
                        
                        <div class="alert alert-info" style="border: 1px solid #3b82f6; border-radius: 8px; background-color: #eff6ff;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-file-pdf" style="font-size: 24px; color: #3b82f6; margin-right: 15px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 8px 0; color: #000; font-weight: 700;">
                                        Planillas de Prueba
                                    </h5>
                                    <p style="margin: 0; color: #000; font-size: 14px;">
                                        Genera una planilla de asistencia PDF con N estudiantes de prueba para probar el sistema
                                    </p>
                                </div>
                            </div>
                        </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <form id="formPlanillaAsistenciaPrueba" method="POST" action="{{ route('maintenance.generar-planilla-asistencia-prueba') }}">
                                        @csrf
                                        <div class="form-group">
                                            <label for="numero_estudiantes">
                                                <i class="fas fa-users mr-2"></i>Número de Estudiantes
                                            </label>
                                            <input type="number" name="numero_estudiantes" id="numero_estudiantes" class="form-control" min="1" max="500" value="20" required>
                                            <small class="form-text text-muted">Ingrese el número de estudiantes que desea en la planilla (máximo 500)</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_inicio">
                                                <i class="fas fa-calendar-alt mr-2"></i>Fecha de Inicio
                                            </label>
                                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_final">
                                                <i class="fas fa-calendar-alt mr-2"></i>Fecha Final
                                            </label>
                                            <input type="date" name="fecha_final" id="fecha_final" class="form-control" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_clase">
                                                <i class="fas fa-calendar-check mr-2"></i>Fecha de Clase
                                            </label>
                                            <input type="date" name="fecha_clase" id="fecha_clase" class="form-control" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <button type="submit" class="btn btn-info btn-lg btn-block" style="background-color: #3b82f6; border-color: #3b82f6; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                            <i class="fas fa-file-pdf mr-2"></i>Generar Planilla de Prueba
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información</h6>
                                    <ul class="mb-0">
                                        <li>Se crearán estudiantes de prueba automáticamente si no existen suficientes</li>
                                        <li>Los estudiantes se crearán con datos aleatorios pero realistas</li>
                                        <li>La planilla se generará en formato PDF y se descargará automáticamente</li>
                                        <li>Se utilizarán programas, horarios y grupos de prueba si no existen</li>
                                        <li>Máximo 500 estudiantes por planilla</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    </div>

                    <!-- ========== SECCIÓN 5: LIMPIEZA DE DATOS DE PRUEBA ========== -->
                    <div class="mb-5">
                        <h3 class="mb-4" style="color: #374151; font-weight: 700; border-bottom: 3px solid #dc2626; padding-bottom: 10px;">
                            <i class="fas fa-trash-alt text-danger mr-2"></i>5. Limpieza de Datos de Prueba
                        </h3>
                        
                        <div class="alert alert-danger" style="border: 2px solid #dc2626; border-radius: 8px; background-color: #fef2f2;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle" style="font-size: 32px; color: #dc2626; margin-right: 15px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 8px 0; color: #000; font-weight: 700;">
                                        ⚠️ ACCIÓN IRREVERSIBLE - PREPARACIÓN PARA PRODUCCIÓN
                                    </h5>
                                    <p style="margin: 0 0 10px 0; color: #000; font-size: 14px;">
                                        Esta herramienta eliminará <strong>TODOS</strong> los datos de prueba de las siguientes tablas:
                                    </p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul style="margin: 0 0 10px 20px; color: #000; font-size: 14px;">
                                                <li>Abonos (Entries)</li>
                                                <li>Otros Abonos (Other Entries)</li>
                                                <li>Costos (Costs)</li>
                                                <li>Matrículas (Matriculas)</li>
                                                <li>Cartera (Purses)</li>
                                                <li>Historial de Cartera (History Purses)</li>
                                                <li>Recibos de Terceros (Third Receipts)</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul style="margin: 0 0 10px 20px; color: #000; font-size: 14px;">
                                                <li>Recibos de Egresos (Egreso Receipts)</li>
                                                <li>Proveedores de Egresos (Egreso Providers)</li>
                                                <li>Terceros (Third Entries)</li>
                                                <li>Actividades de Terceros (Third Activities)</li>
                                                <li>Bases de Caja (Cash Bases)</li>
                                                <li>Balances Iniciales (Initial Balances)</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-success" style="margin-top: 15px; background-color: #f0fdf4; border: 1px solid #22c55e;">
                                        <h6 style="margin: 0 0 8px 0; color: #15803d; font-weight: 700;">
                                            <i class="fas fa-check-circle mr-2"></i>✅ DATOS QUE SE MANTIENEN (Configuración del Sistema)
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul style="margin: 0 0 0 20px; color: #15803d; font-size: 13px;">
                                                    <li><strong>Conceptos</strong> (Configuración de conceptos de pago)</li>
                                                    <li><strong>Debe</strong> (Cuentas de débito)</li>
                                                    <li><strong>Haber</strong> (Cuentas de crédito)</li>
                                                    <li><strong>Elaborados</strong> (Personas que elaboran recibos)</li>
                                                    <li><strong>Consecutivos</strong> (Numeración de recibos)</li>
                                                    <li><strong>Otros Conceptos</strong> (Conceptos adicionales)</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul style="margin: 0 0 0 20px; color: #15803d; font-size: 13px;">
                                                    <li><strong>Programas</strong> (Programas académicos)</li>
                                                    <li><strong>Horarios</strong> (Horarios de clases)</li>
                                                    <li><strong>Grupos</strong> (Grupos de estudiantes)</li>
                                                    <li><strong>Docentes</strong> (Profesores)</li>
                                                    <li><strong>Módulos</strong> (Módulos académicos)</li>
                                                    <li><strong>Configuración de Institución</strong></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info" style="margin-top: 15px; background-color: #eff6ff; border: 1px solid #3b82f6;">
                                        <h6 style="margin: 0 0 8px 0; color: #1e40af; font-weight: 700;">
                                            <i class="fas fa-info-circle mr-2"></i>📋 DESPUÉS DE LIMPIAR - VERIFICAR ANTES DE USAR EN PRODUCCIÓN
                                        </h6>
                                        <ul style="margin: 0 0 0 20px; color: #1e40af; font-size: 13px;">
                                            <li>Verificar que los <strong>consecutivos</strong> estén configurados correctamente</li>
                                            <li>Verificar que los <strong>conceptos</strong> estén configurados según las necesidades</li>
                                            <li>Verificar que las <strong>cuentas contables (Debe/Haber)</strong> estén correctas</li>
                                            <li>Verificar que los <strong>elaborados</strong> estén configurados</li>
                                            <li>Configurar la <strong>base inicial</strong> desde Contabilidad si es necesario</li>
                                            <li>Verificar la <strong>configuración de la institución</strong></li>
                                        </ul>
                                    </div>
                                    
                                    <p style="margin: 15px 0 0 0; color: #dc2626; font-weight: 700; font-size: 15px; text-align: center; padding: 10px; background-color: #fee2e2; border-radius: 5px;">
                                        ⚠️ Después de limpiar, el sistema quedará listo para uso en producción, pero debe verificar la configuración antes de comenzar a registrar datos reales.
                                    </p>
                                </div>
                            </div>
                        </div>

                    <!-- Estadísticas Actuales -->
                    <div class="card mb-4" style="border: 1px solid #e5e7eb;">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar mr-2"></i>Estadísticas Actuales de Datos
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="statsContainer">
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Abonos</h6>
                                            <h3 class="mb-0" id="stat-entries">{{ $stats['entries'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Otros Abonos</h6>
                                            <h3 class="mb-0" id="stat-other_entries">{{ $stats['other_entries'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Costos</h6>
                                            <h3 class="mb-0" id="stat-costs">{{ $stats['costs'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Matrículas</h6>
                                            <h3 class="mb-0" id="stat-matriculas">{{ $stats['matriculas'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Cartera</h6>
                                            <h3 class="mb-0" id="stat-purses">{{ $stats['purses'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Historial Cartera</h6>
                                            <h3 class="mb-0" id="stat-history_purses">{{ $stats['history_purses'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Recibos Terceros</h6>
                                            <h3 class="mb-0" id="stat-third_receipts">{{ $stats['third_receipts'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Recibos Egresos</h6>
                                            <h3 class="mb-0" id="stat-egreso_receipts">{{ $stats['egreso_receipts'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Proveedores</h6>
                                            <h3 class="mb-0" id="stat-egreso_providers">{{ $stats['egreso_providers'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Terceros</h6>
                                            <h3 class="mb-0" id="stat-third_entries">{{ $stats['third_entries'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Actividades Terceros</h6>
                                            <h3 class="mb-0" id="stat-third_activities">{{ $stats['third_activities'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Bases de Caja</h6>
                                            <h3 class="mb-0" id="stat-cash_bases">{{ $stats['cash_bases'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-2">Balances Iniciales</h6>
                                            <h3 class="mb-0" id="stat-initial_balances">{{ $stats['initial_balances'] }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h6 class="mb-2">TOTAL DE REGISTROS</h6>
                                            <h2 class="mb-0" id="stat-total">{{ array_sum($stats) }}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción para Limpieza -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <button type="button" id="btnActualizarStats" class="btn btn-info btn-lg btn-block" style="background-color: #3b82f6; border-color: #3b82f6; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-sync-alt mr-2"></i>Actualizar Estadísticas
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" id="btnVerDatos" class="btn btn-warning btn-lg btn-block" style="background-color: #f59e0b; border-color: #f59e0b; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-eye mr-2"></i>Ver Datos a Eliminar
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" id="btnLimpiarDatos" class="btn btn-danger btn-lg btn-block" style="background-color: #dc2626; border-color: #dc2626; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-trash-alt mr-2"></i>Limpiar Todos los Datos
                            </button>
                        </div>
                    </div>

                        <!-- Tabla de Datos a Eliminar -->
                        <div id="tablaDatosEliminar" style="display: none; margin-top: 20px;">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Datos que se Eliminarán</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="tablaDatos">
                                            <thead>
                                                <tr>
                                                    <th>Tabla</th>
                                                    <th>Nombre</th>
                                                    <th>Cantidad</th>
                                                    <th>Descripción</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyDatos">
                                                <!-- Contenido dinámico -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== SECCIÓN 6: VERIFICACIÓN DE ELIMINACIONES ========== -->
                    <div class="mb-5">
                        <h3 class="mb-4" style="color: #374151; font-weight: 700; border-bottom: 3px solid #8b5cf6; padding-bottom: 10px;">
                            <i class="fas fa-search-minus text-purple mr-2"></i>6. Verificación de Eliminaciones Completas
                        </h3>
                        
                        <div class="alert alert-info" style="border: 1px solid #8b5cf6; border-radius: 8px; background-color: #f3e8ff;">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-search-minus" style="font-size: 24px; color: #8b5cf6; margin-right: 15px; margin-top: 2px;"></i>
                                <div style="flex: 1;">
                                    <h5 style="margin: 0 0 8px 0; color: #000; font-weight: 700;">
                                        Verificar Eliminaciones Físicas
                                    </h5>
                                    <p style="margin: 0; color: #000; font-size: 14px;">
                                        Esta herramienta verifica si los registros eliminados se están borrando completamente de la base de datos o si quedan guardados. Revisa las tablas de configuración para detectar registros huérfanos o eliminaciones incompletas.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <button type="button" id="btnVerificarEliminaciones" class="btn btn-purple btn-lg btn-block" style="background-color: #8b5cf6; border-color: #8b5cf6; color: white; padding: 12px 24px; font-weight: 600; border-radius: 8px;">
                                    <i class="fas fa-search mr-2"></i>Verificar Eliminaciones
                                </button>
                            </div>
                        </div>

                        <!-- Resultados de Verificación -->
                        <div id="resultadosVerificacion" style="display: none; margin-top: 20px;">
                            <div class="card">
                                <div class="card-header bg-purple text-white">
                                    <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Resultados de la Verificación</h5>
                                </div>
                                <div class="card-body" id="contenidoVerificacion">
                                    <!-- Contenido dinámico -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar Abonos -->
<div class="modal fade" id="modalConfirmarEliminarAbonos" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarEliminarAbonosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="modalConfirmarEliminarAbonosLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>¿Estás seguro de que deseas eliminar los abonos problemáticos?</strong></p>
                <p class="text-muted">Esta acción no se puede deshacer. Se eliminarán todos los abonos que no tienen estudiante asociado válido.</p>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Advertencia:</strong> Esta es una acción destructiva. Por favor, confirma que estás completamente seguro.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarAbonos">
                    <i class="fas fa-trash mr-2"></i>Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar Costos -->
<div class="modal fade" id="modalConfirmarEliminarCostos" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarEliminarCostosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="modalConfirmarEliminarCostosLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Eliminación de Costos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>¡ADVERTENCIA!</strong> Esta acción es <strong>IRREVERSIBLE</strong>.
                </div>
                <p>¿Está seguro de que desea eliminar <strong>TODA</strong> la información de costos?</p>
                <p>Esto eliminará:</p>
                <ul>
                    <li><strong>Costos</strong> - Todos los registros de costos de estudiantes</li>
                    <li><strong>Cartera</strong> - Todos los registros de cartera</li>
                    <li><strong>Historial de Cartera</strong> - Todo el historial de movimientos</li>
                    <li><strong>Abonos</strong> - Todos los recibos de pago</li>
                    <li><strong>Otros Abonos</strong> - Todos los otros recibos de pago</li>
                </ul>
                <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarCostos">
                    <i class="fas fa-trash-alt mr-2"></i>Sí, Eliminar Todo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Limpiar Duplicados -->
<div class="modal fade" id="modalConfirmarLimpiarDuplicados" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarLimpiarDuplicadosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="modalConfirmarLimpiarDuplicadosLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Limpieza de Duplicados
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>¿Estás seguro de que deseas limpiar los duplicados?</strong></p>
                <p class="text-muted">Esta acción eliminará los registros duplicados, manteniendo solo el primero de cada grupo (el de menor ID).</p>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Advertencia:</strong> Esta acción no se puede deshacer. Se mantendrá el registro con el ID más bajo de cada grupo duplicado.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarLimpiarDuplicados">
                    <i class="fas fa-trash mr-2"></i>Sí, Limpiar Duplicados
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Reparar Matrículas -->
<div class="modal fade" id="modalConfirmarRepararMatriculas" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarRepararMatriculasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white" id="modalConfirmarRepararMatriculasLabel">
                    <i class="fas fa-wrench mr-2"></i>Confirmar Reparación
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>¿Estás seguro de que deseas reparar las matrículas problemáticas?</strong></p>
                <p class="text-muted">Se intentará reparar automáticamente las matrículas que tengan problemas (por ejemplo, agregar programas faltantes desde la base de datos de estudiantes).</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmarRepararMatriculas">
                    <i class="fas fa-wrench mr-2"></i>Sí, Reparar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Limpiar Datos de Prueba -->
<div class="modal fade" id="modalConfirmarLimpieza" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarLimpiezaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalConfirmarLimpiezaLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Limpieza de Datos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5><strong>⚠️ ADVERTENCIA CRÍTICA</strong></h5>
                    <p>Estás a punto de eliminar <strong>TODOS</strong> los datos de prueba de las siguientes tablas:</p>
                    <ul>
                        <li>Abonos (Entries)</li>
                        <li>Otros Abonos (Other Entries)</li>
                        <li>Costos (Costs)</li>
                        <li>Matrículas (Matriculas)</li>
                        <li>Cartera (Purses)</li>
                        <li>Historial de Cartera (History Purses)</li>
                        <li>Recibos de Terceros (Third Receipts)</li>
                        <li>Recibos de Egresos (Egreso Receipts)</li>
                        <li>Proveedores de Egresos (Egreso Providers)</li>
                        <li>Terceros (Third Entries)</li>
                        <li>Actividades de Terceros (Third Activities)</li>
                        <li>Bases de Caja (Cash Bases)</li>
                        <li>Balances Iniciales (Initial Balances)</li>
                    </ul>
                    <p class="mb-0"><strong>Esta acción NO se puede deshacer.</strong></p>
                </div>
                <div class="form-group">
                    <label for="confirmacionTexto">Para confirmar, escribe <strong>"ELIMINAR"</strong> en el siguiente campo:</label>
                    <input type="text" class="form-control" id="confirmacionTexto" placeholder="Escribe ELIMINAR para confirmar">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarLimpieza" disabled>
                    <i class="fas fa-trash-alt mr-2"></i>Confirmar y Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resultado (Éxito/Error) -->
<div class="modal fade" id="modalResultado" tabindex="-1" role="dialog" aria-labelledby="modalResultadoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" id="modalResultadoHeader">
                <h5 class="modal-title" id="modalResultadoLabel">
                    <i class="fas fa-info-circle mr-2"></i>Resultado
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalResultadoBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    <i class="fas fa-check mr-2"></i>Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revisar Duplicados
    document.getElementById('btnRevisarDuplicados').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Revisando...';

        fetch('{{ route("maintenance.revisar-duplicados") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search mr-2"></i>Revisar Duplicados';

            if (data.success) {
                mostrarResultadosDuplicados(data);
            } else {
                mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search mr-2"></i>Revisar Duplicados';
            console.error('Error:', error);
            mostrarModalResultado('error', 'Error', 'Error al revisar duplicados: ' + error.message);
        });
    });

    // Limpiar Duplicados - Mostrar modal de confirmación
    document.getElementById('btnLimpiarDuplicados').addEventListener('click', function() {
        $('#modalConfirmarLimpiarDuplicados').modal('show');
    });

    // Confirmar limpieza de duplicados
    document.getElementById('btnConfirmarLimpiarDuplicados').addEventListener('click', function() {
        $('#modalConfirmarLimpiarDuplicados').modal('hide');
        
        const btnOriginal = document.getElementById('btnLimpiarDuplicados');
        const btnConfirmar = this;
        btnConfirmar.disabled = true;
        btnOriginal.disabled = true;
        btnOriginal.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Limpiando...';

        fetch('{{ route("maintenance.limpiar-duplicados") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btnConfirmar.disabled = false;
            btnOriginal.disabled = false;
            btnOriginal.innerHTML = '<i class="fas fa-trash mr-2"></i>Limpiar Duplicados';

            if (data.success) {
                let mensaje = '<p><strong>Éxito:</strong> ' + data.message + '</p>';
                mensaje += '<div class="alert alert-success mt-3 mb-0">';
                mensaje += '<strong>Registros eliminados:</strong><ul class="mb-0 mt-2">';
                mensaje += '<li><strong>Debe:</strong> ' + data.eliminados.debe + '</li>';
                mensaje += '<li><strong>Haber:</strong> ' + data.eliminados.haber + '</li>';
                mensaje += '<li><strong>Elaborado:</strong> ' + data.eliminados.elaborado + '</li>';
                mensaje += '</ul></div>';
                mensaje += '<div class="alert alert-info mt-3 mb-0">';
                mensaje += '<strong>Total eliminado:</strong> ' + data.total + ' registros duplicados';
                mensaje += '</div>';
                mostrarModalResultado('success', 'Éxito', mensaje, true);
            } else {
                mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            btnConfirmar.disabled = false;
            btnOriginal.disabled = false;
            btnOriginal.innerHTML = '<i class="fas fa-trash mr-2"></i>Limpiar Duplicados';
            console.error('Error:', error);
            mostrarModalResultado('error', 'Error', 'Error al limpiar duplicados: ' + error.message);
        });
    });

    function mostrarResultadosDuplicados(data) {
        const div = document.getElementById('resultadosDuplicados');
        const contenido = document.getElementById('contenidoResultadosDuplicados');
        
        let html = '<div class="alert alert-info"><strong>Total de grupos duplicados encontrados: ' + data.total + '</strong></div>';
        
        if (data.total > 0) {
            html += '<div class="row">';
            
            // Duplicados en Debe
            if (data.duplicados.debe.length > 0) {
                html += '<div class="col-md-4 mb-3">';
                html += '<h6 class="text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Debe (' + data.resumen.debe + ' grupos duplicados)</h6>';
                html += '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
                html += '<table class="table table-sm table-striped">';
                html += '<thead><tr><th>Cuenta</th><th>Nombre</th><th>Cant.</th><th>IDs</th></tr></thead>';
                html += '<tbody>';
                data.duplicados.debe.forEach(item => {
                    html += '<tr>';
                    html += '<td>' + item.cuenta + '</td>';
                    html += '<td>' + item.nombre + '</td>';
                    html += '<td><span class="badge badge-danger">' + item.cantidad + '</span></td>';
                    html += '<td><small>' + item.ids.join(', ') + '</small></td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div>';
            }

            // Duplicados en Haber
            if (data.duplicados.haber.length > 0) {
                html += '<div class="col-md-4 mb-3">';
                html += '<h6 class="text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Haber (' + data.resumen.haber + ' grupos duplicados)</h6>';
                html += '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
                html += '<table class="table table-sm table-striped">';
                html += '<thead><tr><th>Cuenta</th><th>Nombre</th><th>Cant.</th><th>IDs</th></tr></thead>';
                html += '<tbody>';
                data.duplicados.haber.forEach(item => {
                    html += '<tr>';
                    html += '<td>' + item.cuenta + '</td>';
                    html += '<td>' + item.nombre + '</td>';
                    html += '<td><span class="badge badge-danger">' + item.cantidad + '</span></td>';
                    html += '<td><small>' + item.ids.join(', ') + '</small></td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div>';
            }

            // Duplicados en Elaborado
            if (data.duplicados.elaborado.length > 0) {
                html += '<div class="col-md-4 mb-3">';
                html += '<h6 class="text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Elaborado Por (' + data.resumen.elaborado + ' grupos duplicados)</h6>';
                html += '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">';
                html += '<table class="table table-sm table-striped">';
                html += '<thead><tr><th>Nombre</th><th>Cant.</th><th>IDs</th></tr></thead>';
                html += '<tbody>';
                data.duplicados.elaborado.forEach(item => {
                    html += '<tr>';
                    html += '<td>' + item.nombre + '</td>';
                    html += '<td><span class="badge badge-danger">' + item.cantidad + '</span></td>';
                    html += '<td><small>' + item.ids.join(', ') + '</small></td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div>';
            }

            html += '</div>';
        } else {
            html = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>No se encontraron duplicados. Todas las configuraciones están correctas.</div>';
        }

        contenido.innerHTML = html;
        div.style.display = 'block';
    }

    // Ver Estadísticas de Costos
    document.getElementById('btnVerStatsCostos').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Cargando...';

        fetch('{{ route("maintenance.get-stats-costos") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-chart-bar mr-2"></i>Ver Estadísticas de Costos';

            if (data.success) {
                mostrarResultadosCostos(data.stats);
            } else {
                mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-chart-bar mr-2"></i>Ver Estadísticas de Costos';
            console.error('Error:', error);
            mostrarModalResultado('error', 'Error', 'Error al obtener estadísticas: ' + error.message);
        });
    });

    // Eliminar Costos - Mostrar modal de confirmación
    document.getElementById('btnEliminarCostos').addEventListener('click', function() {
        $('#modalConfirmarEliminarCostos').modal('show');
    });

    // Confirmar eliminación de costos
    document.getElementById('btnConfirmarEliminarCostos').addEventListener('click', function() {
        $('#modalConfirmarEliminarCostos').modal('hide');
        
        const btnOriginal = document.getElementById('btnEliminarCostos');
        const btnConfirmar = this;
        btnConfirmar.disabled = true;
        btnOriginal.disabled = true;
        btnOriginal.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Eliminando...';

        fetch('{{ route("maintenance.eliminar-costos") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btnConfirmar.disabled = false;
            btnOriginal.disabled = false;
            btnOriginal.innerHTML = '<i class="fas fa-trash-alt mr-2"></i>Eliminar Todos los Costos';

            if (data.success) {
                let mensaje = '<p><strong>Éxito:</strong> ' + data.message + '</p>';
                mensaje += '<div class="alert alert-success mt-3 mb-0">';
                mensaje += '<strong>Registros eliminados:</strong><ul class="mb-0 mt-2">';
                mensaje += '<li><strong>Costos:</strong> ' + data.eliminados.costs + '</li>';
                mensaje += '<li><strong>Cartera:</strong> ' + data.eliminados.purses + '</li>';
                mensaje += '<li><strong>Historial de Cartera:</strong> ' + data.eliminados.history_purses + '</li>';
                mensaje += '<li><strong>Abonos:</strong> ' + data.eliminados.entries + '</li>';
                mensaje += '<li><strong>Otros Abonos:</strong> ' + data.eliminados.other_entries + '</li>';
                mensaje += '</ul></div>';
                mensaje += '<div class="alert alert-info mt-3 mb-0">';
                mensaje += '<strong>Total eliminado:</strong> ' + data.total + ' registros';
                mensaje += '</div>';
                mostrarModalResultado('success', 'Éxito', mensaje, true);
                
                // Ocultar resultados de estadísticas si estaban visibles
                document.getElementById('resultadosCostos').style.display = 'none';
            } else {
                mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            btnConfirmar.disabled = false;
            btnOriginal.disabled = false;
            btnOriginal.innerHTML = '<i class="fas fa-trash-alt mr-2"></i>Eliminar Todos los Costos';
            console.error('Error:', error);
            mostrarModalResultado('error', 'Error', 'Error al eliminar costos: ' + error.message);
        });
    });

    function mostrarResultadosCostos(stats) {
        const div = document.getElementById('resultadosCostos');
        const contenido = document.getElementById('contenidoResultadosCostos');
        
        let html = '<div class="alert alert-info"><strong>Total de registros relacionados con costos: ' + stats.total + '</strong></div>';
        html += '<div class="row">';
        html += '<div class="col-md-6 mb-3">';
        html += '<div class="card">';
        html += '<div class="card-body">';
        html += '<h6 class="card-title"><i class="fas fa-dollar-sign mr-2"></i>Costos</h6>';
        html += '<h3 class="text-primary">' + stats.costs + '</h3>';
        html += '<p class="text-muted mb-0">Registros de costos de estudiantes</p>';
        html += '</div></div></div>';
        
        html += '<div class="col-md-6 mb-3">';
        html += '<div class="card">';
        html += '<div class="card-body">';
        html += '<h6 class="card-title"><i class="fas fa-wallet mr-2"></i>Cartera</h6>';
        html += '<h3 class="text-info">' + stats.purses + '</h3>';
        html += '<p class="text-muted mb-0">Registros de cartera de estudiantes</p>';
        html += '</div></div></div>';
        
        html += '<div class="col-md-4 mb-3">';
        html += '<div class="card">';
        html += '<div class="card-body">';
        html += '<h6 class="card-title"><i class="fas fa-history mr-2"></i>Historial de Cartera</h6>';
        html += '<h3 class="text-secondary">' + stats.history_purses + '</h3>';
        html += '<p class="text-muted mb-0">Registros de historial</p>';
        html += '</div></div></div>';
        
        html += '<div class="col-md-4 mb-3">';
        html += '<div class="card">';
        html += '<div class="card-body">';
        html += '<h6 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>Abonos</h6>';
        html += '<h3 class="text-success">' + stats.entries + '</h3>';
        html += '<p class="text-muted mb-0">Recibos de pago</p>';
        html += '</div></div></div>';
        
        html += '<div class="col-md-4 mb-3">';
        html += '<div class="card">';
        html += '<div class="card-body">';
        html += '<h6 class="card-title"><i class="fas fa-coins mr-2"></i>Otros Abonos</h6>';
        html += '<h3 class="text-warning">' + stats.other_entries + '</h3>';
        html += '<p class="text-muted mb-0">Otros recibos de pago</p>';
        html += '</div></div></div>';
        
        html += '</div>';
        html += '<div class="alert alert-warning mt-3 mb-0">';
        html += '<i class="fas fa-exclamation-triangle mr-2"></i>';
        html += '<strong>Advertencia:</strong> Al eliminar los costos, se eliminarán también todos los registros relacionados listados arriba.';
        html += '</div>';

        contenido.innerHTML = html;
        div.style.display = 'block';
    }

    // Investigar Abonos Problemáticos
    document.getElementById('btnInvestigarAbonos').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Investigando...';

        fetch('{{ route("maintenance.investigar-abonos") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search mr-2"></i>Investigar Abonos Problemáticos';

            if (data.success) {
                mostrarResultadosAbonos(data);
            } else {
                mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search mr-2"></i>Investigar Abonos Problemáticos';
            console.error('Error:', error);
            mostrarModalResultado('error', 'Error', 'Error al investigar abonos: ' + error.message);
        });
    });

    // Eliminar Abonos Problemáticos - Mostrar modal de confirmación
    document.getElementById('btnEliminarAbonos').addEventListener('click', function() {
        $('#modalConfirmarEliminarAbonos').modal('show');
    });

    // Confirmar eliminación de abonos
    document.getElementById('btnConfirmarEliminarAbonos').addEventListener('click', function() {
        $('#modalConfirmarEliminarAbonos').modal('hide');
        
        const btnOriginal = document.getElementById('btnEliminarAbonos');
        const btnConfirmar = this;
        btnConfirmar.disabled = true;
        btnOriginal.disabled = true;
        btnOriginal.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Eliminando...';

        fetch('{{ route("maintenance.eliminar-abonos") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btnConfirmar.disabled = false;
            btnOriginal.disabled = false;
            btnOriginal.innerHTML = '<i class="fas fa-trash mr-2"></i>Eliminar Abonos Problemáticos';

            if (data.success) {
                let mensaje = '<p><strong>Éxito:</strong> ' + data.message + '</p>';
                mensaje += '<div class="alert alert-info mt-3 mb-0">';
                mensaje += '<strong>Elementos eliminados:</strong><ul class="mb-0 mt-2">';
                mensaje += '<li><strong>Entries:</strong> ' + data.eliminados.entries + '</li>';
                mensaje += '<li><strong>Other Entries:</strong> ' + data.eliminados.other_entries + '</li>';
                mensaje += '<li><strong>Costs:</strong> ' + data.eliminados.costs + '</li>';
                mensaje += '</ul></div>';
                mostrarModalResultado('success', 'Éxito', mensaje, true);
            } else {
                mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            btnConfirmar.disabled = false;
            btnOriginal.disabled = false;
            btnOriginal.innerHTML = '<i class="fas fa-trash mr-2"></i>Eliminar Abonos Problemáticos';
            console.error('Error:', error);
            mostrarModalResultado('error', 'Error', 'Error al eliminar abonos: ' + error.message);
        });
    });

    // Investigar Matrículas Problemáticas
    document.getElementById('btnInvestigarMatriculas').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Investigando...';

        fetch('{{ route("maintenance.investigar-matriculas") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search mr-2"></i>Investigar Matrículas Problemáticas';

            if (data.success) {
                mostrarResultadosMatriculas(data);
            } else {
                mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search mr-2"></i>Investigar Matrículas Problemáticas';
            console.error('Error:', error);
            mostrarModalResultado('error', 'Error', 'Error al investigar matrículas: ' + error.message);
        });
    });

    // Reparar Matrículas Problemáticas - Mostrar modal de confirmación
    document.getElementById('btnRepararMatriculas').addEventListener('click', function() {
        $('#modalConfirmarRepararMatriculas').modal('show');
    });

    // Confirmar reparación de matrículas
    document.getElementById('btnConfirmarRepararMatriculas').addEventListener('click', function() {
        $('#modalConfirmarRepararMatriculas').modal('hide');
        
        const btnOriginal = document.getElementById('btnRepararMatriculas');
        const btnConfirmar = this;
        btnConfirmar.disabled = true;
        btnOriginal.disabled = true;
        btnOriginal.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Reparando...';

        fetch('{{ route("maintenance.reparar-matriculas") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btnConfirmar.disabled = false;
            btnOriginal.disabled = false;
            btnOriginal.innerHTML = '<i class="fas fa-wrench mr-2"></i>Reparar Matrículas Problemáticas';

            if (data.success) {
                let mensaje = '<p><strong>Éxito:</strong> ' + data.message + '</p>';
                mensaje += '<div class="alert alert-success mt-3 mb-0">';
                mensaje += '<strong>Matrículas reparadas:</strong> ' + data.reparadas;
                mensaje += '</div>';
                mostrarModalResultado('success', 'Éxito', mensaje, true);
            } else {
                mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
            }
        })
        .catch(error => {
            btnConfirmar.disabled = false;
            btnOriginal.disabled = false;
            btnOriginal.innerHTML = '<i class="fas fa-wrench mr-2"></i>Reparar Matrículas Problemáticas';
            console.error('Error:', error);
            mostrarModalResultado('error', 'Error', 'Error al reparar matrículas: ' + error.message);
        });
    });

    function mostrarResultadosAbonos(data) {
        const div = document.getElementById('resultadosInvestigacion');
        const contenido = document.getElementById('contenidoResultados');
        
        let html = '<div class="alert alert-info"><strong>Total de problemas encontrados: ' + data.total + '</strong></div>';
        
        html += '<div class="row">';
        
        // Entries sin estudiante
        if (data.problemas.entries_sin_estudiante.length > 0) {
            html += '<div class="col-md-6 mb-3">';
            html += '<h6 class="text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Entries sin Estudiante (' + data.problemas.entries_sin_estudiante.length + ')</h6>';
            html += '<ul class="list-group">';
            data.problemas.entries_sin_estudiante.slice(0, 10).forEach(item => {
                html += '<li class="list-group-item">';
                html += '<strong>ID Entry:</strong> ' + item.entry.id + '<br>';
                html += '<strong>No. Recibo:</strong> ' + (item.entry.no_recibo || 'N/A') + '<br>';
                html += '<strong>Cód. Alumno:</strong> ' + (item.cod_alumno || 'N/A');
                html += '</li>';
            });
            if (data.problemas.entries_sin_estudiante.length > 10) {
                html += '<li class="list-group-item text-muted">... y ' + (data.problemas.entries_sin_estudiante.length - 10) + ' más</li>';
            }
            html += '</ul></div>';
        }

        // Entries sin programa
        if (data.problemas.entries_sin_programa.length > 0) {
            html += '<div class="col-md-6 mb-3">';
            html += '<h6 class="text-warning"><i class="fas fa-exclamation-triangle mr-2"></i>Entries sin Programa (' + data.problemas.entries_sin_programa.length + ')</h6>';
            html += '<ul class="list-group">';
            data.problemas.entries_sin_programa.slice(0, 10).forEach(item => {
                html += '<li class="list-group-item">';
                html += '<strong>ID Entry:</strong> ' + item.entry.id + '<br>';
                html += '<strong>Estudiante:</strong> ' + (item.student?.nombre || 'N/A') + '<br>';
                html += '<strong>Cód. Alumno:</strong> ' + item.cod_alumno;
                html += '</li>';
            });
            if (data.problemas.entries_sin_programa.length > 10) {
                html += '<li class="list-group-item text-muted">... y ' + (data.problemas.entries_sin_programa.length - 10) + ' más</li>';
            }
            html += '</ul></div>';
        }

        // Other Entries sin estudiante
        if (data.problemas.other_entries_sin_estudiante.length > 0) {
            html += '<div class="col-md-6 mb-3">';
            html += '<h6 class="text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Other Entries sin Estudiante (' + data.problemas.other_entries_sin_estudiante.length + ')</h6>';
            html += '<ul class="list-group">';
            data.problemas.other_entries_sin_estudiante.slice(0, 10).forEach(item => {
                html += '<li class="list-group-item">';
                html += '<strong>ID Other Entry:</strong> ' + item.other_entry.id + '<br>';
                html += '<strong>No. Recibo:</strong> ' + (item.other_entry.no_recibo || 'N/A') + '<br>';
                html += '<strong>Cód. Alumno:</strong> ' + (item.cod_alumno || 'N/A');
                html += '</li>';
            });
            if (data.problemas.other_entries_sin_estudiante.length > 10) {
                html += '<li class="list-group-item text-muted">... y ' + (data.problemas.other_entries_sin_estudiante.length - 10) + ' más</li>';
            }
            html += '</ul></div>';
        }

        // Other Entries sin programa
        if (data.problemas.other_entries_sin_programa.length > 0) {
            html += '<div class="col-md-6 mb-3">';
            html += '<h6 class="text-warning"><i class="fas fa-exclamation-triangle mr-2"></i>Other Entries sin Programa (' + data.problemas.other_entries_sin_programa.length + ')</h6>';
            html += '<ul class="list-group">';
            data.problemas.other_entries_sin_programa.slice(0, 10).forEach(item => {
                html += '<li class="list-group-item">';
                html += '<strong>ID Other Entry:</strong> ' + item.other_entry.id + '<br>';
                html += '<strong>Estudiante:</strong> ' + (item.student?.nombre || 'N/A') + '<br>';
                html += '<strong>Cód. Alumno:</strong> ' + item.cod_alumno;
                html += '</li>';
            });
            if (data.problemas.other_entries_sin_programa.length > 10) {
                html += '<li class="list-group-item text-muted">... y ' + (data.problemas.other_entries_sin_programa.length - 10) + ' más</li>';
            }
            html += '</ul></div>';
        }

        html += '</div>';

        if (data.total === 0) {
            html = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>No se encontraron problemas. Todos los abonos están correctamente vinculados.</div>';
        }

        contenido.innerHTML = html;
        div.style.display = 'block';
    }

    function mostrarResultadosMatriculas(data) {
        const div = document.getElementById('resultadosMatriculas');
        const contenido = document.getElementById('contenidoResultadosMatriculas');
        
        let html = '<div class="alert alert-info"><strong>Total de matrículas problemáticas encontradas: ' + data.total + '</strong></div>';
        
        if (data.total > 0) {
            html += '<div class="table-responsive">';
            html += '<table class="table table-striped table-hover">';
            html += '<thead><tr><th>Cód. Alumno</th><th>Nombre</th><th>Cédula</th><th>Problemas</th></tr></thead>';
            html += '<tbody>';
            data.problemas.slice(0, 50).forEach(item => {
                html += '<tr>';
                html += '<td>' + item.matricula.cod_alumno + '</td>';
                html += '<td>' + (item.matricula.nombre_completo || 'N/A') + '</td>';
                html += '<td>' + (item.matricula.numero_documento || 'N/A') + '</td>';
                html += '<td><span class="badge badge-warning">' + item.problemas.join(', ') + '</span></td>';
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            
            if (data.total > 50) {
                html += '<div class="alert alert-secondary">Mostrando 50 de ' + data.total + ' matrículas problemáticas</div>';
            }
        } else {
            html = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>No se encontraron problemas. Todas las matrículas están correctamente configuradas.</div>';
        }

        contenido.innerHTML = html;
        div.style.display = 'block';
    }

    // Función para mostrar modales de resultado
    function mostrarModalResultado(tipo, titulo, mensaje, recargar = false) {
        const modal = $('#modalResultado');
        const header = $('#modalResultadoHeader');
        const title = $('#modalResultadoLabel');
        const body = $('#modalResultadoBody');
        const closeBtn = header.find('.close');

        // Configurar colores según el tipo
        if (tipo === 'success') {
            header.removeClass('bg-danger bg-warning bg-info').addClass('bg-success');
            title.removeClass('text-dark').addClass('text-white');
            closeBtn.removeClass('text-dark').addClass('text-white');
            title.html('<i class="fas fa-check-circle mr-2"></i>' + titulo);
            body.html(mensaje);
        } else if (tipo === 'error') {
            header.removeClass('bg-success bg-warning bg-info').addClass('bg-danger');
            title.removeClass('text-dark').addClass('text-white');
            closeBtn.removeClass('text-dark').addClass('text-white');
            title.html('<i class="fas fa-exclamation-circle mr-2"></i>' + titulo);
            // Si el mensaje ya contiene HTML, no envolver en alert
            if (mensaje.indexOf('<') === -1) {
                body.html('<div class="alert alert-danger mb-0">' + mensaje + '</div>');
            } else {
                body.html(mensaje);
            }
        } else if (tipo === 'warning') {
            header.removeClass('bg-success bg-danger bg-info').addClass('bg-warning');
            title.removeClass('text-white').addClass('text-dark');
            closeBtn.removeClass('text-white').addClass('text-dark');
            title.html('<i class="fas fa-exclamation-triangle mr-2"></i>' + titulo);
            if (mensaje.indexOf('<') === -1) {
                body.html('<div class="alert alert-warning mb-0">' + mensaje + '</div>');
            } else {
                body.html(mensaje);
            }
        } else {
            header.removeClass('bg-success bg-danger bg-warning').addClass('bg-info');
            title.removeClass('text-dark').addClass('text-white');
            closeBtn.removeClass('text-dark').addClass('text-white');
            title.html('<i class="fas fa-info-circle mr-2"></i>' + titulo);
            if (mensaje.indexOf('<') === -1) {
                body.html('<div class="alert alert-info mb-0">' + mensaje + '</div>');
            } else {
                body.html(mensaje);
            }
        }

        // Si debe recargar después de cerrar el modal
        if (recargar) {
            modal.off('hidden.bs.modal').on('hidden.bs.modal', function() {
                location.reload();
            });
        } else {
            modal.off('hidden.bs.modal');
        }

        modal.modal('show');
    }

    // ========== FUNCIONALIDAD DE LIMPIEZA DE DATOS DE PRUEBA ==========
    
    // Ver Datos a Eliminar
    const btnVerDatos = document.getElementById('btnVerDatos');
    if (btnVerDatos) {
        btnVerDatos.addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Cargando...';

            fetch('{{ route("maintenance.obtener-vista-previa-datos") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-eye mr-2"></i>Ver Datos a Eliminar';

                if (data.success) {
                    mostrarTablaDatos(data.datos, data.total);
                } else {
                    mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-eye mr-2"></i>Ver Datos a Eliminar';
                console.error('Error:', error);
                mostrarModalResultado('error', 'Error', 'Error al obtener datos: ' + error.message);
            });
        });
    }

    function mostrarTablaDatos(datos, total) {
        const div = document.getElementById('tablaDatosEliminar');
        const tbody = document.getElementById('tbodyDatos');
        
        if (!div || !tbody) return;
        
        let html = '';
        datos.forEach(item => {
            const badgeClass = item.cantidad > 0 ? 'badge-danger' : 'badge-secondary';
            html += '<tr>';
            html += '<td><code>' + item.tabla + '</code></td>';
            html += '<td><strong>' + item.nombre + '</strong></td>';
            html += '<td><span class="badge ' + badgeClass + '">' + item.cantidad + '</span></td>';
            html += '<td>' + item.descripcion + '</td>';
            html += '<td>';
            if (item.cantidad > 0) {
                html += '<button type="button" class="btn btn-danger btn-sm" onclick="limpiarTablaEspecifica(\'' + item.tabla + '\', \'' + item.nombre.replace(/'/g, "\\'") + '\')" title="Eliminar">';
                html += '<i class="fas fa-trash mr-1"></i>Eliminar';
                html += '</button>';
            } else {
                html += '<span class="text-muted">Sin datos</span>';
            }
            html += '</td>';
            html += '</tr>';
        });
        
        tbody.innerHTML = html;
        div.style.display = 'block';
        
        // Scroll suave a la tabla
        div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    window.limpiarTablaEspecifica = function(tabla, nombre) {
        showConfirmModal(
            '¿Está seguro de eliminar todos los registros de "' + nombre + '"? Esta acción no se puede deshacer.',
            'Confirmar Eliminación',
            'Eliminar',
            'Cancelar',
            'btn-danger'
        ).then(confirmed => {
            if (confirmed) {
                const btn = event.target.closest('button');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Eliminando...';
                }

                fetch('{{ route("maintenance.limpiar-tabla-especifica") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ tabla: tabla })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarModalResultado('success', 'Éxito', 
                            '<p><strong>Éxito:</strong> ' + data.message + '</p>' +
                            '<div class="alert alert-info mt-3 mb-0">' +
                            '<strong>Registros eliminados:</strong> ' + data.cantidad +
                            '</div>', 
                            true
                        );
                    } else {
                        mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-trash mr-1"></i>Eliminar';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarModalResultado('error', 'Error', 'Error al eliminar: ' + error.message);
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash mr-1"></i>Eliminar';
                    }
                });
            }
        });
    };
    
    // Validar confirmación de texto
    const confirmacionTexto = document.getElementById('confirmacionTexto');
    const btnConfirmarLimpieza = document.getElementById('btnConfirmarLimpieza');
    
    if (confirmacionTexto && btnConfirmarLimpieza) {
        confirmacionTexto.addEventListener('input', function() {
            if (this.value.trim().toUpperCase() === 'ELIMINAR') {
                btnConfirmarLimpieza.disabled = false;
            } else {
                btnConfirmarLimpieza.disabled = true;
            }
        });
    }

    // Actualizar estadísticas
    const btnActualizarStats = document.getElementById('btnActualizarStats');
    if (btnActualizarStats) {
        btnActualizarStats.addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';

            fetch('{{ route("maintenance.get-stats-datos-prueba") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Actualizar Estadísticas';

                if (data.success) {
                    actualizarEstadisticas(data.stats);
                    mostrarModalResultado('success', 'Éxito', 'Estadísticas actualizadas correctamente.');
                } else {
                    mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Actualizar Estadísticas';
                console.error('Error:', error);
                mostrarModalResultado('error', 'Error', 'Error al actualizar estadísticas: ' + error.message);
            });
        });
    }

    // Mostrar modal de confirmación para limpiar datos
    const btnLimpiarDatos = document.getElementById('btnLimpiarDatos');
    if (btnLimpiarDatos) {
        btnLimpiarDatos.addEventListener('click', function() {
            $('#modalConfirmarLimpieza').modal('show');
            if (confirmacionTexto) {
                confirmacionTexto.value = '';
                btnConfirmarLimpieza.disabled = true;
            }
        });
    }

    // Confirmar limpieza de datos
    if (btnConfirmarLimpieza) {
        btnConfirmarLimpieza.addEventListener('click', function() {
            $('#modalConfirmarLimpieza').modal('hide');
            
            const btnOriginal = document.getElementById('btnLimpiarDatos');
            const btnConfirmar = this;
            btnConfirmar.disabled = true;
            btnOriginal.disabled = true;
            btnOriginal.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Limpiando...';

            fetch('{{ route("maintenance.limpiar-datos-prueba") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                btnConfirmar.disabled = false;
                btnOriginal.disabled = false;
                btnOriginal.innerHTML = '<i class="fas fa-trash-alt mr-2"></i>Limpiar Todos los Datos de Prueba';

                if (data.success) {
                    let mensaje = '<p><strong>Éxito:</strong> ' + data.message + '</p>';
                    mensaje += '<div class="alert alert-success mt-3 mb-0">';
                    mensaje += '<strong>Total de registros eliminados: ' + data.total + '</strong><hr>';
                    mensaje += '<div class="row">';
                    mensaje += '<div class="col-md-6"><ul class="mb-0">';
                    mensaje += '<li><strong>Abonos:</strong> ' + data.eliminados.entries + '</li>';
                    mensaje += '<li><strong>Otros Abonos:</strong> ' + data.eliminados.other_entries + '</li>';
                    mensaje += '<li><strong>Costos:</strong> ' + data.eliminados.costs + '</li>';
                    mensaje += '<li><strong>Matrículas:</strong> ' + data.eliminados.matriculas + '</li>';
                    mensaje += '<li><strong>Cartera:</strong> ' + data.eliminados.purses + '</li>';
                    mensaje += '<li><strong>Historial Cartera:</strong> ' + data.eliminados.history_purses + '</li>';
                    mensaje += '<li><strong>Recibos Terceros:</strong> ' + data.eliminados.third_receipts + '</li>';
                    mensaje += '</ul></div>';
                    mensaje += '<div class="col-md-6"><ul class="mb-0">';
                    mensaje += '<li><strong>Recibos Egresos:</strong> ' + data.eliminados.egreso_receipts + '</li>';
                    mensaje += '<li><strong>Proveedores:</strong> ' + data.eliminados.egreso_providers + '</li>';
                    mensaje += '<li><strong>Terceros:</strong> ' + data.eliminados.third_entries + '</li>';
                    mensaje += '<li><strong>Actividades Terceros:</strong> ' + data.eliminados.third_activities + '</li>';
                    mensaje += '<li><strong>Bases de Caja:</strong> ' + data.eliminados.cash_bases + '</li>';
                    mensaje += '<li><strong>Balances Iniciales:</strong> ' + data.eliminados.initial_balances + '</li>';
                    mensaje += '</ul></div></div>';
                    mensaje += '</div>';
                    mostrarModalResultado('success', 'Éxito', mensaje, true);
                } else {
                    mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
                }
            })
            .catch(error => {
                btnConfirmar.disabled = false;
                btnOriginal.disabled = false;
                btnOriginal.innerHTML = '<i class="fas fa-trash-alt mr-2"></i>Limpiar Todos los Datos de Prueba';
                console.error('Error:', error);
                mostrarModalResultado('error', 'Error', 'Error al limpiar datos: ' + error.message);
            });
        });
    }

    function actualizarEstadisticas(stats) {
        document.getElementById('stat-entries').textContent = stats.entries || 0;
        document.getElementById('stat-other_entries').textContent = stats.other_entries || 0;
        document.getElementById('stat-costs').textContent = stats.costs || 0;
        document.getElementById('stat-matriculas').textContent = stats.matriculas || 0;
        document.getElementById('stat-purses').textContent = stats.purses || 0;
        document.getElementById('stat-history_purses').textContent = stats.history_purses || 0;
        document.getElementById('stat-third_receipts').textContent = stats.third_receipts || 0;
        document.getElementById('stat-egreso_receipts').textContent = stats.egreso_receipts || 0;
        document.getElementById('stat-egreso_providers').textContent = stats.egreso_providers || 0;
        document.getElementById('stat-third_entries').textContent = stats.third_entries || 0;
        document.getElementById('stat-third_activities').textContent = stats.third_activities || 0;
        document.getElementById('stat-cash_bases').textContent = stats.cash_bases || 0;
        document.getElementById('stat-initial_balances').textContent = stats.initial_balances || 0;
        document.getElementById('stat-total').textContent = stats.total || 0;
    }

    // ========== FUNCIONALIDAD DE VERIFICACIÓN DE ELIMINACIONES ==========
    
    const btnVerificarEliminaciones = document.getElementById('btnVerificarEliminaciones');
    if (btnVerificarEliminaciones) {
        btnVerificarEliminaciones.addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Verificando...';

            fetch('{{ route("maintenance.verificar-eliminaciones") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-search mr-2"></i>Verificar Eliminaciones';

                if (data.success) {
                    mostrarResultadosVerificacion(data);
                } else {
                    mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-search mr-2"></i>Verificar Eliminaciones';
                console.error('Error:', error);
                mostrarModalResultado('error', 'Error', 'Error al verificar eliminaciones: ' + error.message);
            });
        });
    }

    function mostrarResultadosVerificacion(data) {
        const div = document.getElementById('resultadosVerificacion');
        const contenido = document.getElementById('contenidoVerificacion');
        
        if (!div || !contenido) return;

        let html = '';
        
        // Resumen
        html += '<div class="alert alert-' + (data.total_problemas > 0 ? 'warning' : 'success') + ' mb-4">';
        html += '<h5 class="mb-2"><i class="fas fa-info-circle mr-2"></i>Resumen de la Verificación</h5>';
        html += '<div class="row">';
        html += '<div class="col-md-4"><strong>Total de Tablas:</strong> ' + data.resumen.total_tablas + '</div>';
        html += '<div class="col-md-4"><strong>Tablas OK:</strong> <span class="badge badge-success">' + data.resumen.tablas_ok + '</span></div>';
        html += '<div class="col-md-4"><strong>Tablas con Problemas:</strong> <span class="badge badge-warning">' + data.resumen.tablas_con_problemas + '</span></div>';
        html += '</div>';
        html += '</div>';

        // Tabla de resultados
        html += '<div class="table-responsive">';
        html += '<table class="table table-striped table-bordered">';
        html += '<thead class="thead-dark" style="background-color: #343a40 !important;">';
        html += '<tr>';
        html += '<th style="color: white !important;">Tabla</th>';
        html += '<th style="color: white !important;">Total en BD</th>';
        html += '<th style="color: white !important;">Total en Modelo</th>';
        html += '<th style="color: white !important;">Diferencia</th>';
        html += '<th style="color: white !important;">IDs Huérfanos</th>';
        html += '<th style="color: white !important;">Estado</th>';
        html += '<th style="color: white !important;">Acciones</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        for (const [tabla, resultado] of Object.entries(data.resultados)) {
            if (resultado.error) {
                html += '<tr class="table-danger">';
                html += '<td><strong>' + resultado.nombre + '</strong></td>';
                html += '<td colspan="5"><span class="text-danger">Error: ' + resultado.error + '</span></td>';
                html += '<td>-</td>';
            } else {
                const estadoClass = resultado.estado === 'ok' ? 'success' : 'warning';
                const estadoIcon = resultado.estado === 'ok' ? 'check-circle' : 'exclamation-triangle';
                const estadoTexto = resultado.estado === 'ok' ? 'OK' : 'Problema';
                
                html += '<tr class="table-' + (resultado.estado === 'ok' ? 'success' : 'warning') + '">';
                html += '<td><strong>' + resultado.nombre + '</strong><br><small class="text-muted">' + tabla + '</small></td>';
                html += '<td>' + resultado.total_en_tabla + '</td>';
                html += '<td>' + resultado.total_en_modelo + '</td>';
                html += '<td><span class="badge badge-' + (resultado.diferencia > 0 ? 'danger' : 'success') + '">' + resultado.diferencia + '</span></td>';
                html += '<td>';
                if (resultado.ids_huerfanos && resultado.ids_huerfanos.length > 0) {
                    html += '<span class="badge badge-danger">' + resultado.ids_huerfanos.length + ' IDs</span><br>';
                    html += '<small>' + resultado.ids_huerfanos.slice(0, 5).join(', ') + (resultado.ids_huerfanos.length > 5 ? '...' : '') + '</small>';
                } else {
                    html += '<span class="text-success">Ninguno</span>';
                }
                html += '</td>';
                html += '<td><span class="badge badge-' + estadoClass + '"><i class="fas fa-' + estadoIcon + ' mr-1"></i>' + estadoTexto + '</span></td>';
                html += '<td>';
                if (resultado.tiene_problemas && resultado.ids_huerfanos && resultado.ids_huerfanos.length > 0) {
                    html += '<button type="button" class="btn btn-danger btn-sm" onclick="forzarEliminacionFisica(\'' + tabla + '\', ' + JSON.stringify(resultado.ids_huerfanos) + ', \'' + resultado.nombre + '\')" title="Eliminar físicamente">';
                    html += '<i class="fas fa-trash mr-1"></i>Eliminar';
                    html += '</button>';
                } else {
                    html += '<span class="text-muted">-</span>';
                }
                html += '</td>';
                html += '</tr>';
            }
        }

        html += '</tbody>';
        html += '</table>';
        html += '</div>';

        contenido.innerHTML = html;
        div.style.display = 'block';
        
        // Scroll suave a los resultados
        div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    window.forzarEliminacionFisica = function(tabla, ids, nombre) {
        showConfirmModal(
            '¿Está seguro de eliminar físicamente ' + ids.length + ' registro(s) huérfano(s) de "' + nombre + '"? Esta acción no se puede deshacer.',
            'Confirmar Eliminación Física',
            'Eliminar',
            'Cancelar',
            'btn-danger'
        ).then(confirmed => {
            if (confirmed) {
                const btn = event.target.closest('button');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Eliminando...';
                }

                fetch('{{ route("maintenance.forzar-eliminacion-fisica") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        tabla: tabla,
                        ids: ids
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash mr-1"></i>Eliminar';
                    }

                    if (data.success) {
                        mostrarModalResultado('success', 'Éxito', data.message, true);
                        // Recargar la verificación
                        if (btnVerificarEliminaciones) {
                            btnVerificarEliminaciones.click();
                        }
                    } else {
                        mostrarModalResultado('error', 'Error', data.message || 'Error desconocido');
                    }
                })
                .catch(error => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash mr-1"></i>Eliminar';
                    }
                    console.error('Error:', error);
                    mostrarModalResultado('error', 'Error', 'Error al eliminar registros: ' + error.message);
                });
            }
        });
    };
});
</script>
@endsection
