<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imposto extends Model
{
    protected $table = 'impostos';
    
    protected $fillable = [
        'nome', 
        'sigla', 
        'taxa', 
        'tipo', 
        'motivo_isencao_codigo', 
        'motivo_isencao_descricao', 
        'ativo'
    ];

    protected $casts = [
        'taxa' => 'decimal:2',
        'ativo' => 'boolean',
    ];
}
