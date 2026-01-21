<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero crear roles y permisos
        $this->call(RolePermissionSeeder::class);
        
        // Deshabilitar verificaciones de claves foráneas temporalmente
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Eliminar todas las relaciones de roles
        \Illuminate\Support\Facades\DB::table('role_user')->truncate();
        
        // Eliminar todos los usuarios
        User::truncate();
        
        // Resetear el auto-increment para que el siguiente usuario tenga ID 1
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
        
        // Rehabilitar verificaciones de claves foráneas
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Crear el usuario superadmin (tendrá ID 1)
        $user = User::create([
            'email' => 'pinedasteban13@gmail.com',
            'name' => 'Steban Fabian Pineda Aguilera',
            'password' => bcrypt('lego2200')
        ]);
        
        // Asignar siempre super-admin
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        if ($superAdminRole) {
            $user->assignRole('super-admin');
        }
        
        // Seeders de configuración del sistema
        $this->call(ConsecutiveSeeder::class);
        $this->call(DebeSeeder::class);
        $this->call(ElaboradoSeeder::class);
        $this->call(HaberSeeder::class);
        $this->call(ConceptoSeeder::class);
        $this->call(PasswordPrivilegesSeeder::class);
        $this->call(OtherConceptosSeeder::class);
        $this->call(ConceptDischargeReceiptSeeder::class);
        $this->call(ConceptEntryReceiptSeeder::class);
        // No se crean terceros ni actividades por defecto
        // $this->call(ThirdActivitySeeder::class);
        // $this->call(ThirdEntrySeeder::class);
        $this->call(AcademicCatalogSeeder::class);
        $this->call(EgresoConceptSeeder::class);
    }
}
