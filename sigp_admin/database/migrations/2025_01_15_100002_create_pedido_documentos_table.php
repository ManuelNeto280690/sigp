<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_documentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pedido_id');
            
            // Informações do documento
            $table->string('nome_original');
            $table->string('nome_arquivo'); // Nome do arquivo no storage
            $table->string('caminho_arquivo');
            $table->string('tipo_mime');
            $table->bigInteger('tamanho'); // Tamanho em bytes
            $table->string('extensao');
            
            // Categorização
            $table->enum('tipo_documento', [
                'fal1_declaracao_geral',
                'fal2_declaracao_carga', 
                'fal3_provisoes_bordo',
                'fal4_pertences_tripulacao',
                'fal5_documentos_tripulantes',
                'fal6_documentos_passageiros',
                'fal7_mercadorias_perigosas',
                'documento_adicional'
            ]);
            
            $table->text('descricao')->nullable();
            
            // Controle de upload
            $table->uuid('uploaded_by');
            $table->timestamp('uploaded_at');
            
            // Status do documento
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
            $table->uuid('aprovado_por')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->text('observacoes_aprovacao')->nullable();
            
            // Controle de versão
            $table->integer('versao')->default(1);
            $table->uuid('documento_anterior_id')->nullable(); // Referência ao documento que foi substituído
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Chaves estrangeiras
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('documento_anterior_id')->references('id')->on('pedido_documentos')->onDelete('set null');
            
            // Índices
            $table->index(['pedido_id', 'tipo_documento']);
            $table->index(['pedido_id', 'status']);
            $table->index('uploaded_by');
            $table->index('tipo_documento');
            $table->index(['is_active', 'status']);
            $table->index('versao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_documentos');
    }
};