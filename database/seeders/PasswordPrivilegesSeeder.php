<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use App\Models\PasswordPrivileges;
use Illuminate\Database\Seeder;

class PasswordPrivilegesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PasswordPrivileges::create(['password' => '*admin*']);
        PasswordPrivileges::create(['password' => '*admin*']);
    }
}
