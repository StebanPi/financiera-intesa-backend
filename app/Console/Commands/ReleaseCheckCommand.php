<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ReleaseCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Valida el backend antes de desplegar (env, DB, storage, health endpoint)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Ejecutando release checks...');
        $this->newLine();

        $failed = false;
        $warnings = [];

        // Check 1: APP_ENV no debe ser "local" en staging/prod
        $this->line('1. Checking APP_ENV...');
        $appEnv = config('app.env');
        if ($appEnv === 'local') {
            $this->warn('  ⚠ WARNING: APP_ENV=local (debería ser staging o production)');
            $warnings[] = 'APP_ENV está en "local"';
        } else {
            $this->info('  ✓ PASS (APP_ENV=' . $appEnv . ')');
        }
        $this->newLine();

        // Check 2: APP_DEBUG debe ser false
        $this->line('2. Checking APP_DEBUG...');
        $appDebug = config('app.debug');
        if ($appDebug === true) {
            $this->error('  ✗ FAIL: APP_DEBUG=true (debe ser false en staging/prod)');
            $failed = true;
        } else {
            $this->info('  ✓ PASS (APP_DEBUG=false)');
        }
        $this->newLine();

        // Check 3: APP_KEY debe existir
        $this->line('3. Checking APP_KEY...');
        $appKey = config('app.key');
        if (empty($appKey)) {
            $this->error('  ✗ FAIL: APP_KEY no está definido');
            $this->error('    Ejecutar: php artisan key:generate');
            $failed = true;
        } else {
            $this->info('  ✓ PASS (APP_KEY definido)');
        }
        $this->newLine();

        // Check 4: DB conectividad
        $this->line('4. Checking DB connectivity...');
        try {
            DB::select('SELECT 1');
            $this->info('  ✓ PASS (DB conectividad OK)');
        } catch (\Throwable $e) {
            $this->error('  ✗ FAIL: No se puede conectar a la base de datos');
            $this->error('    Error: ' . $e->getMessage());
            $failed = true;
        }
        $this->newLine();

        // Check 5: Storage paths deben existir y ser escribibles
        $this->line('5. Checking storage paths...');
        $logsPath = storage_path('logs');
        $appPath = storage_path('app');

        $storageOk = true;
        if (!File::exists($logsPath)) {
            $this->error('  ✗ FAIL: ' . $logsPath . ' no existe');
            $failed = true;
            $storageOk = false;
        } elseif (!File::isWritable($logsPath)) {
            $this->error('  ✗ FAIL: ' . $logsPath . ' no es escribible');
            $failed = true;
            $storageOk = false;
        }

        if (!File::exists($appPath)) {
            $this->error('  ✗ FAIL: ' . $appPath . ' no existe');
            $failed = true;
            $storageOk = false;
        } elseif (!File::isWritable($appPath)) {
            $this->error('  ✗ FAIL: ' . $appPath . ' no es escribible');
            $failed = true;
            $storageOk = false;
        }

        if ($storageOk) {
            $this->info('  ✓ PASS (storage/logs y storage/app existen y son escribibles)');
        }
        $this->newLine();

        // Check 6: public/storage symlink
        $this->line('6. Checking public/storage symlink...');
        $publicStoragePath = public_path('storage');
        if (!File::exists($publicStoragePath)) {
            $this->warn('  ⚠ WARNING: ' . $publicStoragePath . ' no existe');
            $this->warn('    Ejecutar: php artisan storage:link');
            $warnings[] = 'public/storage symlink no existe';
        } elseif (!is_link($publicStoragePath)) {
            $this->warn('  ⚠ WARNING: ' . $publicStoragePath . ' existe pero no es un symlink');
            $this->warn('    Ejecutar: php artisan storage:link');
            $warnings[] = 'public/storage no es un symlink';
        } else {
            $this->info('  ✓ PASS (public/storage symlink existe)');
        }
        $this->newLine();

        // Check 7: Endpoint health
        $this->line('7. Checking /api/v1/health endpoint...');
        try {
            $healthController = new HealthController();
            $response = $healthController->index();
            $json = $response->getData(true);

            if ($response->getStatusCode() === 200 && isset($json['data']['status']) && $json['data']['status'] === 'ok') {
                $this->info('  ✓ PASS (health endpoint devuelve status=ok)');
            } else {
                $this->error('  ✗ FAIL: health endpoint no devuelve status=ok');
                $this->error('    Status: ' . $response->getStatusCode());
                $failed = true;
            }
        } catch (\Throwable $e) {
            $this->error('  ✗ FAIL: Error al llamar health endpoint');
            $this->error('    Error: ' . $e->getMessage());
            $failed = true;
        }
        $this->newLine();

        // Resumen
        if ($failed) {
            $this->error('✗ Algunos checks fallaron. Corrige los errores antes de desplegar.');
            if (!empty($warnings)) {
                $this->newLine();
                $this->warn('Advertencias:');
                foreach ($warnings as $warning) {
                    $this->warn('  - ' . $warning);
                }
            }
            return 1;
        }

        if (!empty($warnings)) {
            $this->warn('✓ Todos los checks críticos pasaron, pero hay advertencias:');
            foreach ($warnings as $warning) {
                $this->warn('  - ' . $warning);
            }
            $this->newLine();
        }

        $this->info('✓ Todos los checks pasaron. El backend está listo para desplegar.');
        return 0;
    }
}
