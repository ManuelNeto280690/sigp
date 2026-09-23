<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provisoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fal3_id');
            
            // Informações da provisão
            $table->enum('categoria', ['alimentos', 'bebidas', 'combustivel', 'lubrificantes', 'medicamentos', 'outros']);
            $table->string('descricao');
            $table->decimal('quantidade', 10, 3);
            $table->string('unidade_medida');
            $table->string('origem')->nullable();
            $table->boolean('para_consumo_bordo')->default(true);
            $table->boolean('para_venda')->default(false);
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            
            $table->foreign('fal3_id')->references('id')->on('fal3_provisoes_bordo')->onDelete('cascade');
            $table->index(['fal3_id', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisoes');
    }
};