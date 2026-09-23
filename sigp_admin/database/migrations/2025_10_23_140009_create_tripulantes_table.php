<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tripulantes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fal5_id');
            
            // Informações pessoais
            $table->string('nome_completo');
            $table->string('nacionalidade');
            $table->date('data_nascimento')->nullable();
            $table->string('local_nascimento')->nullable();
            $table->enum('sexo', ['M', 'F']);
            
            // Documentação
            $table->string('tipo_documento'); // passaporte, carteira marítima, etc.
            $table->string('numero_documento');
            $table->date('data_emissao_documento')->nullable();
            $table->date('data_validade_documento')->nullable();
            $table->string('pais_emissao_documento');
            
            // Informações profissionais
            $table->string('funcao_bordo');
            $table->string('categoria_profissional')->nullable();
            $table->string('numero_carteira_maritima')->nullable();
            $table->date('data_embarque')->nullable();
            $table->string('porto_embarque')->nullable();
            
            // Saúde
            $table->boolean('possui_certificado_saude')->default(false);
            $table->date('data_certificado_saude')->nullable();
            $table->boolean('vacinacao_febre_amarela')->default(false);
            $table->date('data_vacinacao_febre_amarela')->nullable();
            
            $table->timestamps();
            
            $table->foreign('fal5_id')->references('id')->on('fal5_lista_tripulantes')->onDelete('cascade');
            $table->index(['fal5_id', 'funcao_bordo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tripulantes');
    }
};