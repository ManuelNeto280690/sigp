<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declaracoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('embarcacao_id');
            $table->uuid('user_id'); // Usuário que criou a declaração
            $table->string('numero_protocolo')->unique();
            $table->enum('tipo', ['chegada', 'saida', 'movimentacao_carga', 'abastecimento', 'reparos']);
            $table->json('dados_declaracao'); // Dados específicos da declaração em JSON
            $table->enum('status', ['pendente', 'em_analise', 'aprovada', 'rejeitada'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->text('motivo_rejeicao')->nullable();
            $table->uuid('aprovado_por')->nullable(); // ID do usuário que aprovou
            $table->timestamp('aprovado_em')->nullable();
            $table->timestamp('data_vencimento')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo', 'status']);
            $table->index('numero_protocolo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declaracoes');
    }
};