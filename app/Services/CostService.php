<?php

namespace App\Services;

use App\Http\Controllers\DateController;
use App\Models\Cost;
use App\Models\Entry;
use App\Models\OtherEntry;
use App\Models\Purse;
use App\Models\historyPurse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CostService
{
    private function validateAndAdjustDate(int $year, int $month, int $day): string
    {
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
        $lastDay = (int) date('t', mktime(0, 0, 0, $month, 1, $year));

        return sprintf('%04d-%02d-%02d', $year, $month, $lastDay);
    }

    /**
     * Crea las cuotas (Purses) para un cost si aún no existen.
     * Usado por Entry al crear abonos cuando el cost no tenía purses.
     */
    public function ensurePursesForCost(int $id_cost): void
    {
        $cost = Cost::findOrFail($id_cost);
        if (Purse::where('id_cost', $id_cost)->count() === 0) {
            $this->createPursesForCost($cost);
        }
    }

    private function createPursesForCost(Cost $cost): void
    {
        $fechaActual = explode('-', $cost->fecha_pago);
        $mes = (int) $fechaActual[1];
        $año = (int) $fechaActual[0];
        $dia = (int) ($fechaActual[2] ?? 1);

        for ($i = 0; $i < (int) $cost->numero_cuotas; $i++) {
            if ($i > 0) {
                $mes = (int) DateController::nextMes($mes, true);
                $año = (int) DateController::Is_nextYear((string) $año, $mes);
            }
            $fechaPago = $this->validateAndAdjustDate($año, $mes, $dia);

            Purse::create([
                'id_cost' => $cost->id,
                'fecha_pago' => $fechaPago,
                'estado' => 'Pendiente',
                'cuota' => $cost->valor_cuotas,
                'abonado' => 0,
                'comentario' => 'Fecha de pago establecidas con sus cuotas iniciales.',
            ]);
        }
    }

    private function updatePursesForCost(Cost $cost): void
    {
        $numPurses = Purse::where('id_cost', $cost->id)->count();

        if ($numPurses === (int) $cost->numero_cuotas) {
            $purses = Purse::where('id_cost', $cost->id)->orderBy('id')->get();
            $fechaActual = explode('-', $cost->fecha_pago);
            $mes = (int) $fechaActual[1];
            $año = (int) $fechaActual[0];
            $dia = (int) ($fechaActual[2] ?? 1);

            foreach ($purses as $k => $item) {
                $item->cuota = $cost->valor_cuotas;
                if ($k === 0) {
                    $item->fecha_pago = $cost->fecha_pago;
                } else {
                    for ($j = 0; $j < $k; $j++) {
                        $mes = (int) DateController::nextMes($mes, true);
                        $año = (int) DateController::Is_nextYear((string) $año, $mes);
                    }
                    $item->fecha_pago = $this->validateAndAdjustDate($año, $mes, $dia);
                    $mes = (int) $fechaActual[1];
                    $año = (int) $fechaActual[0];
                }
                $item->save();
            }
        } else {
            $pursesToDelete = Purse::where('id_cost', $cost->id)->get();
            if ($pursesToDelete->isNotEmpty()) {
                historyPurse::whereIn('id_purse', $pursesToDelete->pluck('id'))->delete();
            }
            Purse::where('id_cost', $cost->id)->delete();
            $this->createPursesForCost($cost);
        }
    }

    public function list(Request $request): LengthAwarePaginator
    {
        $query = Cost::query()
            ->join('matriculas', 'costs.cod_alumno', '=', 'matriculas.cod_alumno')
            ->where('matriculas.estado_estudiante', 'Activo')
            ->select('costs.*');

        if ($request->filled('cod_alumno')) {
            $query->where('costs.cod_alumno', $request->cod_alumno);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);

        return $query->orderBy('costs.id')->paginate($perPage);
    }

    /**
     * Obtiene todos los costos de un estudiante, ordenados por semestre
     */
    public function getByStudent(string $cod_alumno): \Illuminate\Support\Collection
    {
        return Cost::where('cod_alumno', $cod_alumno)
            ->orderBy('numero_semestre')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Cost
    {
        // En el nuevo sistema, permitimos múltiples semestres,
        // pero validamos que no exista el mismo semestre para el mismo alumno.
        if (Cost::where('cod_alumno', $data['cod_alumno'])
                ->where('numero_semestre', $data['numero_semestre'] ?? 1)
                ->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'numero_semestre' => ['Ya existe una configuración para este semestre.'],
            ]);
        }

        $cost = Cost::create($data);
        $this->createPursesForCost($cost);

        return $cost->fresh();
    }

    /**
     * Sincroniza múltiples registros de costos para un estudiante
     *
     * @param string $cod_alumno
     * @param array $semestres
     * @return void
     */
    public function syncStudentCosts(string $cod_alumno, array $semestres): void
    {
        $semestresRecibidos = [];
        
        foreach ($semestres as $data) {
            $numSemestre = $data['numero_semestre'];
            $semestresRecibidos[] = $numSemestre;
            $data['cod_alumno'] = $cod_alumno;

            $cost = Cost::where('cod_alumno', $cod_alumno)
                        ->where('numero_semestre', $numSemestre)
                        ->first();

            if (!$cost) {
                $cost = Cost::create($data);
                $this->createPursesForCost($cost);
            } else {
                $cost->update($data);
                $this->updatePursesForCost($cost->fresh());
            }
        }

        // Eliminar semestres que ya no están en la lista y todas sus relaciones (Solo si no tienen historia financiera)
        $costsAEliminar = Cost::where('cod_alumno', $cod_alumno)
            ->whereNotIn('numero_semestre', $semestresRecibidos)
            ->get();
        
        foreach ($costsAEliminar as $costEliminar) {
            $hasFinancialHistory = Entry::where('id_cost', $costEliminar->id)->exists() ||
                                   OtherEntry::where('id_cost', $costEliminar->id)->exists();

            if (!$hasFinancialHistory) {
                // Eliminar history_purses asociados
                $purseIds = Purse::where('id_cost', $costEliminar->id)->pluck('id');
                if ($purseIds->isNotEmpty()) {
                    historyPurse::whereIn('id_purse', $purseIds)->delete();
                }
                // Eliminar purses asociados
                Purse::where('id_cost', $costEliminar->id)->delete();

                // Eliminar el cost
                $costEliminar->delete();
            }
        }

        // Reasignar entries huérfanos al costo más reciente
        $this->reassignOrphanedEntriesToCost($cod_alumno);
    }

    public function getById(int $id): Cost
    {
        return Cost::findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): Cost
    {
        $cost = Cost::findOrFail($id);
        $fillable = (new Cost)->getFillable();
        $filtered = array_intersect_key($data, array_flip($fillable));
        $cost->update($filtered);
        $this->updatePursesForCost($cost->fresh());

        return $cost->fresh();
    }

    public function delete(int $id): void
    {
        $cost = Cost::findOrFail($id);

        $entriesCount = Entry::where('id_cost', $cost->id)->count();
        $otherCount = OtherEntry::where('id_cost', $cost->id)->count();

        if ($entriesCount > 0 || $otherCount > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'id' => ["No se puede eliminar el costo: tiene {$entriesCount} abonos y {$otherCount} otros ingresos asociados."],
            ]);
        }

        $purseIds = Purse::where('id_cost', $cost->id)->pluck('id');
        if ($purseIds->isNotEmpty()) {
            historyPurse::whereIn('id_purse', $purseIds)->delete();
        }
        Purse::where('id_cost', $cost->id)->delete();
        $cost->delete();
    }

    /**
     * Elimina todos los costos y relaciones financieras de un estudiante
     * Similar a eliminarCostosEstudiante del controlador legacy
     */
    public function deleteAllForStudent(string $cod_alumno): array
    {
        $costs = Cost::where('cod_alumno', $cod_alumno)->get();
        
        $eliminados = [
            'history_purses' => 0,
            'purses' => 0,
            'entries' => 0,
            'other_entries' => 0,
            'costs' => 0
        ];

        if ($costs->isEmpty()) {
            return $eliminados;
        }

        foreach ($costs as $cost) {
             // 1. Eliminar history_purses asociados a los purses del cost
             $purseIds = Purse::where('id_cost', $cost->id)->pluck('id')->toArray();
             if (!empty($purseIds)) {
                 $eliminados['history_purses'] += historyPurse::whereIn('id_purse', $purseIds)->count();
                 historyPurse::whereIn('id_purse', $purseIds)->delete();
             }

             // 2. Eliminar purses asociados al cost
             $eliminados['purses'] += Purse::where('id_cost', $cost->id)->count();
             Purse::where('id_cost', $cost->id)->delete();

             // 3. Desvincular entries (abonos) y other_entries (otros ingresos) - NO eliminar
              // IMPORTANTE: Establecer cod_alumno para mantener referencia al estudiante
              $eliminados['entries'] += Entry::where('id_cost', $cost->id)->count();
              Entry::where('id_cost', $cost->id)->update([
                  'id_cost' => null,
                  'cod_alumno' => $cost->cod_alumno
              ]);

              $eliminados['other_entries'] += OtherEntry::where('id_cost', $cost->id)->count();
              OtherEntry::where('id_cost', $cost->id)->update([
                  'id_cost' => null,
                  'cod_alumno' => $cost->cod_alumno
              ]);

              // 4. Eliminar el cost (hard reset, sin restricciones)
              $cost->delete();
              $eliminados['costs']++;
        }

        return $eliminados;
    }

    /**
     * Reasigna todos los entries huérfanos de un estudiante al costo más reciente.
     * Se llama automáticamente después de syncStudentCosts.
     */
    public function reassignOrphanedEntriesToCost(string $codAlumno): void
    {
        // Obtener el costo más reciente del estudiante (último semestre)
        $cost = Cost::where('cod_alumno', $codAlumno)
            ->orderBy('numero_semestre', 'desc')
            ->first();

        if (!$cost) {
            return; // No hay costo al que reasignar
        }

        // Reasignar entries huérfanos (id_cost = null) de este estudiante
        Entry::where('cod_alumno', $codAlumno)
            ->whereNull('id_cost')
            ->update(['id_cost' => $cost->id]);

        OtherEntry::where('cod_alumno', $codAlumno)
            ->whereNull('id_cost')
            ->update(['id_cost' => $cost->id]);
    }

    /**
     * Puebla el cod_alumno en entries huérfanos que lo tienen como null.
     * Los entries que tienen id_cost = null pero cod_alumno = null son actualizados
     * basándose en la relación histórica con costs ya eliminados.
     */
    public function populateCodAlumnoForOrphanedEntries(): int
    {
        $count = 0;

        // Obtener todos los cod_alumno únicos de costos eliminados (soft reference)
        // Buscar entries con id_cost = null y cod_alumno = null
        $orphanEntries = Entry::whereNull('id_cost')->whereNull('cod_alumno')->get();

        foreach ($orphanEntries as $entry) {
            // Buscar si hay algún costo histórico para este entry (usando el último costo conocido del sistema)
            // Como los costos fueron eliminados, usamos una consulta diferente
            // Buscar en la tabla de costos más reciente para encontrar el cod_alumno
            $entryHistorical = DB::table('entries')
                ->whereNotNull('cod_alumno')
                ->where('id', '!=', $entry->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($entryHistorical) {
                $entry->update(['cod_alumno' => $entryHistorical->cod_alumno]);
                $count++;
            }
        }

        // Hacer lo mismo para OtherEntry
        $orphanOtherEntries = OtherEntry::whereNull('id_cost')->whereNull('cod_alumno')->get();

        foreach ($orphanOtherEntries as $entry) {
            $entryHistorical = DB::table('other_entries')
                ->whereNotNull('cod_alumno')
                ->where('id', '!=', $entry->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($entryHistorical) {
                $entry->update(['cod_alumno' => $entryHistorical->cod_alumno]);
                $count++;
            }
        }

        return $count;
    }
}
