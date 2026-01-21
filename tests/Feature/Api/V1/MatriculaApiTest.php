<?php

namespace Tests\Feature\Api\V1;

use App\Models\Matricula;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatriculaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\AcademicCatalogSeeder::class);
    }

    protected function userWithAccessCore(): User
    {
        $user = User::factory()->create();
        $user->assignRole('secretaria');

        return $user;
    }

    public function test_matriculas_index_returns_401_without_auth(): void
    {
        $response = $this->getJson('/api/v1/matriculas');

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_matriculas_index_returns_200_with_auth(): void
    {
        $user = $this->userWithAccessCore();
        $token = $user->createToken('test')->plainTextToken;

        Matricula::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/matriculas');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total', 'last_page']]);
    }

    public function test_matriculas_show_returns_200(): void
    {
        $user = $this->userWithAccessCore();
        $token = $user->createToken('test')->plainTextToken;

        $mat = Matricula::factory()->create(['cod_alumno' => '12345', 'nombre_completo' => 'Test Student']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/matriculas/12345');

        $response->assertStatus(200)
            ->assertJsonPath('data.cod_alumno', '12345')
            ->assertJsonPath('data.nombre_completo', 'Test Student');
    }

    public function test_matriculas_show_returns_404_when_not_found(): void
    {
        $user = $this->userWithAccessCore();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/matriculas/NOEXISTE');

        $response->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_matriculas_store_valid_returns_201(): void
    {
        $user = $this->userWithAccessCore();
        $token = $user->createToken('test')->plainTextToken;

        $payload = [
            'nombre_completo' => 'Nuevo Estudiante',
            'numero_documento' => '987654321',
            'tipo_documento' => 'CC',
            'programa' => 'Auxiliar de Primera Infancia',
            'sede' => 'Barrancabermeja',
            'estado_estudiante' => 'Activo',
            'horario' => 'Diurno',
            'semestre_actual' => 'I',
            'anio' => '2024',
            'numero_grupo' => '1A',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/matriculas', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.cod_alumno', '987654321')
            ->assertJsonPath('data.nombre_completo', 'Nuevo Estudiante');
    }

    public function test_matriculas_store_validates_required(): void
    {
        $user = $this->userWithAccessCore();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/matriculas', ['nombre_completo' => 'Solo nombre']);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['details']]);
    }
}
