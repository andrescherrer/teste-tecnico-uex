<?php

namespace App\Http\Requests\Contato;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException('Você precisa estar logado para realizar esta ação.');
    }
 
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