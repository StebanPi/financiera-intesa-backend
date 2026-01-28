<?php

namespace App\Services;

use App\Models\consecutive;
use App\Models\ThirdReceipts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ThirdReceiptService
{
    /** @param array<string, mixed> $data */
    public function create(array $data): ThirdReceipts
    {
        $forma = $data['forma'] ?? 'Efectivo';
        if ($forma === 'Consignación') {
            $forma = 'Bancos';
        }

        $receipt = null;
        DB::transaction(function () use ($data, $forma, &$receipt) {
            $con = consecutive::where('type', 'entry')->lockForUpdate()->first();
            if (!$con) {
                throw ValidationException::withMessages([
                    'consecutive' => ['Falta configurar el consecutivo de tipo "entry". Configure el consecutivo en Ajustes.'],
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

            $receipt = ThirdReceipts::create([
                'no_recibo' => $noRecibo,
                'type' => 'entry',
                'third' => $data['third'],
                'concepto' => $data['concepto'],
                'detalles' => $data['detalles'] ?? null,
                'valor' => $valor,
                'debe' => $data['debe'],
                'haber' => $data['haber'],
                'elaborado_por' => $data['elaborado_por'],
                'forma' => $forma,
                'fecha_recibo' => $data['fecha_recibo'],
            ]);
        });

        return $receipt->fresh();
    }

    public function delete(int $id): void
    {
        $r = ThirdReceipts::where('type', 'entry')->findOrFail($id);
        $r->delete();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ThirdReceipts
    {
        $receipt = ThirdReceipts::where('type', 'entry')->findOrFail($id);

        if (isset($data['forma'])) {
            if ($data['forma'] === 'Consignación') {
                $data['forma'] = 'Bancos';
            }
        }

        if (isset($data['valor'])) {
            $data['valor'] = is_string($data['valor']) ? Str::replace('.', '', $data['valor']) : $data['valor'];
        }

        $receipt->update($data);

        return $receipt->fresh();
    }
}
