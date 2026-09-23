<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContratoTarifasTable extends Migration
{
    public function up()
    {
        Schema::create('contrato_tarifas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contrato_id');
            $table->enum('tipo', ['fixa','variavel','penalidade']);
            $table->string('descricao');
            $table->string('evento_disparo'); // entrada, saida, movimentacao, uso_guindaste, permanencia
            $table->decimal('valor_fixo', 14, 2)->nullable();
            $table->decimal('valor_unitario', 14, 2)->nullable();
            $table->string('unidade')->nullable(); // hora, container, ton, m3
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contrato_tarifas');
    }
}
