<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimentoTerminal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'movimentos_terminais';

    protected $fillable = [
        'terminal_id',
        'entrada_saida_id',
        'tipo',
        'data_movimento',
        'observacoes',
        'status',
        'is_active'
    ];

    protected $casts = [
        'data_movimento' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function entradaSaida(): BelongsTo
    {
        return $this->belongsTo(EntradaSaidaEmbarcacao::class, 'entrada_saida_id');
    }
}