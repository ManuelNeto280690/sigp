<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimentosCargaTable extends Migration
{
    public function up()
    {
        Schema::create('movimentos_carga', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entrada_saida_id');
            $table->uuid('terminal_id')->nullable();
            $table->uuid('berco_id')->nullable();
            $table->uuid('guindaste_id')->nullable();
            $table->enum('tipo_operacao', ['descarga','carga','transbordo']);
            $table->string('tipo_produto'); // container, graneis_solidos, ...
            $table->decimal('quantidade', 14, 4); // n containers ou toneladas
            $table->datetime('inicio');
            $table->datetime('fim')->nullable();
            $table->uuid('operador_id')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('entrada_saida_id')->references('id')->on('entrada_saida_embarcacoes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimentos_carga');
    }
}
