<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Financiera Intesa API',
    description: 'API REST para el sistema de gestión financiera académica.

## Autenticación

La API usa autenticación Bearer Token mediante Laravel Sanctum.

Para obtener un token:
1. Realizar un POST a `/api/v1/auth/login` con `email` y `password`
2. El token se devuelve en la respuesta: `data.token`
3. Usar el token en el header: `Authorization: Bearer {token}`

## Endpoints de Archivos

Algunos endpoints devuelven archivos (PDF/XLSX):
- Los endpoints de PDF devuelven `Content-Type: application/pdf`
- Los endpoints de XLSX devuelven `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- Estos endpoints pueden requerir `Accept: */*` o `Accept: application/json` para evitar redirecciones',
    contact: new OA\Contact(
        name: 'Financiera Intesa',
        email: 'support@ejemplo.com'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000/api/v1',
    description: 'Local development server'
)]
#[OA\Server(
    url: 'https://api-staging.ejemplo.com/api/v1',
    description: 'Staging server (placeholder - actualizar con URL real)'
)]
#[OA\Server(
    url: 'https://api.ejemplo.com/api/v1',
    description: 'Production server (placeholder - actualizar con URL real)'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Laravel Sanctum Bearer Token. Format: Bearer {token}'
)]
#[OA\Tag(name: 'Health', description: 'Health check endpoints')]
#[OA\Tag(name: 'Auth', description: 'Authentication endpoints (login, register, password reset, etc.)')]
#[OA\Tag(name: 'Home', description: 'Home endpoint (protected)')]
#[OA\Tag(name: 'Maintenance', description: 'Maintenance endpoints (super-admin only)')]
#[OA\Tag(name: 'Matriculas', description: 'Student registration management')]
#[OA\Tag(name: 'Accounting', description: 'Accounting reports and downloads')]
#[OA\Tag(name: 'Attendance Sheet', description: 'Attendance sheet generation (PDF)')]
#[OA\Tag(name: 'Costs', description: 'Cost management')]
#[OA\Tag(name: 'Purses', description: 'Purse management')]
#[OA\Tag(name: 'Entries', description: 'Entry management')]
#[OA\Tag(name: 'Other Entries', description: 'Other entries management')]
#[OA\Tag(name: 'Discharges', description: 'Discharge management')]
#[OA\Tag(name: 'Providers', description: 'Provider management')]
#[OA\Tag(name: 'Discharge Concepts', description: 'Discharge concept management')]
#[OA\Tag(name: 'Third Entries', description: 'Third party entries management')]
#[OA\Tag(name: 'Third Activities', description: 'Third party activities management')]
#[OA\Tag(name: 'Third Receipts', description: 'Third party receipts management')]
#[OA\Tag(name: 'Concept Entry Receipts', description: 'Concept entry receipts management')]
#[OA\Tag(name: 'Concept Discharge Receipts', description: 'Concept discharge receipts management')]
#[OA\Tag(name: 'Financial Receipts', description: 'Financial receipts management')]
#[OA\Tag(name: 'Consecutives', description: 'Consecutive number management')]
#[OA\Tag(name: 'Catalog', description: 'Catalog endpoints')]
#[OA\Tag(name: 'Settings', description: 'Settings management')]
#[OA\Tag(name: 'Admin', description: 'Administration endpoints (users, roles, permissions)')]
class Info
{
}
