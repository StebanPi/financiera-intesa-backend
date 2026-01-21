<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CashBase;
use App\Models\Cost;
use App\Models\EgresoProvider;
use App\Models\EgresoReceipt;
use App\Models\Entry;
use App\Models\historyPurse;
use App\Models\InitialBalance;
use App\Models\Matricula;
use App\Models\OtherEntry;
use App\Models\Purse;
use App\Models\thirdActivity;
use App\Models\thirdEntry;
use App\Models\ThirdReceipts;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class MaintenanceApiController extends Controller
{
    /**
     * GET /api/v1/maintenance — Estadísticas del panel de mantenimiento (solo lectura).
     * Mismas métricas que MaintenanceController@index. No expone acciones destructivas.
     */
    #[
        OA\Get(
            path: '/api/v1/maintenance',
            summary: 'Estadísticas de mantenimiento',
            description: 'Obtiene estadísticas del panel de mantenimiento (solo lectura). Mismas métricas que MaintenanceController@index. No expone acciones destructivas.',
            tags: ['Maintenance'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Estadísticas de mantenimiento',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'stats', type: 'object', properties: [
                                    new OA\Property(property: 'entries', type: 'integer', example: 100),
                                    new OA\Property(property: 'other_entries', type: 'integer', example: 50),
                                    new OA\Property(property: 'costs', type: 'integer', example: 200),
                                    new OA\Property(property: 'matriculas', type: 'integer', example: 150),
                                    new OA\Property(property: 'purses', type: 'integer', example: 300),
                                    new OA\Property(property: 'history_purses', type: 'integer', example: 500),
                                    new OA\Property(property: 'third_receipts', type: 'integer', example: 75),
                                    new OA\Property(property: 'egreso_receipts', type: 'integer', example: 80),
                                    new OA\Property(property: 'egreso_providers', type: 'integer', example: 25),
                                    new OA\Property(property: 'third_entries', type: 'integer', example: 40),
                                    new OA\Property(property: 'third_activities', type: 'integer', example: 10),
                                    new OA\Property(property: 'cash_bases', type: 'integer', example: 365),
                                    new OA\Property(property: 'initial_balances', type: 'integer', example: 1),
                                ]),
                            ]),
                            new OA\Property(property: 'message', type: 'string', example: 'OK'),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(): JsonResponse
    {
        $stats = [
            'entries' => Entry::count(),
            'other_entries' => OtherEntry::count(),
            'costs' => Cost::count(),
            'matriculas' => Matricula::count(),
            'purses' => Purse::count(),
            'history_purses' => historyPurse::count(),
            'third_receipts' => ThirdReceipts::count(),
            'egreso_receipts' => EgresoReceipt::count(),
            'egreso_providers' => EgresoProvider::count(),
            'third_entries' => thirdEntry::count(),
            'third_activities' => thirdActivity::count(),
            'cash_bases' => CashBase::count(),
            'initial_balances' => InitialBalance::count(),
        ];

        return ApiResponse::success(['stats' => $stats], 'OK');
    }
}
