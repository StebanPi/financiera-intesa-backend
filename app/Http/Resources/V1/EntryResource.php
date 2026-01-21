<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_cost' => $this->id_cost,
            'concepto' => $this->concepto,
            'descripcion' => $this->descripcion,
            'no_recibo' => $this->no_recibo,
            'fecha_recibo' => $this->fecha_recibo instanceof \DateTimeInterface ? $this->fecha_recibo->format('Y-m-d') : $this->fecha_recibo,
            'valor' => $this->valor,
            'elaborado_por' => $this->elaborado_por,
            'debe' => $this->debe,
            'haber' => $this->haber,
            'forma' => $this->forma ?? 'Efectivo',
        ];
    }
}
