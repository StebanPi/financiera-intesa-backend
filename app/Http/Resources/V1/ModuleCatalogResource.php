<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_number' => $this->display_number ?? $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'active' => $this->active,
        ];
    }
}
