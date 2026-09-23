<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FacturaItem extends Model
{
    use HasUuids;
    protected $table = 'factura_items';
    protected $fillable = [
        'factura_id', 'descricao', 'quantidade', 'preco_unitario', 'subtotal',
        'taxa_iva_id', 'valor_iva', 'motivo_isencao_codigo', 'sujeito_retencao'
    ];

    public function factura(){ return $this->belongsTo(Factura::class,'factura_id'); }
    public function taxaIva(){ return $this->belongsTo(Imposto::class,'taxa_iva_id'); }
}
