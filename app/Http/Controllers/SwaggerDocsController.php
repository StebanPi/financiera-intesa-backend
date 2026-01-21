<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class SwaggerDocsController extends Controller
{
    /**
     * GET /docs — Renderiza la UI de Swagger
     */
    public function index(): Response
    {
        return response()->view('swagger.index', [
            'title' => config('app.name') . ' API Documentation',
        ]);
    }

    /**
     * GET /docs/openapi.json — Devuelve el JSON de OpenAPI generado por l5-swagger
     */
    public function spec(): JsonResponse
    {
        $jsonPath = storage_path('api-docs/openapi.json');

        if (!File::exists($jsonPath)) {
            abort(404, 'OpenAPI specification not found. Run: php artisan l5-swagger:generate');
        }

        $jsonContent = File::get($jsonPath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            abort(500, 'Invalid JSON in OpenAPI specification');
        }

        return response()->json($data);
    }
}
