<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_perigosos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fal7_id');
            
            // Identificação da mercadoria perigosa
            $table->string('numero_onu'); // Número ONU
            $table->string('nome_tecnico');
            $table->string('classe_imdg'); // Classe IMDG
            $table->string('grupo_embalagem')->nullable();
            $table->string('numero_identificacao_risco')->nullable();
            
            // Quantidade e embalagem
            $table->decimal('quantidade', 10, 3);
            $table->string('unidade_medida');
            $table->string('tipo_embalagem');
            $table->integer('numero_volumes');
            $table->text('marcacao_rotulagem');
            
            // Localização e manuseio
            $table->string('localizacao_bordo');
            $table->text('instrucoes_manuseio');
            $table->text('equipamentos_protecao');
            $table->text('procedimentos_emergencia');
            
            // Documentação
            $table->boolean('possui_certificado_imdg')->default(false);
            $table->string('numero_certificado_imdg')->nullable();
            $table->date('validade_certificado')->nullable();
            
            $table->timestamps();
            
            $table->foreign('fal7_id')->references('id')->on('fal7_mercadorias_perigosas')->onDelete('cascade');
            $table->index(['fal7_id', 'classe_imdg']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_perigosos');
    }
};