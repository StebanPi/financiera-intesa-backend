<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'sede' => $this->sede,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'slug' => $r->slug])),
            'permissions' => $this->when(
                $this->relationLoaded('roles'),
                function () {
                    $slugs = $this->roles->flatMap->permissions->pluck('slug')->unique()->values()->toArray();
                    return $slugs;
                }
            ),
        ];
    }
}
