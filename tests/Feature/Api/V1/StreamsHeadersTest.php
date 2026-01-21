<?php

namespace Tests\Feature\Api\V1;

use App\Models\Group;
use App\Models\Matricula;
use App\Models\Module;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests para validar headers de streams (PDF/XLSX) en endpoints de la API v1.
 *
 * phpunit.xml usa SQLite (:memory:). Las migraciones tienen MODIFY/CHANGE (MySQL),
 * por lo que hay que sobrescribir DB para usar MySQL. La BD de test debe existir.
 *
 * Comando (Linux/macOS):
 *
 *   DB_CONNECTION=mysql DB_DATABASE=financiera_intesa_test php artisan test tests/Feature/Api/V1/StreamsHeadersTest.php
 *
 * Windows (PowerShell):
 *
 *   $env:DB_CONNECTION="mysql"; $env:DB_DATABASE="financiera_intesa_test"; php artisan test tests/Feature/Api/V1/StreamsHeadersTest.php
 *
 * Si usas otra BD de test, sustituir DB_DATABASE. Opcional: DB_USERNAME, DB_PASSWORD, DB_HOST.
 */
class StreamsHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_xlsx_abonos_download_has_headers(): void
    {
        $user = User::factory()->create();
        $user->assignRole('secretaria');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/v1/accounting/abonos/download?fecha_inicio=2024-01-01&fecha_fin=2024-01-31');

        $response->assertStatus(200);
        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $contentType);
        
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertNotNull($contentDisposition);
        $this->assertStringContainsString('attachment', $contentDisposition);
    }

    public function test_pdf_attendance_sheet_has_headers(): void
    {
        // Crear datos mínimos necesarios
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

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/attendance-sheet/generate', [
                'program_id' => $program->id,
                'schedule_id' => $schedule->id,
                'group_id' => $group->id,
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'fecha_inicio' => '2024-01-01',
                'fecha_final' => '2024-01-31',
                'fecha_clase' => '2024-01-15',
            ]);

        $response->assertStatus(200);
        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/pdf', $contentType);
        
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertNotNull($contentDisposition);
        $this->assertStringContainsString('inline', $contentDisposition);
    }

    public function test_pdf_matricula_has_all_headers(): void
    {
        // Crear matrícula de prueba
        $matricula = Matricula::factory()->create([
            'cod_alumno' => '123456789',
            'nombre_completo' => 'Test Estudiante',
        ]);

        $user = User::factory()->create();
        $user->assignRole('secretaria');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/v1/matriculas/' . $matricula->cod_alumno . '/pdf');

        $response->assertStatus(200);

        // Verificar headers de PDF
        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/pdf', $contentType);

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertNotNull($contentDisposition);
        $this->assertStringContainsString('inline', $contentDisposition);

        // Verificar headers de middleware
        $this->assertNotNull($response->headers->get('X-Request-Id'), 'X-Request-Id header should be present');
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'), 'X-Content-Type-Options should be nosniff');
    }
}
