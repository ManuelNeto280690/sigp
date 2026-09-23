<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_status_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_id');
            
            // Informações da mudança de status
            $table->string('status_anterior')->nullable();
            $table->string('status_novo');
            $table->text('motivo')->nullable();
            $table->text('observacoes')->nullable();
            
            // Quem fez a mudança
            $table->uuid('alterado_por');
            $table->timestamp('alterado_em');
            
            // Dados adicionais
            $table->json('dados_adicionais')->nullable(); // Para armazenar informações extras
            
            $table->timestamps();
            
            // Chaves estrangeiras
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('alterado_por')->references('id')->on('users')->onDelete('cascade');
            
            // Índices
            $table->index(['pedido_id', 'alterado_em']);
            $table->index('status_novo');
            $table->index('alterado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_status_history');
    }
};