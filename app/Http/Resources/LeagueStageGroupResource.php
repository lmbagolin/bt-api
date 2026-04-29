<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueStageGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Build a map: player_id → { id, name, color }
        $playersMap = $this->groupRegistrations->mapWithKeys(function ($gr) {
            $player      = $gr->registration?->player;
            $partnerName = $gr->registration?->partner?->name ?? $gr->registration?->partner_name;

            return [$player?->id => [
                'id'              => $player?->id,
                'registration_id' => $gr->registration_id,
                'name'            => $player?->name ?? 'Jogador',
                'partner_name'    => $partnerName,
                'color'           => $gr->color,
            ]];
        });

        $matches = $this->matches->map(function ($match) use ($playersMap) {
            return [
                'id'           => $match->id,
                'match_number' => $match->match_number,
                'd1'           => array_values(array_filter([
                    $playersMap[$match->d1_player1_id] ?? null,
                    $playersMap[$match->d1_player2_id] ?? null,
                ])),
                'd2'           => array_values(array_filter([
                    $playersMap[$match->d2_player1_id] ?? null,
                    $playersMap[$match->d2_player2_id] ?? null,
                ])),
                'score_p'      => $match->score_p,
                'score_q'      => $match->score_q,
            ];
        });

        return [
            'id'      => $this->id,
            'letter'  => $this->letter,
            'players' => $playersMap->values(),
            'matches' => $matches,
        ];
    }
}
