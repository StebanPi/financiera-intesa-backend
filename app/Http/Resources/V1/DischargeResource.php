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
            'fecha_formatted' => $this->fecha_recibo instanceof \DateTimeInterface ? $this->fecha_recibo->format('d/m/Y') : $this->fecha_recibo,
            'proveedor_id' => $this->proveedor_id,
            'proveedor_nombre' => $this->whenLoaded('provider', fn() => $this->provider->nombre),
            'forma' => $this->forma,
            'concepto' => $this->concepto,
            'concepto_nombre' => $this->whenLoaded('conceptoObject', fn() => $this->conceptoObject->nombre),
            'descripcion' => $this->descripcion,
            'valor' => (float) $this->valor,
            'valor_formatted' => number_format($this->valor, 0, ',', '.'),
            'elaborado_por' => $this->elaborado_por,
            'elaborado_nombre' => $this->whenLoaded('elaboradoObject', fn() => $this->elaboradoObject->nombre),
            'debe' => $this->debe,
            'haber' => $this->haber,
        ];
    }
}
