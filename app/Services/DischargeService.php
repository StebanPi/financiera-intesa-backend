<?php

namespace App\Services;

use App\Models\consecutive;
use App\Models\EgresoConcept;
use App\Models\EgresoReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DischargeService
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): EgresoReceipt
    {
        $concept = EgresoConcept::findOrFail($data['concepto']);
        if (!$concept->debe || !$concept->haber) {
            throw ValidationException::withMessages([
                'concepto' => ['El concepto seleccionado no tiene debe y haber configurados.'],
            ]);
        }

        $receipt = null;
        DB::transaction(function () use ($data, $concept, &$receipt) {
            $con = consecutive::where('type', 'discharge')->lockForUpdate()->first();
            if (!$con) {
                throw ValidationException::withMessages([
                    'consecutive' => ['Falta configurar el consecutivo de tipo "discharge". Configure el consecutivo en Ajustes.'],
                ]);
            }
            $start = (int) $con->num_start;
            $current = (int) $con->num_current;
            if ($current < $start) {
                $current = $start;
            }
            $noRecibo = $current;
            $con->num_current = $current + 1;
            $con->save();

            $valor = is_string($data['valor'] ?? null) ? Str::replace('.', '', $data['valor']) : ($data['valor'] ?? 0);

            $receipt = EgresoReceipt::create([
                'no_recibo' => $noRecibo,
                'fecha_recibo' => $data['fecha_recibo'],
                'proveedor_id' => $data['proveedor_id'],
                'forma' => $data['forma'],
                'concepto' => $concept->id,
                'descripcion' => $data['descripcion'] ?? null,
                'valor' => $valor,
                'elaborado_por' => $data['elaborado_por'],
                'debe' => $concept->debe,
                'haber' => $concept->haber,
            ]);
        });

        return $receipt->fresh();
    }

    public function update(int $id, array $data): EgresoReceipt
    {
        $r = EgresoReceipt::findOrFail($id);

        if (isset($data['concepto']) && $data['concepto'] != $r->concepto) {
            $concept = EgresoConcept::findOrFail($data['concepto']);
            if (!$concept->debe || !$concept->haber) {
                throw ValidationException::withMessages([
                    'concepto' => ['El concepto seleccionado no tiene debe y haber configurados.'],
                ]);
            }
            $data['debe'] = $concept->debe;
            $data['haber'] = $concept->haber;
        }

        if (isset($data['valor'])) {
            $data['valor'] = is_string($data['valor']) ? Str::replace('.', '', $data['valor']) : $data['valor'];
        }

        $r->update($data);
        return $r->fresh();
    }

    public function delete(int $id): void
    {
        $r = EgresoReceipt::findOrFail($id);
        $r->delete();
    }
}
