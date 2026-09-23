<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fal3_provisoes_bordo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_id');
            
            // Informações gerais
            $table->text('observacoes_gerais')->nullable();
            $table->boolean('possui_provisoes_excesso')->default(false);
            $table->text('justificativa_excesso')->nullable();
            
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
        Schema::dropIfExists('fal3_provisoes_bordo');
    }
};