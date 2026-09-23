<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ContratoTarifa extends Model
{
    use HasUuids;
    protected $table = 'contrato_tarifas';
    protected $fillable = ['contrato_id','tipo','descricao','evento_disparo','valor_fixo','valor_unitario','unidade','is_active'];

    public function contrato(){ return $this->belongsTo(Contrato::class,'contrato_id'); }
}
