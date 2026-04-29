<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArenaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'city_id'  => $this->city_id,
            'city'     => $this->whenLoaded('city', fn () => [
                'id'         => $this->city->id,
                'name'       => $this->city->name,
                'state_code' => $this->city->state_code,
            ]),
            'owner_id' => $this->owner_id,
            'logo_url' => $this->logo?->url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
