<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berco extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bercos';

    protected $fillable = [
        'terminal_id',
        'nome',
        'calado_maximo',
        'comprimento_maximo',
        'status',
    ];

    protected $casts = [
        'calado_maximo' => 'decimal:2',
        'comprimento_maximo' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'disponivel',
    ];

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }
}
