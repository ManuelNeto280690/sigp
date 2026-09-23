<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Factura extends Model
{
    use HasUuids;
    protected $table = 'facturas';
    protected $fillable = [
        'concessionaria_id','contrato_id','entrada_saida_id','movimento_carga_id',
        'numero', 'valor_total', 'status', 'metadados',
        'tipo_documento', 'serie', 'numero_sequencial', 'hash', 'hash_anterior',
        'hash_control', 'total_s_iva', 'total_iva', 'total_imposto_selo',
        'total_retencao', 'total_a_pagar', 'agt_status'
    ];
    protected $casts = ['metadados' => 'array'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($factura) {
            // Regras AGT na criação
            $factura->serie = $factura->serie ?? \App\Models\Configuracao::obter('faturacao_serie', date('Y'));
            $factura->tipo_documento = $factura->tipo_documento ?? 'FT';
            $factura->status = $factura->status ?? 'emitida';

            // Encontrar a última fatura desta série e tipo
            $ultima = static::where('serie', $factura->serie)
                            ->where('tipo_documento', $factura->tipo_documento)
                            ->orderBy('numero_sequencial', 'desc')
                            ->first();

            $factura->numero_sequencial = $ultima ? $ultima->numero_sequencial + 1 : 1;
            $factura->numero = "{$factura->tipo_documento} {$factura->serie}/{$factura->numero_sequencial}";
            $factura->hash_anterior = $ultima ? $ultima->hash : "";
            
            // Assinar a fatura (calcula-se o total previamente ou usa-se o valor_total)
            try {
                $factura->hash = \App\Helpers\AgtSignatureHelper::signInvoice(
                    now()->format('Y-m-d'),
                    now()->format('Y-m-d\TH:i:s'),
                    $factura->numero,
                    $factura->total_a_pagar > 0 ? $factura->total_a_pagar : $factura->valor_total,
                    $factura->hash_anterior
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erro Hash AGT: " . $e->getMessage());
                // Em produção, devíamos abortar. Aqui permitimos falhar se a chave não existir
            }
        });

        static::updating(function ($factura) {
            // Se já foi emitida, não se pode alterar valores financeiros nem referências AGT
            // Pode alterar status para 'paga' ou 'agt_status', mas não cancelar ou alterar hash
            $restrictedFields = [
                'valor_total', 'total_s_iva', 'total_iva', 'total_imposto_selo', 
                'total_retencao', 'total_a_pagar', 'hash', 'hash_anterior', 
                'numero', 'serie', 'tipo_documento'
            ];

            if ($factura->getOriginal('status') === 'emitida' || $factura->getOriginal('status') === 'paga') {
                foreach ($restrictedFields as $field) {
                    if ($factura->isDirty($field)) {
                        throw new \Exception("A lei da AGT proíbe a alteração do campo {$field} após a emissão da fatura. Emita uma Nota de Crédito.");
                    }
                }
            }
        });

        static::deleting(function ($factura) {
            throw new \Exception("A lei da AGT proíbe a eliminação de faturas. Deve emitir uma Nota de Crédito.");
        });
    }

    public function items(){ return $this->hasMany(FacturaItem::class,'factura_id'); }
    public function contrato(){ return $this->belongsTo(Contrato::class,'contrato_id'); }
    public function entradaSaida(){ return $this->belongsTo(EntradaSaidaEmbarcacao::class,'entrada_saida_id'); }
    public function concessionaria(){ return $this->belongsTo(Concessionaria::class,'concessionaria_id'); }
    public function movimentoCarga(){ return $this->belongsTo(MovimentoCarga::class,'movimento_carga_id'); }
}
