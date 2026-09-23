<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PedidoDocumento extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pedido_documentos';

    protected $fillable = [
        'pedido_id',
        'nome_original',
        'nome_arquivo',
        'caminho_arquivo',
        'tipo_mime',
        'tamanho',
        'extensao',
        'tipo_documento',
        'descricao',
        'uploaded_by',
        'uploaded_at',
        'status',
        'aprovado_por',
        'aprovado_em',
        'observacoes_aprovacao',
        'versao',
        'documento_anterior_id',
        'is_active'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'aprovado_em' => 'datetime',
        'tamanho' => 'integer',
        'versao' => 'integer',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'status' => 'pendente',
        'versao' => 1,
        'is_active' => true
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    public function documentoAnterior(): BelongsTo
    {
        return $this->belongsTo(self::class, 'documento_anterior_id');
    }

    public function getUrlDownloadAttribute(): string
    {
        return route('pedidos.documentos.download', $this->id);
    }

    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getTipoDocumentoLabelAttribute(): string
    {
        $labels = [
            'fal1_declaracao_geral' => 'FAL 1 - Declaração Geral',
            'fal2_declaracao_carga' => 'FAL 2 - Declaração de Carga',
            'fal3_provisoes_bordo' => 'FAL 3 - Provisões de Bordo',
            'fal4_pertences_tripulacao' => 'FAL 4 - Pertences da Tripulação',
            'fal5_documentos_tripulantes' => 'FAL 5 - Documentos de Tripulantes',
            'fal6_documentos_passageiros' => 'FAL 6 - Documentos de Passageiros',
            'fal7_mercadorias_perigosas' => 'FAL 7 - Mercadorias Perigosas',
            'documento_adicional' => 'Documento Adicional'
        ];

        return $labels[$this->tipo_documento] ?? $this->tipo_documento;
    }

    public function aprovar(User $user, string $observacoes = null): void
    {
        $this->update([
            'status' => 'aprovado',
            'aprovado_por' => $user->id,
            'aprovado_em' => now(),
            'observacoes_aprovacao' => $observacoes
        ]);
    }

    public function rejeitar(User $user, string $observacoes): void
    {
        $this->update([
            'status' => 'rejeitado',
            'aprovado_por' => $user->id,
            'aprovado_em' => now(),
            'observacoes_aprovacao' => $observacoes
        ]);
    }

    public function existeArquivo(): bool
    {
        return Storage::exists($this->caminho_arquivo);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($documento) {
            // Remover arquivo físico quando o documento for deletado
            if ($documento->existeArquivo()) {
                Storage::delete($documento->caminho_arquivo);
            }
        });
    }
}