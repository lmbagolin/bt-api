<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeagueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'data_inicio' => ['required', 'date'],
            'data_prevista_termino' => ['nullable', 'date', 'after:data_inicio'],
            'numero_etapas' => ['required', 'integer', 'min:1'],
            'descricao' => ['nullable', 'string'],
            'premiacao' => ['nullable', 'string'],
            'nivel'  => ['required', 'string', 'max:255'],
            'genero' => ['required', 'in:masculino,feminino,misto'],
        ];
    }
}
