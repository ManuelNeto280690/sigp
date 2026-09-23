<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fal2_declaracao_carga', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_id');
            
            // Informações gerais da carga
            $table->enum('tipo_operacao', ['embarque', 'desembarque', 'transbordo', 'transito']);
            $table->decimal('peso_total_carga', 12, 3); // em toneladas
            $table->integer('numero_volumes')->nullable();
            $table->text('descricao_geral_carga');
            
            // Informações de manuseio
            $table->json('equipamentos_necessarios')->nullable(); // guindastes, empilhadeiras, etc.
            $table->text('instrucoes_especiais')->nullable();
            $table->boolean('carga_perigosa')->default(false);
            $table->boolean('carga_refrigerada')->default(false);
            $table->boolean('carga_fragil')->default(false);
            
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
        Schema::dropIfExists('fal2_declaracao_carga');
    }
};