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
            'data_inicio'            => $this->data_inicio?->format('Y-m-d'),
            'data_prevista_termino'  => $this->data_prevista_termino?->format('Y-m-d'),
            'numero_etapas'          => $this->numero_etapas,
            'descricao'              => $this->descricao,
            'premiacao'              => $this->premiacao,
            'nivel'                  => $this->nivel,
            'genero'                 => $this->genero,
            'arena'                  => $this->whenLoaded('arena', fn () => [
                'id'       => $this->arena->id,
                'name'     => $this->arena->name,
                'city'     => $this->arena->city,
                'logo_url' => $this->arena->logo?->url,
            ]),
            'stages'                 => $this->whenLoaded('stages', fn () => LeagueStageResource::collection($this->stages)),
            'total_stages'           => $this->whenLoaded('stages', fn () => $this->stages->count()),
            'closed_stages'          => $this->whenLoaded('stages', fn () => $this->stages->where('stage_status', 'closed')->count()),
            'active_stage'           => $this->whenLoaded('stages', function () {
                $stage = $this->stages
                    ->where('stage_status', '!=', 'closed')
                    ->sortByDesc('data_etapa')
                    ->first();
                if (!$stage) return null;
                return [
                    'id'            => $stage->id,
                    'tipo'          => $stage->tipo,
                    'data_etapa'    => $stage->data_etapa?->format('Y-m-d H:i'),
                    'stage_status'  => $stage->stage_status,
                    'registrations' => $stage->registrations->count(),
                ];
            }),
            'last_closed_stage'      => $this->whenLoaded('stages', function () {
                $stage = $this->stages
                    ->where('stage_status', 'closed')
                    ->sortByDesc('data_etapa')
                    ->first();
                if (!$stage) return null;
                return [
                    'id'         => $stage->id,
                    'tipo'       => $stage->tipo,
                    'data_etapa' => $stage->data_etapa?->format('Y-m-d H:i'),
                ];
            }),
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
