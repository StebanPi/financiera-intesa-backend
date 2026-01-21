<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ApiSmokeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:smoke';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta checks rápidos vía HTTP para validar endpoints básicos de la API v1';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseUrl = config('app.url');
        $token = env('API_SMOKE_TOKEN');

        if (empty($token)) {
            $this->error('ERROR: API_SMOKE_TOKEN no está definido en .env');
            $this->info('Define API_SMOKE_TOKEN=tu_token_en_.env para ejecutar los checks.');
            return 1;
        }

        $this->info("Ejecutando smoke tests en: {$baseUrl}");
        $this->newLine();

        $failed = false;

        // Check: GET /api/v1/health
        $this->line('Checking: GET /api/v1/health');
        $response = Http::get("{$baseUrl}/api/v1/health");
        
        if ($response->successful() && $response->json('data.status') === 'ok') {
            $this->info('  ✓ PASS');
        } else {
            $this->error('  ✗ FAIL');
            $this->error('    Status: ' . $response->status());
            $failed = true;
        }
        $this->newLine();

        // Check: GET /api/v1/home (requiere token)
        $this->line('Checking: GET /api/v1/home');
        $response = Http::withToken($token)
            ->get("{$baseUrl}/api/v1/home");
        
        if ($response->successful() && $response->json('data.message')) {
            $this->info('  ✓ PASS');
        } else {
            $this->error('  ✗ FAIL');
            $this->error('    Status: ' . $response->status());
            if ($response->json('error.code')) {
                $this->error('    Error: ' . $response->json('error.code'));
            }
            $failed = true;
        }
        $this->newLine();

        if ($failed) {
            $this->error('Algunos checks fallaron.');
            return 1;
        }

        $this->info('Todos los checks pasaron.');
        return 0;
    }
}
