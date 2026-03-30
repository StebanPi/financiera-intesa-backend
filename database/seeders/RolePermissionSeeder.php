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
        // Desactivar chequeo de llaves foráneas para poder limpiar las tablas
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpiar tablas de permisos y pivote
        \DB::table('permission_role')->truncate();
        Permission::truncate();
        
        // Reactivar chequeo de llaves foráneas
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Crear permisos
        $permissions = [
            [
                'name' => 'Acceso Total',
                'slug' => 'access.all',
                'description' => 'Acceso inrestricto a todo el sistema',
            ],
            [
                'name' => 'Gestión Académica',
                'slug' => 'access.academic',
                'description' => 'Acceso al módulo de Gestión Académica',
            ],
            [
                'name' => 'Terceros',
                'slug' => 'access.third_parties',
                'description' => 'Acceso al módulo de Terceros',
            ],
            [
                'name' => 'Egresos',
                'slug' => 'access.expenses',
                'description' => 'Acceso al módulo de Egresos',
            ],
            [
                'name' => 'Contabilidad',
                'slug' => 'access.accounting',
                'description' => 'Acceso al módulo de Contabilidad',
            ],
            [
                'name' => 'Ajustes',
                'slug' => 'access.settings',
                'description' => 'Acceso al módulo de Ajustes',
            ],
            [
                'name' => 'Administración',
                'slug' => 'access.administration',
                'description' => 'Acceso al módulo de Administración',
            ],
            [
                'name' => 'Eliminar Registros Financieros',
                'slug' => 'records.delete',
                'description' => 'Permiso para eliminar abonos, otros ingresos, egresos y costos',
            ],
            [
                'name' => 'Fecha Personalizada en Recibos',
                'slug' => 'receipts.custom_date',
                'description' => 'Permite crear recibos (abonos, otros ingresos, egresos y terceros) con una fecha personalizada distinta a la actual.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Crear roles (si no existen)
        $roles = [
            [
                'name' => 'Super Administrador',
                'slug' => 'super-admin',
                'description' => 'Tiene todos los permisos del sistema',
            ],
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Administrador del sistema',
            ],
            [
                'name' => 'Secretari@',
                'slug' => 'secretaria',
                'description' => 'Perfil de secretaría académica',
            ],
            [
                'name' => 'Contador@',
                'slug' => 'contador',
                'description' => 'Perfil contable',
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
                    $role->permissions()->sync(Permission::pluck('id'));
                    break;

                case 'admin':
                    // Admin también suele tener acceso total, o podemos dar todos excepto algunos. 
                    // Asumiremos acceso total por ahora para simplificar.
                    $role->permissions()->sync(Permission::pluck('id'));
                    break;

                case 'secretaria':
                    // Secretaria: Académica, Terceros, (Quizás Ajustes básico? No, solo lo pedido)
                    $role->permissions()->sync(
                        Permission::whereIn('slug', [
                            'access.academic', 
                            'access.third_parties'
                        ])->pluck('id')
                    );
                    break;

                case 'contador':
                    // Contador: Contabilidad, Egresos, Terceros
                    $role->permissions()->sync(
                        Permission::whereIn('slug', [
                            'access.accounting', 
                            'access.expenses', 
                            'access.third_parties'
                        ])->pluck('id')
                    );
                    break;
            }
        }
    }
}
