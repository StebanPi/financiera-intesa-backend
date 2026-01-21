<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_users_index_returns_401_without_auth(): void
    {
        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_users_index_returns_403_without_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('secretaria'); // tiene access.core pero no users.manage
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_users_index_returns_200_with_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $token = $user->createToken('test')->plainTextToken;

        User::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total', 'last_page']]);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_users_show_returns_200(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $token = $admin->createToken('test')->plainTextToken;

        $target = User::factory()->create(['name' => 'Target User']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/admin/users/' . $target->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $target->id)
            ->assertJsonPath('data.name', 'Target User');
    }
}
