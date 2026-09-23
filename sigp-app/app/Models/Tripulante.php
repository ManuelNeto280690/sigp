<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tripulante extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tripulantes';

    protected $fillable = [
        'fal5_id',
        'nome',
        'cargo',
        'nacionalidade',
        'documento_tipo',
        'documento_numero',
        'data_nascimento',
        'local_nascimento',
        'observacoes'
    ];

    protected $casts = [
        'data_nascimento' => 'date'
    ];

    public function fal5(): BelongsTo
    {
        return $this->belongsTo(Fal5ListaTripulantes::class, 'fal5_id');
    }
}