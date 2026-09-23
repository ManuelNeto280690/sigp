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
        
        // Dropar tabelas dependentes primeiro
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
        
        // Dropar a tabela principal
        Schema::dropIfExists('pedidos');
        
        // Reabilitar verificações de chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        Schema::create('pedidos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relacionamentos básicos
            $table->uuid('user_id'); // Solicitante
            $table->uuid('embarcacao_id'); // Embarcação (obrigatório)
            $table->uuid('terminal_id')->nullable(); // Terminal de destino
            $table->uuid('concessionaria_id')->nullable(); // Concessionária responsável
            
            // Identificação do pedido
            $table->string('numero_pedido')->unique();
            $table->string('numero_protocolo')->unique()->nullable();
            
            // Tipo de operação
            $table->string('tipo');
            
            // Informações básicas do pedido
            $table->text('titulo');
            $table->text('descricao');
            $table->text('justificativa')->nullable();
            $table->text('observacoes')->nullable();
            
            // Prioridade e status do pedido
            $table->enum('prioridade', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->enum('status', ['pendente', 'em_analise', 'aprovado', 'rejeitado', 'cancelado'])->default('pendente');
            
            // Datas importantes
            $table->timestamp('data_solicitacao')->default(now());
            $table->timestamp('data_necessaria')->nullable();
            $table->timestamp('data_vencimento')->nullable();
            
            // Datas da operação (ETA/ETD/ATA/ATD)
            $table->timestamp('eta_prevista')->nullable(); // Estimated Time of Arrival
            $table->timestamp('etd_prevista')->nullable(); // Estimated Time of Departure
            $table->timestamp('ata_real')->nullable(); // Actual Time of Arrival
            $table->timestamp('atd_real')->nullable(); // Actual Time of Departure
            
            // Informações operacionais
            $table->string('berco')->nullable();
            $table->string('agente_maritimo')->nullable();
            $table->string('capitania_origem')->nullable();
            $table->string('capitania_destino')->nullable();
            
            // Controle de aprovação do pedido
            $table->uuid('analisado_por')->nullable();
            $table->timestamp('analisado_em')->nullable();
            $table->text('motivo_rejeicao')->nullable();
            $table->uuid('aprovado_por')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            
            // Controle dos formulários FAL
            $table->json('status_fals')->nullable(); // Status de cada FAL
            $table->decimal('progresso_fals', 5, 2)->default(0.00); // Progresso em %
            $table->boolean('fals_obrigatorios_completos')->default(false);
            $table->boolean('todos_fals_aprovados')->default(false);
            
            // Informações de carga (se aplicável)
            $table->decimal('quantidade_carga', 12, 3)->nullable();
            $table->string('unidade_medida_carga')->nullable();
            $table->string('tipo_carga')->nullable();
            $table->string('origem_carga')->nullable();
            $table->string('destino_carga')->nullable();
            
            // Campos de controle
            $table->boolean('is_active')->default(true);
            $table->boolean('is_urgent')->default(false);
            $table->boolean('requires_special_authorization')->default(false);
            
            // Campos para notificações e comunicação
            $table->json('historico_status')->nullable(); // Histórico de mudanças de status
            $table->json('comunicacoes')->nullable(); // Comunicações relacionadas ao pedido
            $table->timestamp('ultima_atualizacao_fals')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Chaves estrangeiras
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('cascade');
            $table->foreign('terminal_id')->references('id')->on('terminais')->onDelete('set null');
            $table->foreign('concessionaria_id')->references('id')->on('concessionarias')->onDelete('set null');
            $table->foreign('analisado_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
            
            // Índices para performance
            $table->index(['tipo', 'status', 'prioridade']);
            $table->index('numero_protocolo');
            $table->index(['eta_prevista', 'etd_prevista']);
            $table->index(['embarcacao_id', 'status']);
            $table->index(['terminal_id', 'status']);
            $table->index(['concessionaria_id', 'status']);
            $table->index('data_solicitacao');
            $table->index('progresso_fals');
            $table->index(['is_active', 'status']);
            $table->index(['is_urgent', 'prioridade']);
            $table->index('todos_fals_aprovados');
            $table->index('data_necessaria');
        });
    }

    public function down(): void
    {
        // Desabilitar verificações de chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Dropar tabelas dependentes primeiro
        Schema::dropIfExists('pedido_documentos');
        Schema::dropIfExists('pedido_status_history');
        Schema::dropIfExists('fal1_declaracao_geral');
        Schema::dropIfExists('fal2_declaracao_carga');
        Schema::dropIfExists('fal3_provisoes_bordo');
        Schema::dropIfExists('fal4_pertences_tripulacao');
        Schema::dropIfExists('fal5_lista_tripulantes');
        Schema::dropIfExists('fal6_lista_passageiros');
        Schema::dropIfExists('fal7_mercadorias_perigosas');
        
        // Dropar a tabela principal
        Schema::dropIfExists('pedidos');
        
        // Reabilitar verificações de chaves estrangeiras
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};