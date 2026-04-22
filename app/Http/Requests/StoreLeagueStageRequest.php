<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeagueStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'league_id' => ['required', 'exists:leagues,id'],
            'data_etapa' => ['required', 'date'],
            'data_abertura_inscricoes' => ['nullable', 'date'],
            'valor_inscricao' => ['required', 'integer', 'min:0'],
            'tipo' => ['required', 'string', 'max:255'],
            'jogadores_por_grupo' => ['required', 'integer', 'min:1'],
            'vagas' => ['nullable', 'integer', 'min:1'],
            'classificam_total' => ['nullable', 'integer', Rule::in([4, 8, 12, 16])],
            'disputa_3_lugar' => ['required', 'boolean'],
            'pontuacao_1' => ['required', 'integer', 'min:0'],
            'pontuacao_2' => ['required', 'integer', 'min:0'],
            'pontuacao_3' => ['required', 'integer', 'min:0'],
            'pontuacao_4' => ['required', 'integer', 'min:0'],
            'pontuacao_classificados' => ['required', 'integer', 'min:0'],
            'pontuacao_fase_grupo' => ['required', 'integer', 'min:0'],
            'pontuacao_extra_1_grupo' => ['required', 'integer', 'min:0'],
            'sorteio_playoffs' => ['required', Rule::in(['aleatorio', 'primeiros_colocados', 'primeiros_com_segundos', 'ordem_classificacao', 'manual'])],
            'confrontos_playoffs' => ['required', Rule::in(['aleatorio', 'primeiros_contra_ultimos', 'manual'])],
            'sorteio_grupos' => ['required', Rule::in(['aleatorio', 'pela_ordem'])],
        ];
    }
}
