<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoStatusHistory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pedido_status_history';

    protected $fillable = [
        'pedido_id',
        'status_anterior',
        'status_novo',
        'motivo',
        'observacoes',
        'alterado_por',
        'alterado_em',
        'dados_adicionais'
    ];

    protected $casts = [
        'alterado_em' => 'datetime',
        'dados_adicionais' => 'array'
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function alteradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alterado_por');
    }
}