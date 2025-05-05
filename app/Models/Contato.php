<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contato extends Model
{
    use HasFactory;
    
    protected $table = 'contatos';

    protected $fillable = [
        'user_id',
        'nome',
        'cpf',
        'telefone',
        'logradouro',
        'numero',
        'bairro',
        'complemento',
        'localidade',
        'uf',
        'estado',
        'cep',
        'latitude',
        'longitude',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
