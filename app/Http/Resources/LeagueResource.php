<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'arena_id'               => $this->arena_id,
            'nome'                   => $this->nome,
            'data_inicio'            => $this->data_inicio,
            'data_prevista_termino'  => $this->data_prevista_termino,
            'numero_etapas'          => $this->numero_etapas,
            'descricao'              => $this->descricao,
            'premiacao'              => $this->premiacao,
            'nivel'                  => $this->nivel,
            'arena'                  => $this->whenLoaded('arena', fn () => [
                'id'   => $this->arena->id,
                'name' => $this->arena->name,
                'city' => $this->arena->city,
            ]),
            'stages'                 => $this->whenLoaded('stages', fn () => LeagueStageResource::collection($this->stages)),
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
