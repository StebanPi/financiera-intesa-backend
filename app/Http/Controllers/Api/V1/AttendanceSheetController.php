<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AttendanceSheetGenerateRequest;
use App\Models\Group;
use App\Models\InstitutionSetting;
use App\Models\Matricula;
use App\Models\Module;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Support\ApiResponse;
use Dompdf\Dompdf;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Throwable;
use OpenApi\Attributes as OA;

class AttendanceSheetController extends Controller
{
    /**
     * POST /api/v1/attendance-sheet/generate — genera PDF de planilla de asistencia.
     * Stream: Content-Type: application/pdf, Content-Disposition: inline; filename="planilla_asistencia_<rango>.pdf"
     * Reutiliza la misma vista y lógica que AcademicManagement\AttendanceSheetController.
     */
    #[
        OA\Post(
            path: '/api/v1/attendance-sheet/generate',
            summary: 'Generar planilla de asistencia (PDF)',
            description: 'Genera y devuelve un PDF de planilla de asistencia con los datos especificados. El PDF se devuelve como binario para visualización inline.',
            tags: ['Attendance Sheet'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['program_id', 'schedule_id', 'group_id', 'teacher_id', 'module_id', 'fecha_inicio', 'fecha_final', 'fecha_clase'],
                    properties: [
                        new OA\Property(property: 'program_id', type: 'integer', example: 1, description: 'ID del programa'),
                        new OA\Property(property: 'schedule_id', type: 'integer', example: 1, description: 'ID del horario'),
                        new OA\Property(property: 'group_id', type: 'integer', example: 1, description: 'ID del grupo'),
                        new OA\Property(property: 'teacher_id', type: 'integer', example: 1, description: 'ID del docente'),
                        new OA\Property(property: 'module_id', type: 'integer', example: 1, description: 'ID del módulo'),
                        new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date', example: '2024-01-01', description: 'Fecha de inicio del período'),
                        new OA\Property(property: 'fecha_final', type: 'string', format: 'date', example: '2024-01-31', description: 'Fecha final del período'),
                        new OA\Property(property: 'fecha_clase', type: 'string', format: 'date', example: '2024-01-15', description: 'Fecha de la clase (debe estar dentro del rango de fechas)'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'PDF generado exitosamente',
                    content: new OA\MediaType(
                        mediaType: 'application/pdf',
                        schema: new OA\Schema(
                            type: 'string',
                            format: 'binary'
                        )
                    ),
                    headers: [
                        new OA\Header(
                            header: 'Content-Disposition',
                            schema: new OA\Schema(type: 'string'),
                            description: 'inline; filename="planilla_asistencia_Programa_Grupo_2024-01-15.pdf"'
                        ),
                    ]
                ),
                new OA\Response(
                    response: 401,
                    description: 'No autenticado',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
                new OA\Response(
                    response: 422,
                    description: 'Errores de validación',
                    content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
                ),
                new OA\Response(
                    response: 500,
                    description: 'Error interno del servidor',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
            ]
        )
    ]
    public function generate(AttendanceSheetGenerateRequest $request): Response
    {
        $programa = Program::findOrFail($request->program_id);
        $horario = Schedule::findOrFail($request->schedule_id);
        $grupo = Group::findOrFail($request->group_id);
        $docente = Teacher::findOrFail($request->teacher_id);
        $modulo = Module::findOrFail($request->module_id);

        $institucion = InstitutionSetting::getSettings();

        $estudiantes = Matricula::where('programa', $programa->name)
            ->where('horario', $horario->name)
            ->where('numero_grupo', $grupo->name)
            ->orderBy('nombre_completo', 'asc')
            ->get();

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
            'hideDefaultFooter' => true,
        ];

        try {
            $dompdf = new Dompdf();
            $html = view('academic-management.planillas.asistencia.pdf', $data)->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $canvas = $dompdf->getCanvas();
            $pageWidth = $canvas->get_width();
            $fontSize = 7;
            $x = $pageWidth - 110;
            $y = 18;
            $canvas->page_text($x, $y, 'Página {PAGE_NUM} de {PAGE_COUNT}', 'Helvetica', $fontSize, [0, 0, 0]);

            $nombreArchivo = 'planilla_asistencia_'
                . str_replace(' ', '_', $programa->name) . '_'
                . str_replace(' ', '_', $grupo->name) . '_'
                . date('Y-m-d', strtotime($request->fecha_clase)) . '.pdf';

            $pdfBinary = $dompdf->output();

            return response($pdfBinary, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
        } catch (Throwable $e) {
            \Log::error('Error al generar PDF de planilla de asistencia (API): ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            // Usar request_id del middleware RequestIdMiddleware
            $traceId = $request->attributes->get('request_id') ?? $request->header('X-Request-Id') ?? Str::uuid()->toString();
            $message = config('app.debug') ? 'Error al generar el PDF: ' . $e->getMessage() : 'Error al generar el PDF.';
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                ApiResponse::error(
                    'SERVER_ERROR',
                    $message,
                    null,
                    500,
                    ['trace_id' => $traceId]
                )
            );
        }
    }
}
