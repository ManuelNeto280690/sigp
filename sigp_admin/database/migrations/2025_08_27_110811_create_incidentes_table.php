<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidentes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // Usuário que reportou
            $table->uuid('embarcacao_id')->nullable();
            $table->uuid('terminal_id')->nullable();
            $table->string('numero_incidente')->unique();
            $table->string('titulo');
            $table->text('descricao');
            $table->enum('tipo', ['acidente', 'avaria', 'poluicao', 'seguranca', 'operacional', 'outros']);
            $table->enum('gravidade', ['baixa', 'media', 'alta', 'critica'])->default('media');
            $table->timestamp('data_ocorrencia');
            $table->string('local_ocorrencia');
            $table->json('pessoas_envolvidas')->nullable();
            $table->text('causas_identificadas')->nullable();
            $table->text('acoes_imediatas')->nullable();
            $table->text('acoes_corretivas')->nullable();
            $table->enum('status', ['aberto', 'investigando', 'resolvido', 'fechado'])->default('aberto');
            $table->uuid('responsavel_investigacao')->nullable();
            $table->timestamp('data_fechamento')->nullable();
            $table->boolean('requer_notificacao_autoridades')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('set null');
            $table->foreign('terminal_id')->references('id')->on('terminais')->onDelete('set null');
            $table->foreign('responsavel_investigacao')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo', 'gravidade', 'status']);
            $table->index('data_ocorrencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};