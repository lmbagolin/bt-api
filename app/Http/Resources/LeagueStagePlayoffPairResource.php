<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueStagePlayoffPairResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'pair_rank'    => $this->pair_rank,
            'pts_total'    => $this->pts_total,
            'gp_total'     => $this->gp_total,
            'gc_total'     => $this->gc_total,
            'sg_total'     => $this->sg_total,
            'position_sum' => $this->position_sum,
            'finalist1'    => $this->finalistData($this->finalist1),
            'finalist2'    => $this->finalist2_id ? $this->finalistData($this->finalist2) : null,
        ];
    }

    private function finalistData($finalist): ?array
    {
        if (!$finalist) return null;
        $color = $finalist->group?->groupRegistrations
            ->where('registration_id', $finalist->registration_id)
            ->first()?->color ?? '#64748b';

        return [
            'id'             => $finalist->id,
            'registration_id'=> $finalist->registration_id,
            'group_letter'   => $finalist->group?->letter,
            'group_position' => $finalist->group_position,
            'pts'            => $finalist->pts,
            'gp'             => $finalist->gp,
            'gc'             => $finalist->gc,
            'saldo_games'    => $finalist->saldo_games,
            'color'          => $color,
            'player'         => [
                'name'         => $finalist->registration?->player?->name,
                'partner_name' => $finalist->registration?->partner?->name
                    ?? $finalist->registration?->partner_name,
            ],
        ];
    }
}
