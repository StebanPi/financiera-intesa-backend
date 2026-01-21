<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Tests para verificar que en producción (APP_DEBUG=false) no se devuelva información sensible.
 *
 * phpunit.xml usa SQLite (:memory:). Las migraciones tienen MODIFY/CHANGE (MySQL),
 * por lo que hay que sobrescribir DB para usar MySQL. La BD de test debe existir.
 *
 * Comando (Linux/macOS):
 *
 *   DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test APP_DEBUG=false php artisan test tests/Feature/Api/V1/ErrorHardeningTest.php
 *
 * Windows (PowerShell):
 *
 *   $env:DB_CONNECTION="mysql"; $env:DB_DATABASE="financiera_intesa_test"; $env:APP_DEBUG="false"; php artisan test tests/Feature/Api/V1/ErrorHardeningTest.php
 */
class ErrorHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Ruta temporal para forzar un 500
        if (!Route::has('test.error.500')) {
            Route::get('/api/v1/test-error-500', function () {
                throw new \RuntimeException('Test error for hardening verification');
            })->name('test.error.500')->middleware('auth:sanctum');
        }
    }

    public function test_server_error_in_production_does_not_expose_sensitive_info(): void
    {
        // Forzar APP_DEBUG=false
        config(['app.debug' => false]);

        $user = User::factory()->create();
        $user->assignRole('secretaria');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/test-error-500');

        $response->assertStatus(500);

        $json = $response->json();

        // Verificar estructura de error
        $this->assertArrayHasKey('error', $json);
        $this->assertArrayHasKey('code', $json['error']);
        $this->assertEquals('SERVER_ERROR', $json['error']['code']);

        // Verificar que NO viene información sensible
        $this->assertArrayNotHasKey('trace', $json['error']);
        $this->assertArrayNotHasKey('exception', $json['error']);
        $this->assertArrayNotHasKey('file', $json['error']);
        $this->assertArrayNotHasKey('line', $json['error']);

        // Verificar que viene trace_id
        $this->assertArrayHasKey('trace_id', $json['error']);

        // Verificar que el mensaje es genérico (no incluye detalles de la excepción)
        $this->assertStringNotContainsString('Test error', $json['error']['message']);
        $this->assertEquals('Error interno del servidor.', $json['error']['message']);

        // Verificar que no hay "details" con información sensible
        if (isset($json['error']['details'])) {
            $details = $json['error']['details'];
            $this->assertNotIsArray($details);
        }
    }

    public function test_server_error_in_debug_mode_exposes_info(): void
    {
        // Forzar APP_DEBUG=true para verificar que en debug sí se expone
        config(['app.debug' => true]);

        $user = User::factory()->create();
        $user->assignRole('secretaria');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/test-error-500');

        $response->assertStatus(500);

        $json = $response->json();

        // En debug, el mensaje debería incluir el mensaje de la excepción
        $this->assertArrayHasKey('error', $json);
        $this->assertStringContainsString('Test error', $json['error']['message']);

        // En debug, puede venir información adicional (pero no en el array "error" directamente)
        // El Handler devuelve 'details' con exception/file/line solo en debug
        if (isset($json['error']['details'])) {
            $details = $json['error']['details'];
            if (is_array($details)) {
                $this->assertArrayHasKey('exception', $details);
                $this->assertArrayHasKey('file', $details);
                $this->assertArrayHasKey('line', $details);
            }
        }
    }
}
