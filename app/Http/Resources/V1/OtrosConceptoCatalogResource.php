<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OtrosConceptoCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_number' => $this->display_number ?? $this->id,
            'nombre' => $this->nombre,
            'estado' => $this->estado,
            'debe' => $this->debe,
            'haber' => $this->haber,
        ];
    }
}
