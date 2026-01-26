<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FinancialReceiptService;
use App\Support\ApiResponse;
use Dompdf\Dompdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class FinancialReceiptController extends Controller
{
    private const TYPES = ['entry', 'other-entry', 'egreso', 'third'];

    public function __construct(private FinancialReceiptService $financialReceiptService) {}

    /**
     * GET /financial-receipts/{type}/{id} — JSON con datos del recibo.
     * type: entry|other-entry|egreso|third
     */
    #[
        OA\Get(
            path: '/api/v1/financial-receipts/{type}/{id}',
            summary: 'Obtener datos de recibo financiero',
            description: 'Obtiene los datos JSON de un recibo financiero por tipo e ID.',
            tags: ['Financial Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'type', in: 'path', required: true, description: 'Tipo de recibo', schema: new OA\Schema(type: 'string', enum: ['entry', 'other-entry', 'egreso', 'third'], example: 'entry')),
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del recibo', schema: new OA\Schema(type: 'integer', example: 1)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del recibo', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recibo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Tipo inválido', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function show(string $type, int $id): JsonResponse
    {
        $this->validateType($type);
        $data = $this->financialReceiptService->getReceiptData($type, $id);
        if (!$data) {
            abort(404, 'Recibo no encontrado');
        }
        return ApiResponse::success($data);
    }

    /**
     * GET /financial-receipts/{type}/{id}/pdf — stream PDF (Content-Type: application/pdf, Content-Disposition: inline).
     */
    #[
        OA\Get(
            path: '/api/v1/financial-receipts/{type}/{id}/pdf',
            summary: 'Descargar recibo financiero (PDF)',
            description: 'Genera y devuelve un PDF del recibo financiero. El PDF se devuelve como binario para visualización inline.',
            tags: ['Financial Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'type', in: 'path', required: true, description: 'Tipo de recibo', schema: new OA\Schema(type: 'string', enum: ['entry', 'other-entry', 'egreso', 'third'], example: 'entry')),
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del recibo', schema: new OA\Schema(type: 'integer', example: 1)),
                new OA\Parameter(name: 'paper', in: 'query', required: false, description: 'Ancho del papel (76 o 80 mm)', schema: new OA\Schema(type: 'string', enum: ['76', '80'], example: '80')),
                new OA\Parameter(name: 'offset', in: 'query', required: false, description: 'Offset izquierdo (mm)', schema: new OA\Schema(type: 'string', example: '8')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'PDF generado exitosamente',
                    content: new OA\MediaType(
                        mediaType: 'application/pdf',
                        schema: new OA\Schema(type: 'string', format: 'binary')
                    ),
                    headers: [
                        new OA\Header(header: 'Content-Disposition', schema: new OA\Schema(type: 'string'), description: 'inline; filename="financial-receipt-entry-1.pdf"'),
                    ]
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recibo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Tipo inválido', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function streamPdf(Request $request, string $type, int $id)
    {
        $this->validateType($type);
        $data = $this->financialReceiptService->getReceiptData($type, $id);
        if (!$data) {
            abort(404, 'Recibo no encontrado');
        }

        $paper = in_array($request->query('paper', '80'), ['76', '80']) ? $request->query('paper') : '80';
        $offsetLeft = $request->query('offset', '8') . 'mm';
        $paperWidth = $paper . 'mm';
        $fechaFormateada = $this->financialReceiptService->formatDate($data['fecha'] ?? null);

        // Determinar la vista específica según el tipo para usar exactamente el mismo HTML que la versión web
        $viewName = match($type) {
            'entry' => 'prints.entry-pos',
            'other-entry' => 'prints.other-entry-pos',
            'egreso' => 'prints.financial-receipt-pos',
            'third' => 'prints.financial-receipt-pos',
        };

        // Para entry y other-entry, simplificar viewData para coincidir con la versión web
        if (in_array($type, ['entry', 'other-entry'])) {
            $viewData = [
                'consecutivo' => $data['consecutivo'] ?? null,
                'estudiante_cedula' => $data['estudiante_cedula'] ?? null,
                'estudiante_nombre' => $data['estudiante_nombre'] ?? null,
                'programa' => $data['programa'] ?? null,
                'concepto' => $data['concepto'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'valor' => $data['valor'] ?? null,
                'fecha' => $fechaFormateada,
            ];
        } else {
            // Para egreso y third, usar vista unificada con parámetros completos
            $viewData = [
                'paper' => $paper,
                'paperWidth' => $paperWidth,
                'offsetLeft' => $offsetLeft,
                'consecutivo' => $data['consecutivo'] ?? null,
                'fecha' => $fechaFormateada,
                'valor' => $data['valor'] ?? null,
                'concepto' => $data['concepto'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'tipo_recibo' => $type,
            ];
            if ($type === 'egreso') {
                $viewData['proveedor_nombre'] = $data['proveedor_nombre'] ?? null;
                $viewData['forma'] = $data['forma'] ?? null;
            }
            if ($type === 'third') {
                $viewData['tercero_nombre'] = $data['tercero_nombre'] ?? null;
                $viewData['tercero_documento'] = $data['tercero_documento'] ?? null;
                $viewData['forma'] = $data['forma'] ?? null;
            }
        }

        $html = view($viewName, $viewData)->render();
        
        $dompdf = new Dompdf();
        
        // Configurar opciones para cargar imágenes correctamente
        $options = $dompdf->getOptions();
        $options->setChroot(public_path());
        $options->setIsRemoteEnabled(true);
        $options->setIsHtml5ParserEnabled(true);
        $dompdf->setOptions($options);

        // Calcular ancho en puntos (1mm = 72pt/25.4mm ≈ 2.83465pt)
        $widthPoints = round($paper * 2.83465, 2);
        
        $dompdf->loadHtml($html);
        // [0, 0, ancho, alto] en puntos. 1000pt de alto es suficiente para la mayoría de tickets.
        $dompdf->setPaper([0, 0, $widthPoints, 1000], 'portrait'); 
        $dompdf->render();

        $filename = 'financial-receipt-' . $type . '-' . $id . '.pdf';
        $pdfBinary = $dompdf->output();

        return response($pdfBinary, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    private function validateType(string $type): void
    {
        if (!in_array($type, self::TYPES, true)) {
            throw ValidationException::withMessages(['type' => ['Tipo debe ser: entry, other-entry, egreso, third.']]);
        }
    }
}
