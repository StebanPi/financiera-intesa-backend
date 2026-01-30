<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EntryStoreRequest;
use App\Http\Resources\V1\EntryResource;
use App\Models\Cost;
use App\Models\Entry;
use App\Services\EntryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\DB;
use App\Models\InstitutionSetting;
use Illuminate\Support\Str;

class EntryController extends Controller
{
    public function __construct(
        private EntryService $entryService
    ) {}

    /**
     * GET /entries?cod_alumno=...&id_cost=...&from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    #[
        OA\Get(
            path: '/api/v1/entries',
            summary: 'Listar abonos',
            description: 'Lista los abonos (entries) con filtros opcionales y paginación.',
            tags: ['Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'query', required: false, description: 'Código del alumno', schema: new OA\Schema(type: 'string')),
                new OA\Parameter(name: 'id_cost', in: 'query', required: false, description: 'ID del costo', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'from', in: 'query', required: false, description: 'Fecha desde (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'to', in: 'query', required: false, description: 'Fecha hasta (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de abonos', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $query = Entry::query();

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
            EntryResource::collection($paginator->items())->resolve(),
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
     * POST /entries — asigna no_recibo con consecutivo (lockForUpdate).
     */
    #[
        OA\Post(
            path: '/api/v1/entries',
            summary: 'Crear abono',
            description: 'Crea un nuevo abono (entry). Asigna automáticamente el número de recibo usando un consecutivo con lockForUpdate.',
            tags: ['Entries'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['id_cost', 'concepto', 'descripcion', 'fecha_recibo', 'valor', 'elaborado_por', 'debe', 'haber'],
                    properties: [
                        new OA\Property(property: 'id_cost', type: 'integer', example: 1),
                        new OA\Property(property: 'concepto', type: 'integer', example: 1),
                        new OA\Property(property: 'descripcion', type: 'string', example: 'Abono semestral'),
                        new OA\Property(property: 'fecha_recibo', type: 'string', format: 'date', example: '2024-01-15'),
                        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 500000),
                        new OA\Property(property: 'elaborado_por', type: 'integer', example: 1),
                        new OA\Property(property: 'debe', type: 'integer', example: 1),
                        new OA\Property(property: 'haber', type: 'integer', example: 1),
                        new OA\Property(property: 'forma', type: 'string', enum: ['Efectivo', 'Bancos', 'Consignación'], nullable: true, example: 'Efectivo'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Abono creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(EntryStoreRequest $request): JsonResponse
    {
        $entry = $this->entryService->create($request->validated());

        return ApiResponse::success(new EntryResource($entry), 'Abono creado.', null, 201);
    }

    /**
     * GET /entries/{id}
     */
    #[
        OA\Get(
            path: '/api/v1/entries/{id}',
            summary: 'Obtener abono',
            description: 'Obtiene los datos de un abono específico.',
            tags: ['Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del abono', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del abono', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Abono no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $entry = Entry::findOrFail($id);

        return ApiResponse::success(new EntryResource($entry));
    }

    /**
     * DELETE /entries/{id}
     */
    #[
        OA\Delete(
            path: '/api/v1/entries/{id}',
            summary: 'Eliminar abono',
            description: 'Elimina un abono existente.',
            tags: ['Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del abono', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Abono eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Abono no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->hasPermission('records.delete')) {
            return ApiResponse::error('No tienes permiso para eliminar registros financieros.', 403);
        }

        $this->entryService->delete($id);

        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }

    /**
     * GET /entries/abonos/{cod_alumno}/pdf
     * Genera PDF consolidado de abonos para un estudiante
     */
    public function streamAbonosPdf(string $cod_alumno)
    {
        try {
            // 1. Obtener todos los costos del estudiante
            $costs = Cost::where('cod_alumno', $cod_alumno)->get();
            
            // Si no hay costos, intentamos buscar si hay abonos huérfanos, 
            // pero para el saldo necesitamos costos. Si no hay, asumimos 0.
            $totalNeto = 0;
            $id_cost_ref = 0;
            
            if ($costs->count() > 0) {
                // Calcular total neto (Suma de todos los semestres)
                // Limpiamos puntos por si acaso se guardan como string formateado
                $totalNeto = $costs->sum(function($c) {
                    return (int) str_replace('.', '', $c->valor_neto);
                });
                $id_cost_ref = $costs->first()->id;
            }

            // 2. Crear objeto fake cost para la vista
            // La vista usa $cost[0]->valor_neto para el saldo check
            $fakeCost = new \stdClass();
            $fakeCost->valor_neto = $totalNeto;
            $passedCost = [$fakeCost];

            // 3. Obtener todos los abonos del estudiante
            // Hacemos JOIN con costs para filtrar por cod_alumno
            $entries = DB::connection('mysql')->select(
                'SELECT entries.id, entries.id_cost, conceptos.nombre AS concepto, entries.descripcion, entries.no_recibo, entries.fecha_recibo, entries.valor, elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe, CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, entries.created_at 
                 FROM entries 
                 INNER JOIN costs ON costs.id = entries.id_cost
                 INNER JOIN conceptos ON conceptos.id = entries.concepto 
                 INNER JOIN elaborados ON elaborados.id = entries.elaborado_por 
                 INNER JOIN debes ON debes.id = entries.debe 
                 INNER JOIN habers ON habers.id = entries.haber 
                 WHERE costs.cod_alumno = ? 
                 ORDER BY entries.no_recibo ASC',
                [$cod_alumno]
            );

            // 4. Obtener datos del estudiante
            $data = [];
            $matricula = \App\Models\Matricula::where('cod_alumno', $cod_alumno)->first();
            if ($matricula) {
                $data = [(object)[
                    'cedula' => $matricula->numero_documento ?? '', 
                    'nombre' => $matricula->nombre_completo ?? 'N/A', 
                    'nombre_programa' => $matricula->programa ?? ''
                ]];
            }

            // 5. Configuración
            $institucion = InstitutionSetting::getSettings();

            // 6. Generar HTML
            // Reutilizamos la vista existente PDFs.pdf_abonos
            $dompdf = new Dompdf();
            $html = view('PDFs.pdf_abonos', [
                'id_cost' => $id_cost_ref,
                'student' => $data,
                'cost' => $passedCost, // Array con 1 objeto que tiene la suma total
                'entries' => $entries,
                'institucion' => $institucion
            ])->render();
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $nombreEstudiante = 'Estudiante';
            if (!empty($data) && isset($data[0]) && isset($data[0]->nombre)) {
                $nombreEstudiante = Str::slug($data[0]->nombre);
            }
            $filename = 'informe-abonos-' . $nombreEstudiante . '.pdf';
            
            $output = $dompdf->output();
            
            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Error en EntryController::streamAbonosPdf', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            abort(500, 'Error al generar el documento PDF: ' . $e->getMessage());
        }
    }
}
