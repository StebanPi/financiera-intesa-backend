<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DischargeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_recibo' => $this->no_recibo,
            'fecha_recibo' => $this->fecha_recibo instanceof \DateTimeInterface ? $this->fecha_recibo->format('Y-m-d') : $this->fecha_recibo,
            'proveedor_id' => $this->proveedor_id,
            'forma' => $this->forma,
            'concepto' => $this->concepto,
            'descripcion' => $this->descripcion,
            'valor' => $this->valor,
            'elaborado_por' => $this->elaborado_por,
            'debe' => $this->debe,
            'haber' => $this->haber,
        ];
    }
}
