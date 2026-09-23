<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Contrato extends Model
{
    use HasUuids;
    protected $table = 'contratos';
    protected $fillable = ['concessionaria_id','titulo','data_inicio','data_fim','modo_faturacao','status','observacoes'];
    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    public function concessionaria(){ 
        return $this->belongsTo(Concessionaria::class,'concessionaria_id'); 
    }
    public function tarifas(){ 
        return $this->hasMany(ContratoTarifa::class,'contrato_id'); 
    }

    public function getEventoOptions(): array
    {
        $titulo = mb_strtolower($this->titulo);
        $options = [];

        if (strpos($titulo, 'navio') !== false) {
            $options = array_merge($options, ['Entrada', 'Saída', 'Atracação', 'Desatracação', 'Estadia', 'Escala']);
        }

        if (strpos($titulo, 'carga') !== false) {
            $options = array_merge($options, ['Carga', 'Descarga', 'Movimentação interna', 'Transbordo', 'Pesagem', 'Inspeção']);
        }

        if (strpos($titulo, 'operação') !== false || strpos($titulo, 'operacao') !== false || strpos($titulo, 'equipamento') !== false) {
            $options = array_merge($options, ['Guindaste', 'Uso de guindaste', 'Uso de empilhadora', 'Hora máquina', 'Estadia de equipamento', 'Horas de operador']);
        }

        return array_values(array_unique($options));
    }
}
