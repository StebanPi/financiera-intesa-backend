<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\OtherEntryStoreRequest;
use App\Http\Resources\V1\OtherEntryResource;
use App\Models\Cost;
use App\Models\OtherEntry;
use App\Services\OtherEntryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\DB;
use App\Models\InstitutionSetting;
use Illuminate\Support\Str;

class OtherEntryController extends Controller
{
    public function __construct(
        private OtherEntryService $otherEntryService
    ) {}

    /**
     * GET /other-entries?cod_alumno=...&id_cost=...&from=...&to=...
     */
    #[
        OA\Get(
            path: '/api/v1/other-entries',
            summary: 'Listar otros ingresos',
            description: 'Lista los otros ingresos con filtros opcionales y paginación.',
            tags: ['Other Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'query', required: false, description: 'Código del alumno', schema: new OA\Schema(type: 'string')),
                new OA\Parameter(name: 'id_cost', in: 'query', required: false, description: 'ID del costo', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'from', in: 'query', required: false, description: 'Fecha desde (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'to', in: 'query', required: false, description: 'Fecha hasta (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de otros ingresos', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $query = OtherEntry::query()->where('sede', $request->get('sede_activa', 'BARRANCABERMEJA'));

        if ($request->filled('cod_alumno')) {
            $cost = Cost::where('cod_alumno', $request->cod_alumno)->first();
            if ($cost) {
                $query->where('id_cost', $cost->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        if ($request->filled('id_cost')) {
            $query->where('id_cost', $request->id_cost);
        }
        if ($request->filled('from')) {
            $query->whereDate('fecha_recibo', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('fecha_recibo', '<=', $request->to);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = $query->orderBy('id')->paginate($perPage);

        return ApiResponse::success(
            OtherEntryResource::collection($paginator->items())->resolve(),
            null,
            [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            200
        );
    }

    /**
     * POST /other-entries — asigna no_recibo con consecutivo (lockForUpdate).
     */
    #[
        OA\Post(
            path: '/api/v1/other-entries',
            summary: 'Crear otro ingreso',
            description: 'Crea un nuevo otro ingreso. Asigna automáticamente el número de recibo usando un consecutivo con lockForUpdate.',
            tags: ['Other Entries'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['id_cost', 'concepto', 'descripcion', 'fecha_recibo', 'valor', 'elaborado_por', 'debe', 'haber'],
                    properties: [
                        new OA\Property(property: 'id_cost', type: 'integer', example: 1),
                        new OA\Property(property: 'concepto', type: 'integer', example: 1),
                        new OA\Property(property: 'descripcion', type: 'string', example: 'Otro concepto'),
                        new OA\Property(property: 'fecha_recibo', type: 'string', format: 'date', example: '2024-01-15'),
                        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 100000),
                        new OA\Property(property: 'elaborado_por', type: 'integer', example: 1),
                        new OA\Property(property: 'debe', type: 'integer', example: 1),
                        new OA\Property(property: 'haber', type: 'integer', example: 1),
                        new OA\Property(property: 'forma', type: 'string', enum: ['Efectivo', 'Bancos', 'Consignación'], nullable: true, example: 'Efectivo'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Otro ingreso creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(OtherEntryStoreRequest $request): JsonResponse
    {
        $entry = $this->otherEntryService->create($request->validated());

        return ApiResponse::success(new OtherEntryResource($entry), 'Otro ingreso creado.', null, 201);
    }

    /**
     * GET /other-entries/{id}
     */
    #[
        OA\Get(
            path: '/api/v1/other-entries/{id}',
            summary: 'Obtener otro ingreso',
            description: 'Obtiene los datos de un otro ingreso específico.',
            tags: ['Other Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del otro ingreso', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del otro ingreso', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Otro ingreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $entry = OtherEntry::findOrFail($id);

        return ApiResponse::success(new OtherEntryResource($entry));
    }

    /**
     * DELETE /other-entries/{id}
     */
    #[
        OA\Delete(
            path: '/api/v1/other-entries/{id}',
            summary: 'Eliminar otro ingreso',
            description: 'Elimina un otro ingreso existente.',
            tags: ['Other Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del otro ingreso', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Otro ingreso eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Otro ingreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->hasPermission('records.delete')) {
            return ApiResponse::error('No tienes permiso para eliminar registros financieros.', 403);
        }

        $this->otherEntryService->delete($id);

        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }

    /**
     * GET /other-entries/pdf/{cod_alumno}
     * Genera PDF consolidado de otros ingresos para un estudiante
     */
    public function streamOtrosIngresosPdf(string $cod_alumno)
    {
        try {
            // 1. Obtener datos del estudiante
            $data = [];
            $matricula = \App\Models\Matricula::where('cod_alumno', $cod_alumno)->first();
            if ($matricula) {
                $data = [(object)[
                    'cedula' => $matricula->numero_documento ?? '', 
                    'nombre' => $matricula->nombre_completo ?? 'N/A', 
                    'nombre_programa' => $matricula->programa ?? ''
                ]];
            }

            // 2. Obtener otros ingresos del estudiante
            // JOIN con costs para filtrar por cod_alumno
            $entries = DB::connection('mysql')->select(
                'SELECT other_entries.id, other_entries.id_cost, otros_conceptos.nombre AS concepto, other_entries.descripcion, other_entries.no_recibo, other_entries.fecha_recibo, other_entries.valor, elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe, CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, other_entries.created_at 
                 FROM other_entries 
                 INNER JOIN costs ON costs.id = other_entries.id_cost
                 INNER JOIN otros_conceptos ON otros_conceptos.id = other_entries.concepto 
                 INNER JOIN elaborados ON elaborados.id = other_entries.elaborado_por 
                 INNER JOIN debes ON debes.id = other_entries.debe 
                 INNER JOIN habers ON habers.id = other_entries.haber 
                 WHERE costs.cod_alumno = ? 
                 ORDER BY other_entries.no_recibo ASC',
                [$cod_alumno]
            );

            // 3. Configuración de institución
            $institucion = InstitutionSetting::getSettings();

            // 4. Generar HTML
            $dompdf = new Dompdf();
            $html = view('PDFs.pdf_otrosAbonos', [
                'student' => $data,
                'entries' => $entries,
                'institucion' => $institucion
            ])->render();
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();
            
            $nombreEstudiante = 'Estudiante';
            if (!empty($data) && isset($data[0]) && isset($data[0]->nombre)) {
                $nombreEstudiante = Str::slug($data[0]->nombre);
            }
            $filename = 'informe-otros-ingresos-' . $nombreEstudiante . '.pdf';
            
            $output = $dompdf->output();
            
            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Error en OtherEntryController::streamOtrosIngresosPdf', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            abort(500, 'Error al generar el documento PDF: ' . $e->getMessage());
        }
    }
}
