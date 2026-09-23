<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Provisao extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'provisoes';

    protected $fillable = [
        'fal3_id',
        'tipo',
        'descricao',
        'quantidade',
        'unidade',
        'observacoes'
    ];

    protected $casts = [
        'quantidade' => 'decimal:2'
    ];

    public function fal3(): BelongsTo
    {
        return $this->belongsTo(Fal3ProvisoesBordo::class, 'fal3_id');
    }
}