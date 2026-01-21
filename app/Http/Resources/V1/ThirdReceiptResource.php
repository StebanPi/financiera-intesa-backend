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
            'concepto' => $this->concepto,
            'detalles' => $this->detalles,
            'valor' => $this->valor,
            'debe' => $this->debe,
            'haber' => $this->haber,
            'elaborado_por' => $this->elaborado_por,
            'fecha_recibo' => $this->fecha_recibo instanceof \DateTimeInterface ? $this->fecha_recibo->format('Y-m-d') : $this->fecha_recibo,
            'forma' => $this->forma ?? 'Efectivo',
        ];
    }
}
