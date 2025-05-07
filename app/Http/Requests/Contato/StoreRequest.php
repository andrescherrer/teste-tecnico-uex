<?php

namespace App\Http\Requests\Contato;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
        return  [

            'cpf' => [
                'required',
                'string',
                'size:11',
                Rule::unique('contatos', 'cpf'),
            ],

            'nome' => [
                'required',
            ],

            'telefone' => [
                'required',
            ],

            'numero' => [
                'required',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.unique' => 'O cpf já está em uso.',
            'cpf.required' => 'O cpf é obrigatório.',
            'cpf.string' => 'O cpf deve ser uma string.',
            'cpf.size' => 'O cpf deve ter exatamente 11 caracteres.',
            'nome.required' => 'O nome é obrigatório.',
            'telefone.required' => 'O telefone é obrigatório.',
            'numero.required' => 'O número é obrigatório.',
        ];
    }
}
