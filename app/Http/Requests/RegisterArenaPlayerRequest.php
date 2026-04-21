<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterArenaPlayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => 'sometimes|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'gender'   => 'nullable|string|in:male,female,other',
            'level'    => 'nullable|string|max:100',
            'city'     => 'nullable|string|max:255',
        ];
    }
}
