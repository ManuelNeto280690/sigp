<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Desabilitar verificações de chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Dropar tabelas relacionadas se existirem
        Schema::dropIfExists('pedido_documentos');
        Schema::dropIfExists('pedido_status_history');
        Schema::dropIfExists('fal1_declaracao_geral');
        Schema::dropIfExists('fal2_declaracao_carga');
        Schema::dropIfExists('fal3_provisoes_bordo');
        Schema::dropIfExists('fal4_pertences_tripulacao');
        Schema::dropIfExists('fal5_lista_tripulantes');
        Schema::dropIfExists('fal6_lista_passageiros');
        Schema::dropIfExists('fal7_mercadorias_perigosas');
        Schema::dropIfExists('provisoes');
        Schema::dropIfExists('pertences_tripulantes');
        Schema::dropIfExists('tripulantes');
        Schema::dropIfExists('passageiros');
        Schema::dropIfExists('itens_perigosos');
        Schema::dropIfExists('itens_carga');
        Schema::dropIfExists('pedidos');
        
        // Reabilitar verificações de chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        Schema::create('pedidos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Informações básicas do navio
            $table->string('nome_navio');
            $table->string('numero_imo');
            $table->string('indicativo_chamada');
            $table->string('viagem_numero');
            $table->string('bandeira_navio');
            
            // Datas e horários
            $table->date('data_chegada');
            $table->time('hora_chegada');
            $table->date('data_partida');
            $table->time('hora_partida');
            
            // Informações do agente
            $table->string('nome_agente');
            $table->string('contato_agente');
            
            // Tripulação e passageiros
            $table->integer('numero_tripulantes')->default(0);
            $table->integer('numero_passageiros')->default(0);
            
            // Observações
            $table->text('observacoes_operacao')->nullable();
            
            // Arquivos/Documentos (JSON para armazenar paths dos arquivos)
            $table->json('certificados_navio')->nullable(); // Array de paths dos arquivos
            $table->json('declaracoes_cargas')->nullable(); // Array de paths dos arquivos
            $table->json('declaracao_provisoes_bordo')->nullable(); // Path do arquivo
            $table->json('declaracao_pertences_tripulacao')->nullable(); // Path do arquivo
            $table->json('documentos_tripulantes')->nullable(); // Array de objetos {nome, documento_path}
            $table->json('documentos_passageiros')->nullable(); // Array de objetos {nome, documento_path}
            $table->json('declaracao_mercadorias_perigosas')->nullable(); // Path do arquivo
            
            // Status e controle
            $table->enum('status', ['pendente', 'em_analise', 'aprovado', 'rejeitado', 'cancelado'])->default('pendente');
            $table->uuid('decidido_por')->nullable(); // ID do usuário que tomou a decisão
            
            // Relacionamentos
            $table->uuid('user_id'); // Usuário que criou o pedido
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index(['status']);
            $table->index(['data_chegada']);
            $table->index(['nome_navio']);
            $table->index(['numero_imo']);
            
            // Chaves estrangeiras
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('decidido_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};