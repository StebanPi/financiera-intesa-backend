<?php

namespace App\Http\Controllers\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceSheetRequest;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\Module;
use App\Models\InstitutionSetting;
use App\Models\Matricula;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class AttendanceSheetController extends Controller
{
    /**
     * Mostrar el formulario para generar planilla de asistencia
     */
    public function create()
    {
        $programs = Program::where('active', true)->orderBy('name')->get();
        $schedules = Schedule::where('active', true)->orderBy('name')->get();
        $groups = Group::where('active', true)->orderBy('name')->get();
        $teachers = Teacher::where('active', true)->orderBy('name')->get();
        $modules = Module::where('active', true)->orderBy('name')->get();

        return view('academic-management.planillas.asistencia.create', [
            'programs' => $programs,
            'schedules' => $schedules,
            'groups' => $groups,
            'teachers' => $teachers,
            'modules' => $modules,
        ]);
    }

    /**
     * Generar el PDF de la planilla de asistencia
     */
    public function generate(AttendanceSheetRequest $request)
    {
        try {
            // Obtener datos de los catálogos
            $programa = Program::findOrFail($request->programa_id);
            $horario = Schedule::findOrFail($request->horario_id);
            $grupo = Group::findOrFail($request->grupo_id);
            $docente = Teacher::findOrFail($request->docente_id);
            $modulo = Module::findOrFail($request->modulo_id);

            // Obtener configuración de la institución
            $institucion = InstitutionSetting::getSettings();

            // Obtener estudiantes matriculados según los filtros
            // Como los campos son strings en matrícula, filtramos por coincidencia
            $estudiantes = Matricula::where('programa', $programa->name)
                ->where('horario', $horario->name)
                ->where('numero_grupo', $grupo->name)
                ->where('estado_estudiante', 'Activo')
                ->orderBy('nombre_completo', 'asc')
                ->get();

            // Preparar datos para la vista
            $data = [
                'institucion' => $institucion,
                'programa' => $programa->name,
                'horario' => $horario->name,
                'grupo' => $grupo->name,
                'docente' => $docente->name,
                'modulo' => $modulo->name,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_final' => $request->fecha_final,
                'fecha_clase' => $request->fecha_clase,
                'estudiantes' => $estudiantes,
                'hideDefaultFooter' => true, // Ocultar footer genérico para mostrar footer personalizado
            ];

            // Generar el PDF
            $dompdf = new Dompdf();
            $html = view('academic-management.planillas.asistencia.pdf', $data)->render();
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Obtener el canvas para agregar paginación dinámica
            $canvas = $dompdf->getCanvas();
            $pageWidth = $canvas->get_width();
            $pageHeight = $canvas->get_height();
            
            // Calcular posición del número de página en la parte inferior derecha
            // El footer está a 40px desde abajo (bottom: 40px) = aproximadamente 30 puntos
            // El footer-bottom (donde está la fecha) está dentro del footer-planilla
            // Necesitamos alinear el número de página con la línea de fecha
            $fontSize = 7;
            $font = 'Helvetica';
            
            // Posición horizontal: derecha con margen adecuado para el texto "Página X de Y"
            // Ajustado para que el texto quede bien alineado en la esquina
            $x = $pageWidth - 110;
            
            // Posición vertical: IMPORTANTE - En DomPDF CPDF, el método text() usa:
            // y(y) - fontHeight donde y() hace: return height - y
            // 
            // Si el texto aparece arriba con valores grandes de y, necesitamos usar valores pequeños
            // Para una página A4 (842 puntos de alto), si queremos estar a ~28-30 puntos desde abajo:
            // text() calcula: (height - y) - fontHeight
            // Con height = 842, fontHeight ≈ 10:
            // Si y = 10: (842 - 10) - 10 = 822 desde arriba = 20 desde abajo
            // Si y = 20: (842 - 20) - 10 = 812 desde arriba = 30 desde abajo ✓
            // Si y = 28: (842 - 28) - 10 = 804 desde arriba = 38 desde abajo
            // 
            // Usamos un valor pequeño para asegurar que aparezca en la parte inferior
            $y = 18; // Valor que debería posicionar el texto a ~28-30 puntos desde abajo
            
            // Usar page_text para agregar paginación dinámicamente en todas las páginas
            // El método page_text reemplaza automáticamente {PAGE_NUM} y {PAGE_COUNT}
            $canvas->page_text(
                $x,                  // x: posición horizontal desde la izquierda (puntos)
                $y,                  // y: posición vertical desde abajo (será: (height - y) - fontHeight)
                'Página {PAGE_NUM} de {PAGE_COUNT}',
                $font,               // Fuente estándar de DomPDF
                $fontSize,           // Tamaño de fuente en puntos
                [0, 0, 0]            // Color negro [R, G, B] valores entre 0 y 1
            );

            // Nombre del archivo
            $nombreArchivo = 'planilla_asistencia_' . 
                str_replace(' ', '_', $programa->name) . '_' . 
                str_replace(' ', '_', $grupo->name) . '_' . 
                date('Y-m-d', strtotime($request->fecha_clase)) . '.pdf';

            // Descargar el PDF
            return $dompdf->stream($nombreArchivo, [
                'Attachment' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de planilla de asistencia: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error al generar el PDF: ' . $e->getMessage()]);
        }
    }
}
