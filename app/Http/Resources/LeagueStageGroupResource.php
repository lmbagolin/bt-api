<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueStageGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Build a map: registration_id → { id, name, color }
        $playersMap = $this->groupRegistrations->mapWithKeys(function ($gr) {
            $reg         = $gr->registration;
            $name        = $reg?->player?->name ?? 'Jogador';
            $partnerName = $reg?->partner?->name ?? $reg?->partner_name;

            return [$gr->registration_id => [
                'id'           => $gr->registration_id,
                'name'         => $name,
                'partner_name' => $partnerName,
                'color'        => $gr->color,
            ]];
        });

        $matches = $this->matches->map(function ($match) use ($playersMap) {
            return [
                'id'           => $match->id,
                'match_number' => $match->match_number,
                'p'            => array_values(array_filter([
                    $playersMap[$match->p1_registration_id] ?? null,
                    $playersMap[$match->p2_registration_id] ?? null,
                ])),
                'q'            => array_values(array_filter([
                    $playersMap[$match->q1_registration_id] ?? null,
                    $playersMap[$match->q2_registration_id] ?? null,
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
