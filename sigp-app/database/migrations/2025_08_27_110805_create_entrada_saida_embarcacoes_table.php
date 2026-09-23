<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrada_saida_embarcacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('embarcacao_id');
            $table->uuid('terminal_id')->nullable();
            $table->uuid('user_id'); // Usuário que registrou
            $table->string('numero_movimento')->unique();
            $table->enum('tipo_movimento', ['entrada', 'saida']);
            $table->timestamp('data_programada');
            $table->timestamp('data_efetiva')->nullable();
            $table->string('berco')->nullable();
            $table->string('agente_maritimo')->nullable();
            $table->string('capitania_origem')->nullable();
            $table->string('capitania_destino')->nullable();
            $table->text('motivo')->nullable();
            $table->json('documentos_apresentados')->nullable();
            $table->enum('status', ['programado', 'autorizado', 'em_andamento', 'concluido', 'cancelado'])->default('programado');
            $table->text('observacoes')->nullable();
            $table->uuid('autorizado_por')->nullable();
            $table->timestamp('autorizado_em')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('cascade');
            $table->foreign('terminal_id')->references('id')->on('terminais')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('autorizado_por')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo_movimento', 'status']);
            $table->index(['data_programada', 'data_efetiva']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrada_saida_embarcacoes');
    }
};
