<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GradesSheetGenerateRequest;
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

class GradesSheetController extends Controller
{
    /**
     * POST /api/v1/grades-sheet/generate — genera PDF de planilla de notas.
     * Stream: Content-Type: application/pdf, Content-Disposition: attachment; filename="planilla_notas_<rango>.pdf"
     */
    #[
        OA\Post(
            path: '/api/v1/grades-sheet/generate',
            summary: 'Generar planilla de notas (PDF)',
            description: 'Genera y devuelve un PDF de planilla de notas con los datos especificados.',
            tags: ['Grades Sheet'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['program_id', 'schedule_id', 'group_id', 'teacher_id', 'module_id', 'fecha_inicio', 'fecha_final'],
                    properties: [
                        new OA\Property(property: 'program_id', type: 'integer', example: 1, description: 'ID del programa'),
                        new OA\Property(property: 'schedule_id', type: 'integer', example: 1, description: 'ID del horario'),
                        new OA\Property(property: 'group_id', type: 'integer', example: 1, description: 'ID del grupo'),
                        new OA\Property(property: 'teacher_id', type: 'integer', example: 1, description: 'ID del docente'),
                        new OA\Property(property: 'module_id', type: 'integer', example: 1, description: 'ID del módulo'),
                        new OA\Property(property: 'fecha_inicio', type: 'string', format: 'date', example: '2024-01-01', description: 'Fecha de inicio del período'),
                        new OA\Property(property: 'fecha_final', type: 'string', format: 'date', example: '2024-01-31', description: 'Fecha final del período'),
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
                    )
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
    public function generate(GradesSheetGenerateRequest $request): Response
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
            'estudiantes' => $estudiantes,
            'hideDefaultFooter' => true,
        ];

        try {
            $dompdf = new Dompdf();
            $html = view('academic-management.planillas.notas.pdf', $data)->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $canvas = $dompdf->getCanvas();
            $pageWidth = $canvas->get_width();
            $fontSize = 7;
            $x = $pageWidth - 110;
            $y = 18;
            $canvas->page_text($x, $y, 'Página {PAGE_NUM} de {PAGE_COUNT}', 'Helvetica', $fontSize, [0, 0, 0]);

            $nombreArchivo = 'planilla_notas_'
                . str_replace(' ', '_', $programa->name) . '_'
                . str_replace(' ', '_', $grupo->name) . '_'
                . date('Y-m-d') . '.pdf';

            $pdfBinary = $dompdf->output();

            return response($pdfBinary, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
        } catch (Throwable $e) {
            \Log::error('Error al generar PDF de planilla de notas (API): ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

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
