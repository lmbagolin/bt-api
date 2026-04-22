<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeagueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['sometimes', 'string', 'max:255'],
            'data_inicio' => ['sometimes', 'date'],
            'data_prevista_termino' => ['nullable', 'date', 'after:data_inicio'],
            'numero_etapas' => ['sometimes', 'integer', 'min:1'],
            'descricao' => ['nullable', 'string'],
            'premiacao' => ['nullable', 'string'],
            'nivel' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
