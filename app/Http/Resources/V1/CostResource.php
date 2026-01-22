<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cod_alumno' => $this->cod_alumno,
            'numero_semestre' => $this->numero_semestre,
            'valor_semestre' => $this->valor_semestre,
            'valor_total_semestre' => $this->valor_total_semestre,
            'descuento' => $this->descuento,
            'valor_neto' => $this->valor_neto,
            'saldo_financiar' => $this->saldo_financiar,
            'periodo' => $this->periodo,
            'numero_cuotas' => $this->numero_cuotas,
            'valor_cuotas' => $this->valor_cuotas,
            'fecha_pago' => $this->fecha_pago instanceof \DateTimeInterface ? $this->fecha_pago->format('Y-m-d') : $this->fecha_pago,
            'detalles' => $this->detalles,
            'cuotas' => PurseResource::collection($this->whenLoaded('purses')),
        ];
    }
}
