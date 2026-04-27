<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueStagePlayoffMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'round_name'     => $this->round_name,
            'match_number'   => $this->match_number,
            'is_bye'         => $this->is_bye,
            'score_p'        => $this->score_p,
            'score_q'        => $this->score_q,
            'winner_pair_id' => $this->winner_pair_id,
            'pair1_id'       => $this->pair1_id,
            'pair2_id'       => $this->pair2_id,
            'pair1'          => $this->pair1_id ? new LeagueStagePlayoffPairResource($this->pair1) : null,
            'pair2'          => $this->pair2_id ? new LeagueStagePlayoffPairResource($this->pair2) : null,
        ];
    }
}
