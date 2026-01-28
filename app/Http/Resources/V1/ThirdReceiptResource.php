<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdReceiptResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_recibo' => $this->no_recibo,
            'type' => $this->type,
            'third' => $this->third,
            'third_nombre' => $this->thirdObject->nombre ?? null,
            'third_cedula' => $this->thirdObject->cedula ?? null,
            'concepto' => $this->concepto,
            'concepto_nombre' => $this->conceptoObject->name ?? $this->conceptoObject->concepto ?? null,
            'detalles' => $this->detalles,
            'valor' => $this->valor,
            'debe' => $this->debe,
            'haber' => $this->haber,
            'elaborado_por' => $this->elaborado_por,
            'elaborado_nombre' => $this->elaboradoObject->nombre ?? null,
            'fecha_recibo' => $this->fecha_recibo instanceof \DateTimeInterface ? $this->fecha_recibo->format('Y-m-d') : $this->fecha_recibo,
            'forma' => $this->forma ?? 'Efectivo',
        ];
    }
}
