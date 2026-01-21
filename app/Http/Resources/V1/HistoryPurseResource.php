<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryPurseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_purse' => $this->id_purse,
            'fecha_pago' => $this->fecha_pago instanceof \DateTimeInterface ? $this->fecha_pago->format('Y-m-d') : $this->fecha_pago,
            'estado' => $this->estado,
            'cuota' => $this->cuota,
            'abonado' => $this->abonado ?? 0,
            'comentario' => $this->comentario,
        ];
    }
}
