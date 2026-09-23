<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspecoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('embarcacao_id');
            $table->uuid('inspetor_id'); // Usuário inspetor
            $table->string('numero_inspecao')->unique();
            $table->enum('tipo_inspecao', ['isps', 'ambiental', 'cais', 'seguranca', 'sanitaria']);
            $table->timestamp('data_inspecao');
            $table->timestamp('hora_inicio')->nullable();
            $table->timestamp('hora_fim')->nullable();
            $table->json('checklist_itens'); // Itens verificados
            $table->json('nao_conformidades')->nullable(); // Problemas encontrados
            $table->text('observacoes')->nullable();
            $table->enum('resultado', ['aprovado', 'aprovado_com_restricoes', 'reprovado'])->default('aprovado');
            $table->text('restricoes')->nullable();
            $table->text('acoes_corretivas')->nullable();
            $table->timestamp('prazo_correcao')->nullable();
            $table->enum('status', ['agendada', 'em_andamento', 'concluida', 'cancelada'])->default('agendada');
            $table->json('documentos_verificados')->nullable();
            $table->json('evidencias')->nullable(); // Fotos, relatórios
            $table->uuid('aprovado_por')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('cascade');
            $table->foreign('inspetor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo_inspecao', 'resultado', 'status']);
            $table->index('data_inspecao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspecoes');
    }
};