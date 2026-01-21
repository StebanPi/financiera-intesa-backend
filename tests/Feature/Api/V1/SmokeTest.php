<?php

namespace Tests\Feature\Api\V1;

use App\Models\Group;
use App\Models\Module;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests para API v1: 401 sin token, 200 en /home con token, y health endpoint.
 *
 * phpunit.xml usa SQLite (:memory:). Las migraciones tienen MODIFY/CHANGE (MySQL),
 * por lo que hay que sobrescribir DB para usar MySQL. La BD de test debe existir.
 *
 * Comando (Linux/macOS):
 *
 *   DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test php artisan test tests/Feature/Api/V1/SmokeTest.php
 *
 * Windows (PowerShell):
 *
 *   $env:DB_CONNECTION="mysql"; $env:DB_DATABASE="financiera_intesa_test"; php artisan test tests/Feature/Api/V1/SmokeTest.php
 *
 * Si usas otra BD de test, sustituir DB_DATABASE. Opcional: DB_USERNAME, DB_PASSWORD, DB_HOST.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_auth_me_without_token_returns_401_unauthorized(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_matriculas_without_token_returns_401_unauthorized(): void
    {
        $response = $this->getJson('/api/v1/matriculas');

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_accounting_without_token_returns_401_unauthorized(): void
    {
        $response = $this->getJson('/api/v1/accounting');

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_maintenance_without_token_returns_401_unauthorized(): void
    {
        $response = $this->getJson('/api/v1/maintenance');

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_home_with_valid_token_returns_200_and_message(): void
    {
        $user = User::factory()->create();
        $user->assignRole('secretaria');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/home');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['message']]);
    }

    public function test_health_endpoint_returns_200_with_ok_status(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'status',
                    'server_time',
                    'app_env',
                ],
                'message',
            ])
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('message', 'OK');
    }

    public function test_api_endpoints_never_return_302_redirects_with_accept_wildcard(): void
    {
        // Verificar que /api/* nunca devuelve 302, incluso con Accept: */*
        // (por ejemplo descargas PDF/XLSX con Accept: */* deben devolver 401 JSON, no 302)

        // Test 1: attendance-sheet/generate con Accept: */* y sin token => 401 JSON, NO 302
        $response = $this->withHeader('Accept', '*/*')
            ->post('/api/v1/attendance-sheet/generate', [
                'program_id' => 1,
                'schedule_id' => 1,
                'group_id' => 1,
                'teacher_id' => 1,
                'module_id' => 1,
                'fecha_inicio' => '2024-01-01',
                'fecha_final' => '2024-01-31',
                'fecha_clase' => '2024-01-15',
            ]);

        // NO debe ser 302
        $this->assertNotEquals(302, $response->getStatusCode(), 'API endpoints should never return 302 redirects');
        
        // Debe ser 401 JSON
        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_api_endpoints_with_token_and_accept_wildcard_return_200_not_302(): void
    {
        // Crear datos mínimos necesarios para attendance-sheet
        $program = Program::firstOrCreate(
            ['name' => 'Programa de Prueba'],
            ['active' => true]
        );
        
        $schedule = Schedule::firstOrCreate(
            ['name' => 'Horario de Prueba'],
            ['active' => true]
        );
        
        $group = Group::firstOrCreate(
            ['name' => 'Grupo de Prueba'],
            ['active' => true]
        );
        
        $teacher = Teacher::firstOrCreate(
            ['name' => 'Docente de Prueba'],
            ['active' => true]
        );
        
        $module = Module::firstOrCreate(
            ['name' => 'Módulo de Prueba'],
            ['active' => true]
        );

        $user = User::factory()->create();
        $user->assignRole('secretaria');
        $token = $user->createToken('test')->plainTextToken;

        // Test: attendance-sheet/generate con Accept: */* y token válido => 200 PDF, NO 302
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept', '*/*')
            ->post('/api/v1/attendance-sheet/generate', [
                'program_id' => $program->id,
                'schedule_id' => $schedule->id,
                'group_id' => $group->id,
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'fecha_inicio' => '2024-01-01',
                'fecha_final' => '2024-01-31',
                'fecha_clase' => '2024-01-15',
            ]);

        // NO debe ser 302
        $this->assertNotEquals(302, $response->getStatusCode(), 'API endpoints should never return 302 redirects');
        
        // Debe ser 200 PDF
        $response->assertStatus(200);
        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/pdf', $contentType);
    }

    public function test_api_endpoints_with_invalid_token_and_accept_wildcard_return_401_not_302(): void
    {
        // Verificar que con token inválido y Accept: */*, devuelve 401 JSON, NO 302
        $response = $this->withHeader('Authorization', 'Bearer invalid-token-12345')
            ->withHeader('Accept', '*/*')
            ->post('/api/v1/attendance-sheet/generate', [
                'program_id' => 1,
                'schedule_id' => 1,
                'group_id' => 1,
                'teacher_id' => 1,
                'module_id' => 1,
                'fecha_inicio' => '2024-01-01',
                'fecha_final' => '2024-01-31',
                'fecha_clase' => '2024-01-15',
            ]);

        // NO debe ser 302
        $this->assertNotEquals(302, $response->getStatusCode(), 'API endpoints should never return 302 redirects');
        
        // Debe ser 401 JSON
        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }
}
