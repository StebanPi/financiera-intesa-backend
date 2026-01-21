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
        $query = Cost::query();

        if ($request->filled('cod_alumno')) {
            $query->where('cod_alumno', $request->cod_alumno);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);

        return $query->orderBy('id')->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Cost
    {
        if (Cost::where('cod_alumno', $data['cod_alumno'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cod_alumno' => ['Ya existe un costo para este alumno.'],
            ]);
        }

        $cost = Cost::create($data);
        $this->createPursesForCost($cost);

        return $cost->fresh();
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
}
