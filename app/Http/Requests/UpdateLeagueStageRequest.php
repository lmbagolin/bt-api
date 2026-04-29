<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeagueStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->stage->league->arena->owner_id !== auth()->id()) {
            return false;
        }
        return true;
    }

    public function rules(): array
    {
        return [
            'league_id' => ['sometimes', 'exists:leagues,id'],
            'data_etapa' => ['sometimes', 'date'],
            'data_abertura_inscricoes' => ['nullable', 'date'],
            'valor_inscricao' => ['sometimes', 'integer', 'min:0'],
            'tipo' => ['sometimes', Rule::in(['rei-da-praia', 'dupla-fixa', 'simples'])],
            'jogadores_por_grupo' => ['sometimes', 'integer', 'min:1'],
            'vagas' => ['nullable', 'integer', 'min:1'],
            'classificam_total' => ['nullable', 'integer', Rule::in([4, 8, 12, 16])],
            'disputa_3_lugar' => ['sometimes', 'boolean'],
            'pontuacao_1' => ['sometimes', 'integer', 'min:0'],
            'pontuacao_2' => ['sometimes', 'integer', 'min:0'],
            'pontuacao_3' => ['sometimes', 'integer', 'min:0'],
            'pontuacao_4' => ['sometimes', 'integer', 'min:0'],
            'pontuacao_classificados' => ['sometimes', 'integer', 'min:0'],
            'pontuacao_fase_grupo' => ['sometimes', 'integer', 'min:0'],
            'pontuacao_extra_1_grupo' => ['sometimes', 'integer', 'min:0'],
            'sorteio_playoffs' => ['sometimes', Rule::in(['aleatorio', 'primeiros_colocados', 'primeiros_com_segundos', 'ordem_classificacao', 'manual'])],
            'confrontos_playoffs' => ['sometimes', Rule::in(['aleatorio', 'primeiros_contra_ultimos', 'manual'])],
            'sorteio_grupos' => ['sometimes', Rule::in(['aleatorio', 'pela_ordem'])],
        ];
    }

}
