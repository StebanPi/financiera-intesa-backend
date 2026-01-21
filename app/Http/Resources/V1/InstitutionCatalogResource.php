<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstitutionCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo_path' => $this->logo_path,
            'institucion_subtitulo' => $this->institucion_subtitulo,
            'sede' => $this->sede,
            'nit' => $this->nit,
            'address' => $this->address,
            'phone' => $this->phone,
            'telefono2' => $this->telefono2,
            'telefono3' => $this->telefono3,
            'email' => $this->email,
            'website' => $this->website,
            'footer_licencia_texto' => $this->footer_licencia_texto,
            'footer_ciudad' => $this->footer_ciudad,
            'footer_mostrar_ubicacion_fecha' => $this->footer_mostrar_ubicacion_fecha,
            'footer_firma' => $this->footer_firma,
        ];
    }
}
