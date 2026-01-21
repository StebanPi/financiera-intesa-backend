<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\concepto;
use App\Models\elaborado;
use App\Models\haber;
use App\Models\debe;
use App\Models\otrosConcepto;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\Module;
use App\Models\InstitutionSetting;
use App\Models\consecutive;
use App\Models\PasswordPrivileges;
use App\Models\ConceptDischargeReceipt;
use App\Models\ConceptEntryReceipt;
use App\Models\thirdActivity;
use App\Models\thirdEntry;
use App\Models\EgresoConcept;
use Illuminate\Support\Facades\File;

class ExportSettingsToSeeders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:export-to-seeders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta los datos actuales de ajustes del sistema a los archivos de seeders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Exportando datos actuales a seeders...');

        // Obtener datos únicos (sin duplicados) como lo hace el SettingController
        $conceptosIds = DB::table('conceptos')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('nombre')
            ->pluck('id');
        $conceptos = concepto::whereIn('id', $conceptosIds)->orderBy('id')->get();

        $elaboradosIds = DB::table('elaborados')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('nombre')
            ->pluck('id');
        $elaborados = elaborado::whereIn('id', $elaboradosIds)->orderBy('id')->get();

        $habersIds = DB::table('habers')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('cuenta', 'nombre')
            ->pluck('id');
        $habers = haber::whereIn('id', $habersIds)->orderBy('id')->get();

        $debesIds = DB::table('debes')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('cuenta', 'nombre')
            ->pluck('id');
        $debes = debe::whereIn('id', $debesIds)->orderBy('id')->get();

        $otrosIds = DB::table('otros_conceptos')
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('nombre')
            ->pluck('id');
        $otros = otrosConcepto::whereIn('id', $otrosIds)->orderBy('id')->get();

        $programs = Program::distinct()->orderBy('id')->get();
        $schedules = Schedule::distinct()->orderBy('id')->get();
        $groups = Group::distinct()->orderBy('id')->get();
        $teachers = Teacher::distinct()->orderBy('id')->get();
        $modules = Module::distinct()->orderBy('id')->get();
        $institucion = InstitutionSetting::getSettings();

        $consecutives = consecutive::all();
        $passwordPrivileges = PasswordPrivileges::all();
        $conceptDischargeReceipts = ConceptDischargeReceipt::all();
        $conceptEntryReceipts = ConceptEntryReceipt::all();
        $thirdActivities = thirdActivity::all();
        $thirdEntries = thirdEntry::all();
        $egresoConcepts = EgresoConcept::orderBy('nombre')->get();

        // Actualizar seeders
        $this->updateConceptoSeeder($conceptos);
        $this->updateElaboradoSeeder($elaborados);
        $this->updateHaberSeeder($habers);
        $this->updateDebeSeeder($debes);
        $this->updateOtherConceptosSeeder($otros);
        $this->updateAcademicCatalogSeeder($programs, $schedules, $groups, $teachers, $modules, $institucion);
        $this->updateConsecutiveSeeder($consecutives);
        $this->updatePasswordPrivilegesSeeder($passwordPrivileges);
        $this->updateConceptDischargeReceiptSeeder($conceptDischargeReceipts);
        $this->updateConceptEntryReceiptSeeder($conceptEntryReceipts);
        $this->updateThirdActivitySeeder($thirdActivities);
        $this->updateThirdEntrySeeder($thirdEntries);
        $this->updateEgresoConceptSeeder($egresoConcepts);

        $this->info('✅ Seeders actualizados exitosamente!');
    }

    private function updateConceptoSeeder($conceptos)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n\n";
        $content .= "use Illuminate\Database\Seeder;\n";
        $content .= "use Illuminate\Support\Facades\DB;\n";
        $content .= "use App\Models\concepto;\n\n";
        $content .= "class ConceptoSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($conceptos as $item) {
            $content .= "        concepto::create([\n";
            $content .= "            'nombre' => " . var_export($item->nombre, true) . ",\n";
            $content .= "            'estado' => " . var_export($item->estado, true) . ",\n";
            $content .= "            'orderTable' => " . var_export($item->orderTable ?? '0', true) . ",\n";
            $content .= "            'consecutivo' => " . var_export($item->consecutivo ?? '1', true) . ",\n";
            if (isset($item->debe)) {
                $content .= "            'debe' => " . var_export($item->debe, true) . ",\n";
            }
            if (isset($item->haber)) {
                $content .= "            'haber' => " . var_export($item->haber, true) . ",\n";
            }
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/ConceptoSeeder.php'), $content);
        $this->info('✓ ConceptoSeeder actualizado');
    }

    private function updateElaboradoSeeder($elaborados)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n\n";
        $content .= "use Illuminate\Database\Seeder;\n";
        $content .= "use Illuminate\Support\Facades\DB;\n";
        $content .= "use App\Models\elaborado;\n\n";
        $content .= "class ElaboradoSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($elaborados as $item) {
            $content .= "        elaborado::create([\n";
            $content .= "            'nombre' => " . var_export($item->nombre, true) . ",\n";
            $content .= "            'estado' => " . var_export($item->estado, true) . ",\n";
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/ElaboradoSeeder.php'), $content);
        $this->info('✓ ElaboradoSeeder actualizado');
    }

    private function updateHaberSeeder($habers)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n\n";
        $content .= "use Illuminate\Database\Seeder;\n";
        $content .= "use App\Models\haber;\n\n";
        $content .= "class HaberSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($habers as $item) {
            $content .= "        haber::create([\n";
            $content .= "            'cuenta' => " . var_export($item->cuenta, true) . ",\n";
            $content .= "            'nombre' => " . var_export($item->nombre, true) . ",\n";
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/HaberSeeder.php'), $content);
        $this->info('✓ HaberSeeder actualizado');
    }

    private function updateDebeSeeder($debes)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n\n";
        $content .= "use Illuminate\Database\Seeder;\n";
        $content .= "use Illuminate\Support\Facades\DB;\n";
        $content .= "use App\Models\debe;\n\n";
        $content .= "class DebeSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($debes as $item) {
            $content .= "        debe::create([\n";
            $content .= "            'cuenta' => " . var_export($item->cuenta, true) . ",\n";
            $content .= "            'nombre' => " . var_export($item->nombre, true) . ",\n";
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/DebeSeeder.php'), $content);
        $this->info('✓ DebeSeeder actualizado');
    }

    private function updateOtherConceptosSeeder($otros)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n\n";
        $content .= "use Illuminate\Database\Seeder;\n";
        $content .= "use App\Models\otrosConcepto;\n\n";
        $content .= "class OtherConceptosSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($otros as $item) {
            $content .= "        otrosConcepto::create([\n";
            $content .= "            'nombre' => " . var_export($item->nombre, true) . ",\n";
            $content .= "            'estado' => " . var_export($item->estado, true) . ",\n";
            if (isset($item->debe)) {
                $content .= "            'debe' => " . var_export($item->debe, true) . ",\n";
            }
            if (isset($item->haber)) {
                $content .= "            'haber' => " . var_export($item->haber, true) . ",\n";
            }
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/OtherConceptosSeeder.php'), $content);
        $this->info('✓ OtherConceptosSeeder actualizado');
    }

    private function updateAcademicCatalogSeeder($programs, $schedules, $groups, $teachers, $modules, $institucion)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n\n";
        $content .= "use Illuminate\Database\Console\Seeds\WithoutModelEvents;\n";
        $content .= "use Illuminate\Database\Seeder;\n";
        $content .= "use App\Models\Program;\n";
        $content .= "use App\Models\Schedule;\n";
        $content .= "use App\Models\Group;\n";
        $content .= "use App\Models\Teacher;\n";
        $content .= "use App\Models\Module;\n";
        $content .= "use App\Models\InstitutionSetting;\n\n";
        $content .= "class AcademicCatalogSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     */\n";
        $content .= "    public function run(): void\n";
        $content .= "    {\n";

        // Programas
        if ($programs->count() > 0) {
            $content .= "        // Programas\n";
            foreach ($programs as $item) {
                $content .= "        Program::firstOrCreate(\n";
                $content .= "            ['name' => " . var_export($item->name, true) . "],\n";
                $content .= "            ['active' => " . ($item->active ? 'true' : 'false') . "]\n";
                $content .= "        );\n";
            }
        }

        // Horarios
        if ($schedules->count() > 0) {
            $content .= "\n        // Horarios\n";
            foreach ($schedules as $item) {
                $content .= "        Schedule::firstOrCreate(\n";
                $content .= "            ['name' => " . var_export($item->name, true) . "],\n";
                $content .= "            ['active' => " . ($item->active ? 'true' : 'false') . "]\n";
                $content .= "        );\n";
            }
        }

        // Grupos
        if ($groups->count() > 0) {
            $content .= "\n        // Grupos\n";
            foreach ($groups as $item) {
                $content .= "        Group::firstOrCreate(\n";
                $content .= "            ['name' => " . var_export($item->name, true) . "],\n";
                $content .= "            ['active' => " . ($item->active ? 'true' : 'false') . "]\n";
                $content .= "        );\n";
            }
        }

        // Docentes
        if ($teachers->count() > 0) {
            $content .= "\n        // Docentes\n";
            foreach ($teachers as $item) {
                $content .= "        Teacher::firstOrCreate(\n";
                $content .= "            ['name' => " . var_export($item->name, true) . "],\n";
                $content .= "            ['active' => " . ($item->active ? 'true' : 'false') . "]\n";
                $content .= "        );\n";
            }
        }

        // Módulos
        if ($modules->count() > 0) {
            $content .= "\n        // Módulos\n";
            foreach ($modules as $item) {
                $content .= "        Module::firstOrCreate(\n";
                $content .= "            ['name' => " . var_export($item->name, true) . "],\n";
                $content .= "            [\n";
                $content .= "                'code' => " . var_export($item->code ?? null, true) . ",\n";
                $content .= "                'active' => " . ($item->active ? 'true' : 'false') . "\n";
                $content .= "            ]\n";
                $content .= "        );\n";
            }
        }

        // Configuración de Institución
        if ($institucion) {
            $content .= "\n        // Configuración de Institución\n";
            $content .= "        InstitutionSetting::firstOrCreate(\n";
            $content .= "            ['id' => 1],\n";
            $content .= "            [\n";
            $content .= "                'name' => " . var_export($institucion->name ?? '', true) . ",\n";
            $content .= "                'nit' => " . var_export($institucion->nit ?? '', true) . ",\n";
            $content .= "                'address' => " . var_export($institucion->address ?? '', true) . ",\n";
            $content .= "                'phone' => " . var_export($institucion->phone ?? '', true) . ",\n";
            $content .= "                'email' => " . var_export($institucion->email ?? '', true) . ",\n";
            $content .= "                'website' => " . var_export($institucion->website ?? '', true) . ",\n";
            $content .= "                'footer_licencia_texto' => " . var_export($institucion->footer_licencia_texto ?? '', true) . ",\n";
            $content .= "                'footer_ciudad' => " . var_export($institucion->footer_ciudad ?? '', true) . ",\n";
            $content .= "                'footer_mostrar_ubicacion_fecha' => " . ($institucion->footer_mostrar_ubicacion_fecha ?? false ? 'true' : 'false') . ",\n";
            $content .= "            ]\n";
            $content .= "        );\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/AcademicCatalogSeeder.php'), $content);
        $this->info('✓ AcademicCatalogSeeder actualizado');
    }

    private function updateConsecutiveSeeder($consecutives)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n\n";
        $content .= "use Illuminate\Database\Seeder;\n";
        $content .= "use Illuminate\Support\Facades\DB;\n";
        $content .= "use App\Models\consecutive;\n\n";
        $content .= "class ConsecutiveSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($consecutives as $item) {
            $content .= "        consecutive::create([\n";
            $content .= "            'type' => " . var_export($item->type, true) . ",\n";
            $content .= "            'num_start' => " . var_export($item->num_start, true) . ",\n";
            $content .= "            'num_current' => " . var_export($item->num_current, true) . ",\n";
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/ConsecutiveSeeder.php'), $content);
        $this->info('✓ ConsecutiveSeeder actualizado');
    }

    private function updatePasswordPrivilegesSeeder($passwordPrivileges)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n";
        $content .= "use Illuminate\Support\Facades\DB;\n";
        $content .= "use App\Models\PasswordPrivileges;\n";
        $content .= "use Illuminate\Database\Seeder;\n\n";
        $content .= "class PasswordPrivilegesSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($passwordPrivileges as $item) {
            $content .= "        PasswordPrivileges::create(['password' => " . var_export($item->password, true) . "]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/PasswordPrivilegesSeeder.php'), $content);
        $this->info('✓ PasswordPrivilegesSeeder actualizado');
    }

    private function updateConceptDischargeReceiptSeeder($conceptDischargeReceipts)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n";
        $content .= "use App\Models\ConceptDischargeReceipt;\n";
        $content .= "use Illuminate\Database\Seeder;\n\n";
        $content .= "class ConceptDischargeReceiptSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($conceptDischargeReceipts as $item) {
            $content .= "        ConceptDischargeReceipt::create([\n";
            $content .= "            'name' => " . var_export($item->name, true) . ",\n";
            $content .= "            'state' => " . ($item->state ? 'true' : 'false') . ",\n";
            $content .= "            'debe' => " . var_export($item->debe, true) . ",\n";
            $content .= "            'haber' => " . var_export($item->haber, true) . ",\n";
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/ConceptDischargeReceiptSeeder.php'), $content);
        $this->info('✓ ConceptDischargeReceiptSeeder actualizado');
    }

    private function updateConceptEntryReceiptSeeder($conceptEntryReceipts)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n";
        $content .= "use App\Models\ConceptEntryReceipt;\n";
        $content .= "use Illuminate\Database\Seeder;\n\n";
        $content .= "class ConceptEntryReceiptSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($conceptEntryReceipts as $item) {
            $content .= "        ConceptEntryReceipt::create([\n";
            $content .= "            'name' => " . var_export($item->name, true) . ",\n";
            $content .= "            'state' => " . ($item->state ? 'true' : 'false') . ",\n";
            $content .= "            'debe' => " . var_export($item->debe, true) . ",\n";
            $content .= "            'haber' => " . var_export($item->haber, true) . ",\n";
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/ConceptEntryReceiptSeeder.php'), $content);
        $this->info('✓ ConceptEntryReceiptSeeder actualizado');
    }

    private function updateThirdActivitySeeder($thirdActivities)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n";
        $content .= "use App\Models\thirdActivity;\n";
        $content .= "use Illuminate\Database\Seeder;\n\n";
        $content .= "class ThirdActivitySeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($thirdActivities as $item) {
            $content .= "        thirdActivity::create([\n";
            $content .= "            'nombre' => " . var_export($item->nombre, true) . ",\n";
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/ThirdActivitySeeder.php'), $content);
        $this->info('✓ ThirdActivitySeeder actualizado');
    }

    private function updateThirdEntrySeeder($thirdEntries)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n";
        $content .= "use App\Models\thirdEntry;\n";
        $content .= "use Illuminate\Database\Seeder;\n\n";
        $content .= "class ThirdEntrySeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($thirdEntries as $item) {
            $content .= "        thirdEntry::updateOrCreate(\n";
            $content .= "            ['cedula' => " . var_export($item->cedula, true) . "],\n";
            $content .= "            [\n";
            $content .= "                'nombre' => " . var_export($item->nombre, true) . ",\n";
            $content .= "                'direccion' => " . var_export($item->direccion ?? null, true) . ",\n";
            $content .= "                'telefono' => " . var_export($item->telefono ?? null, true) . ",\n";
            $content .= "                'actividad' => " . var_export($item->actividad ?? null, true) . ",\n";
            $content .= "            ]\n";
            $content .= "        );\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/ThirdEntrySeeder.php'), $content);
        $this->info('✓ ThirdEntrySeeder actualizado');
    }

    private function updateEgresoConceptSeeder($egresoConcepts)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\Seeders;\n";
        $content .= "use App\Models\EgresoConcept;\n";
        $content .= "use Illuminate\Database\Seeder;\n\n";
        $content .= "class EgresoConceptSeeder extends Seeder\n";
        $content .= "{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     *\n";
        $content .= "     * @return void\n";
        $content .= "     */\n";
        $content .= "    public function run()\n";
        $content .= "    {\n";

        foreach ($egresoConcepts as $item) {
            $content .= "        EgresoConcept::create([\n";
            $content .= "            'nombre' => " . var_export($item->nombre, true) . ",\n";
            if (!empty($item->descripcion)) {
                $content .= "            'descripcion' => " . var_export($item->descripcion, true) . ",\n";
            }
            $content .= "        ]);\n";
        }

        $content .= "    }\n";
        $content .= "}\n";

        File::put(database_path('seeders/EgresoConceptSeeder.php'), $content);
        $this->info('✓ EgresoConceptSeeder actualizado');
    }
}
