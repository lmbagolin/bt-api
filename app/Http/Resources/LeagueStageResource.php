<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PlayerResource;

class LeagueStageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                          => $this->id,
            'league_id'                   => $this->league_id,
            'data_etapa'                  => $this->data_etapa,
            'data_abertura_inscricoes'    => $this->data_abertura_inscricoes,
            'valor_inscricao'             => $this->valor_inscricao,
            'tipo'                       => $this->tipo,
            'jogadores_por_grupo'        => $this->jogadores_por_grupo,
            'vagas'                      => $this->vagas,
            'classificam_total'          => $this->classificam_total,
            'disputa_3_lugar'            => $this->disputa_3_lugar,
            'pontuacao_1'                => $this->pontuacao_1,
            'pontuacao_2'                => $this->pontuacao_2,
            'pontuacao_3'                => $this->pontuacao_3,
            'pontuacao_4'                => $this->pontuacao_4,
            'pontuacao_classificados'    => $this->pontuacao_classificados,
            'pontuacao_fase_grupo'       => $this->pontuacao_fase_grupo,
            'pontuacao_extra_1_grupo'    => $this->pontuacao_extra_1_grupo,
            'sorteio_playoffs'           => $this->sorteio_playoffs,
            'confrontos_playoffs'        => $this->confrontos_playoffs,
            'sorteio_grupos'             => $this->sorteio_grupos,
            'players'                    => PlayerResource::collection($this->whenLoaded('players')),
            'created_at'                 => $this->created_at,
            'updated_at'                 => $this->updated_at,
        ];
    }
}
