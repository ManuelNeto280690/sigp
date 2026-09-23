<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturasTable extends Migration
{
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('concessionaria_id');
            $table->uuid('contrato_id')->nullable();
            $table->uuid('entrada_saida_id')->nullable();
            $table->uuid('movimento_carga_id')->nullable();
            $table->string('numero')->unique();
            $table->decimal('valor_total', 14, 2)->default(0);
            $table->enum('status',['emitida','paga','cancelada'])->default('emitida');
            $table->json('metadados')->nullable();
            $table->timestamps();

            $table->foreign('concessionaria_id')->references('id')->on('concessionarias')->onDelete('cascade');
            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('facturas');
    }
}
