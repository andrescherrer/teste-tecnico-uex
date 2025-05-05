<?php

namespace App\Http\Requests\Contato;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'nome' => 'nullable|string',
            'cpf' => 'nullable|string',
            'telefone' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.min' => 'O número de itens por página deve ser maior que 0.',
        ];
    }
}