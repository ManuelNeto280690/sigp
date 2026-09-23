<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passageiros', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fal6_id');
            
            // Informações pessoais
            $table->string('nome_completo');
            $table->string('nacionalidade');
            $table->date('data_nascimento')->nullable();
            $table->string('local_nascimento')->nullable();
            $table->enum('sexo', ['M', 'F']);
            
            // Documentação
            $table->string('tipo_documento');
            $table->string('numero_documento');
            $table->date('data_emissao_documento')->nullable();
            $table->date('data_validade_documento')->nullable();
            $table->string('pais_emissao_documento');
            
            // Informações da viagem
            $table->string('porto_embarque');
            $table->string('porto_desembarque');
            $table->boolean('em_transito')->default(false);
            $table->string('endereco_destino')->nullable();
            
            // Saúde
            $table->boolean('possui_certificado_saude')->default(false);
            $table->date('data_certificado_saude')->nullable();
            $table->boolean('vacinacao_febre_amarela')->default(false);
            $table->date('data_vacinacao_febre_amarela')->nullable();
            
            $table->timestamps();
            
            $table->foreign('fal6_id')->references('id')->on('fal6_lista_passageiros')->onDelete('cascade');
            $table->index(['fal6_id', 'nacionalidade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passageiros');
    }
};