<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContratosTable extends Migration
{
    public function up()
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('concessionaria_id');
            $table->string('titulo')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->enum('modo_faturacao', ['por_operacao','consolidado_por_navio'])->default('por_operacao');
            $table->enum('status', ['ativo','inativo','cancelado'])->default('ativo');
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('concessionaria_id')->references('id')->on('concessionarias')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contratos');
    }
}
