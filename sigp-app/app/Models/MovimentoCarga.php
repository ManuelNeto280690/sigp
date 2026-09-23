<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MovimentoCarga extends Model
{
    use HasUuids;
    protected $table = 'movimentos_carga';
    protected $fillable = ['entrada_saida_id','terminal_id','berco_id','guindaste_id','tipo_operacao','tipo_produto','quantidade','unidade','inicio','fim','operador_id','observacoes'];

    public function entradaSaida(){ return $this->belongsTo(EntradaSaidaEmbarcacao::class,'entrada_saida_id'); }
}
