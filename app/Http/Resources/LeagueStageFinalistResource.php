<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueStageFinalistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $color = $this->group?->groupRegistrations
            ->where('registration_id', $this->registration_id)
            ->first()?->color ?? '#64748b';

        return [
            'id'             => $this->id,
            'registration_id'=> $this->registration_id,
            'group_id'       => $this->group_id,
            'group_letter'   => $this->group?->letter,
            'group_position' => $this->group_position,
            'pts'            => $this->pts,
            'gp'             => $this->gp,
            'gc'             => $this->gc,
            'saldo_games'    => $this->saldo_games,
            'player'         => $this->whenLoaded('registration', fn () => [
                'name'  => $this->registration->player?->name,
                'color' => $color,
            ]),
        ];
    }
}
