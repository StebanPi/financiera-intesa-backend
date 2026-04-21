<?php

namespace App\Services;

use App\Models\consecutive;
use App\Models\Cost;
use App\Models\Entry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EntryService
{
    public function __construct(
        private CostService $costService
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Entry
    {
        $forma = $data['forma'] ?? 'Efectivo';
        if ($forma === 'Consignación') {
            $forma = 'Bancos';
        }

        $entry = null;

        DB::transaction(function () use ($data, $forma, &$entry) {
            $noRecibo = null;

            $con = consecutive::where('type', 'entry')
                ->where('sede', $data['sede'] ?? 'BARRANCABERMEJA')
                ->lockForUpdate()->first();
            if (! $con) {
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

            $codAlumno = $data['cod_alumno'] ?? null;
            if (!$codAlumno && !empty($data['id_cost'])) {
                $cost = Cost::find($data['id_cost']);
                $codAlumno = $cost ? $cost->cod_alumno : null;
            }

            $entry = Entry::create([
                'id_cost' => $data['id_cost'] ?? null,
                'cod_alumno' => $codAlumno,
                'concepto' => $data['concepto'],
                'descripcion' => $data['descripcion'],
                'no_recibo' => $noRecibo,
                'fecha_recibo' => $data['fecha_recibo'],
                'valor' => (string) Str::replace('.', '', $data['valor'] ?? '0'),
                'elaborado_por' => $data['elaborado_por'],
                'debe' => $data['debe'],
                'haber' => $data['haber'],
                'forma' => $forma,
                'sede' => $data['sede'] ?? 'BARRANCABERMEJA',
            ]);

            if (!empty($data['id_cost'])) {
                $this->costService->ensurePursesForCost((int) $data['id_cost']);
            }
        });

        return $entry->fresh();
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $entry = Entry::findOrFail($id);
            $entry->delete();
        });
    }
}
