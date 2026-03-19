<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SedeMiddleware
{
    private const VALID_SEDES = ['BARRANCABERMEJA', 'AGUACHICA'];

    public function handle(Request $request, Closure $next): Response
    {
        $sede = $request->header('X-Sede');

        if (!$sede || !in_array($sede, self::VALID_SEDES, true)) {
            $sede = $request->user()?->sede ?? 'BARRANCABERMEJA';
        }

        if (!in_array($sede, self::VALID_SEDES, true)) {
            $sede = 'BARRANCABERMEJA';
        }

        $request->merge(['sede_activa' => $sede]);

        return $next($request);
    }
}
