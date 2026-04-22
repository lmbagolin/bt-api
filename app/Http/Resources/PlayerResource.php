<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'nickname'  => $this->nickname,
            'gender'    => $this->gender,
            'level'     => $this->level,
            'city'      => $this->city,
            'whatsapp'  => $this->whatsapp,
            'instagram' => $this->instagram,
            'image_url' => $this->image?->url,
            'player_status' => $this->whenPivotLoaded('league_stage_players', fn () => $this->pivot->player_status),
            'arenas'    => ArenaResource::collection($this->whenLoaded('arenas')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
