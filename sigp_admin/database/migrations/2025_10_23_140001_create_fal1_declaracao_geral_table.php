<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fal1_declaracao_geral', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_id'); // Referência ao pedido/escala
            
            // Informações da embarcação
            $table->string('nome_embarcacao');
            $table->string('imo');
            $table->string('mmsi');
            $table->string('indicativo_chamada');
            $table->string('bandeira');
            $table->string('porto_registo');
            $table->decimal('arqueacao_bruta', 10, 2);
            $table->decimal('arqueacao_liquida', 10, 2);
            $table->string('nome_armador');
            $table->string('endereco_armador');
            
            // Informações da viagem
            $table->string('porto_procedencia');
            $table->string('porto_destino');
            $table->timestamp('data_chegada_prevista')->nullable();
            $table->timestamp('data_saida_prevista')->nullable();
            $table->string('local_atracacao');
            $table->text('proposito_escala');
            $table->text('carga_resumo')->nullable();
            
            // Informações da tripulação
            $table->integer('numero_tripulantes');
            $table->integer('numero_passageiros')->default(0);
            
            // Declarações
            $table->boolean('possui_substancias_perigosas')->default(false);
            $table->boolean('possui_poluentes')->default(false);
            $table->boolean('possui_drogas_narcoticos')->default(false);
            $table->boolean('possui_armas_municoes')->default(false);
            $table->boolean('possui_animais_vivos')->default(false);
            
            // Status e aprovação
            $table->enum('status', ['rascunho', 'submetido', 'aprovado', 'rejeitado'])->default('rascunho');
            $table->uuid('aprovado_por')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->text('observacoes_aprovacao')->nullable();
            
            $table->timestamps();
            
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
            $table->index(['pedido_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fal1_declaracao_geral');
    }
};