<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Componentes reutilizables para el formato estándar de ApiResponse
 */
#[OA\Schema(
    schema: 'ApiError',
    type: 'object',
    description: 'Error estándar de la API con código, mensaje, detalles opcionales y trace_id para logs',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'VALIDATION_ERROR', description: 'Código del error'),
        new OA\Property(property: 'message', type: 'string', example: 'Los datos enviados no son válidos.', description: 'Mensaje del error'),
        new OA\Property(property: 'details', type: 'object', nullable: true, description: 'Detalles del error (objeto o null)'),
        new OA\Property(property: 'trace_id', type: 'string', nullable: true, description: 'Trace ID para correlacionar errores en logs', example: '550e8400-e29b-41d4-a716-446655440000'),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    required: ['error'],
    properties: [
        new OA\Property(property: 'error', ref: '#/components/schemas/ApiError'),
    ]
)]
#[OA\Schema(
    schema: 'SuccessResponse',
    type: 'object',
    required: ['data'],
    properties: [
        new OA\Property(property: 'data', description: 'Datos de la respuesta (puede ser cualquier tipo: objeto, array, null)'),
        new OA\Property(property: 'message', type: 'string', nullable: true, description: 'Mensaje opcional'),
        new OA\Property(property: 'meta', type: 'object', nullable: true, description: 'Metadatos opcionales (paginación, etc.)'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    type: 'object',
    required: ['error'],
    properties: [
        new OA\Property(
            property: 'error',
            type: 'object',
            required: ['code', 'message', 'details'],
            properties: [
                new OA\Property(property: 'code', type: 'string', example: 'VALIDATION_ERROR'),
                new OA\Property(property: 'message', type: 'string', example: 'Los datos enviados no son válidos.'),
                new OA\Property(
                    property: 'details',
                    type: 'object',
                    additionalProperties: new OA\AdditionalProperties(
                        type: 'array',
                        items: new OA\Items(type: 'string')
                    ),
                    example: [
                        'email' => ['El campo email es obligatorio.'],
                        'password' => ['El campo contraseña es obligatorio.'],
                    ]
                ),
            ]
        ),
    ]
)]
class Schemas
{
}
