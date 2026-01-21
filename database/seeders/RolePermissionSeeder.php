<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            [
                'name' => 'Acceso al Sistema',
                'slug' => 'access.core',
                'description' => 'Acceso a módulos generales del sistema (excepto contabilidad)',
            ],
            [
                'name' => 'Acceso a Contabilidad',
                'slug' => 'access.accounting',
                'description' => 'Acceso al módulo de contabilidad',
            ],
            [
                'name' => 'Gestionar Usuarios',
                'slug' => 'users.manage',
                'description' => 'Crear y editar usuarios',
            ],
            [
                'name' => 'Gestionar Roles',
                'slug' => 'roles.manage',
                'description' => 'Crear y editar roles y asignar permisos',
            ],
            [
                'name' => 'Gestionar Ajustes y Catálogos',
                'slug' => 'settings.manage',
                'description' => 'Gestionar catálogos de ajustes (programas, horarios, grupos, conceptos, etc.)',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Crear roles
        $roles = [
            [
                'name' => 'Super Administrador',
                'slug' => 'super-admin',
                'description' => 'Tiene todos los permisos del sistema',
            ],
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Tiene acceso a todos los módulos, pero no gestiona roles/permisos por defecto',
            ],
            [
                'name' => 'Secretari@',
                'slug' => 'secretaria',
                'description' => 'Acceso a todo excepto Contabilidad',
            ],
            [
                'name' => 'Contador@',
                'slug' => 'contador',
                'description' => 'Solo acceso al módulo de Contabilidad',
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Asignar permisos según el rol
            switch ($role->slug) {
                case 'super-admin':
                    // Super Admin tiene TODOS los permisos
                    $role->permissions()->sync(Permission::pluck('id'));
                    break;

                case 'admin':
                    // Admin tiene access.core y access.accounting (NO roles.manage/users.manage por defecto)
                    $role->permissions()->sync(
                        Permission::whereIn('slug', ['access.core', 'access.accounting'])->pluck('id')
                    );
                    break;

                case 'secretaria':
                    // Secretaria tiene solo access.core
                    $role->permissions()->sync(
                        Permission::where('slug', 'access.core')->pluck('id')
                    );
                    break;

                case 'contador':
                    // Contador tiene solo access.accounting
                    $role->permissions()->sync(
                        Permission::where('slug', 'access.accounting')->pluck('id')
                    );
                    break;
            }
        }
    }
}
