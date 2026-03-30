<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $slug = 'receipts.custom_date';

        if (DB::table('permissions')->where('slug', $slug)->exists()) {
            return;
        }

        $permissionId = DB::table('permissions')->insertGetId([
            'name'        => 'Fecha Personalizada en Recibos',
            'slug'        => $slug,
            'description' => 'Permite crear recibos (abonos, otros ingresos, egresos y terceros) con una fecha personalizada distinta a la actual.',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Asignar automáticamente a super-admin y admin
        $adminRoles = DB::table('roles')
            ->whereIn('slug', ['super-admin', 'admin'])
            ->pluck('id');

        foreach ($adminRoles as $roleId) {
            $alreadyAssigned = DB::table('permission_role')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$alreadyAssigned) {
                DB::table('permission_role')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permission = DB::table('permissions')
            ->where('slug', 'receipts.custom_date')
            ->first();

        if ($permission) {
            DB::table('permission_role')
                ->where('permission_id', $permission->id)
                ->delete();

            DB::table('permissions')
                ->where('id', $permission->id)
                ->delete();
        }
    }
};
