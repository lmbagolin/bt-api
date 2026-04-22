<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueStageRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'status'        => $this->status,
            'valor_pago'    => $this->valor_pago,
            'posicao_grupo' => $this->posicao_grupo,
            'observacoes'   => $this->observacoes,
            'player'        => new PlayerResource($this->whenLoaded('player')),
        ];
    }
}
