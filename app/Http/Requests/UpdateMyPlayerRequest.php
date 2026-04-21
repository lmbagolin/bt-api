<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMyPlayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->player !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'nickname'  => ['nullable', 'string', 'max:255'],
            'gender'    => ['nullable', 'string', 'in:male,female,other'],
            'level'     => ['nullable', 'string', 'max:100'],
            'city'      => ['nullable', 'string', 'max:255'],
            'whatsapp'  => ['nullable', 'string', 'max:20'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
        ];
    }
}
