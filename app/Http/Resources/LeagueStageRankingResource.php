<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueStageRankingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reg = $this->registration;
        $player = $reg?->player;

        $partner = $reg?->partner;
        $partnerName = $partner?->name ?? $reg?->partner_name;

        return [
            'id'             => $this->id,
            'position'       => $this->position,
            'points'         => $this->points,
            'wins'           => $this->wins,
            'matches_played' => $this->matches_played,
            'games_pro'      => $this->games_pro,
            'games_against'  => $this->games_against,
            'saldo_games'    => $this->games_pro - $this->games_against,
            'registration_id' => $this->registration_id,
            'player' => $player ? [
                'id'          => $player->id,
                'name'        => $player->name,
                'partner_name' => $partnerName,
                'color'       => $reg->groupRegistration?->color ?? '#64748b',
            ] : null,
        ];
    }
}
