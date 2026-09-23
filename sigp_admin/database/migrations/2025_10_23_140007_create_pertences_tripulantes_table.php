<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertences_tripulantes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fal4_id');
            
            // Informações do tripulante
            $table->string('nome_tripulante');
            $table->string('funcao_bordo');
            $table->string('nacionalidade');
            $table->string('numero_documento');
            
            // Informações dos pertences
            $table->string('descricao_item');
            $table->integer('quantidade');
            $table->decimal('valor_declarado', 10, 2)->nullable();
            $table->string('moeda', 3)->default('USD');
            $table->boolean('para_uso_pessoal')->default(true);
            $table->boolean('para_venda')->default(false);
            $table->text('observacoes')->nullable();
            
            $table->timestamps();
            
            $table->foreign('fal4_id')->references('id')->on('fal4_pertences_tripulacao')->onDelete('cascade');
            $table->index(['fal4_id', 'nome_tripulante']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertences_tripulantes');
    }
};