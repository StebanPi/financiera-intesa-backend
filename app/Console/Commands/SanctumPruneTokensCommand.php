<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SanctumPruneTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:prune-tokens {--days=30 : Número de días después del cual borrar tokens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Borra tokens de Sanctum (personal_access_tokens) más antiguos que el número de días especificado';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('El número de días debe ser mayor a 0.');
            return 1;
        }

        $this->info("Buscando tokens creados hace más de {$days} días...");

        // Calcular fecha límite
        $cutoffDate = now()->subDays($days);

        // Contar tokens a borrar antes de borrarlos
        $count = DB::table('personal_access_tokens')
            ->where('created_at', '<', $cutoffDate)
            ->count();

        if ($count === 0) {
            $this->info('No se encontraron tokens antiguos para borrar.');
            return 0;
        }

        // Borrar tokens antiguos
        $deleted = DB::table('personal_access_tokens')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("✓ Se borraron {$deleted} token(s) antiguo(s) (creados antes del {$cutoffDate->format('Y-m-d H:i:s')}).");

        return 0;
    }
}
