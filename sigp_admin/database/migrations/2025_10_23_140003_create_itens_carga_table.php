<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_carga', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fal2_id'); // Referência ao FAL2
            
            // Informações do item
            $table->string('descricao');
            $table->string('codigo_hs')->nullable(); // Código do Sistema Harmonizado
            $table->decimal('peso', 10, 3); // em toneladas
            $table->integer('quantidade');
            $table->string('unidade_medida'); // ton, m³, unidades, etc.
            $table->string('embalagem')->nullable();
            $table->text('marcas_numeros')->nullable();
            
            // Classificação
            $table->boolean('carga_perigosa')->default(false);
            $table->string('classe_imdg')->nullable(); // Classe IMDG se perigosa
            $table->string('numero_onu')->nullable(); // Número ONU se perigosa
            $table->boolean('carga_refrigerada')->default(false);
            $table->decimal('temperatura_requerida', 5, 2)->nullable(); // em Celsius
            
            // Origem/Destino
            $table->string('porto_origem')->nullable();
            $table->string('porto_destino')->nullable();
            $table->string('consignatario')->nullable();
            $table->string('embarcador')->nullable();
            
            $table->timestamps();
            
            $table->foreign('fal2_id')->references('id')->on('fal2_declaracao_carga')->onDelete('cascade');
            $table->index(['fal2_id', 'carga_perigosa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_carga');
    }
};