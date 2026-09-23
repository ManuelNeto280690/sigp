<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentos_terminais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('terminal_id');
            $table->uuid('embarcacao_id')->nullable();
            $table->uuid('user_id'); // Usuário que registrou
            $table->string('numero_movimento')->unique();
            $table->enum('tipo_movimento', ['atracacao', 'desatracacao', 'mudanca_berco', 'manutencao']);
            $table->string('berco')->nullable();
            $table->timestamp('data_inicio');
            $table->timestamp('data_fim')->nullable();
            $table->text('descricao')->nullable();
            $table->json('recursos_utilizados')->nullable(); // Equipamentos, pessoal
            $table->enum('status', ['planejado', 'em_andamento', 'concluido', 'cancelado'])->default('planejado');
            $table->text('observacoes')->nullable();
            $table->uuid('autorizado_por')->nullable();
            $table->timestamp('autorizado_em')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('terminal_id')->references('id')->on('terminais')->onDelete('cascade');
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('autorizado_por')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo_movimento', 'status']);
            $table->index(['data_inicio', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentos_terminais');
    }
};