<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identificação da escala
            $table->string('numero_escala')->unique();
            
            // Relacionamentos
            $table->uuid('embarcacao_id');
            
            // Tipo de operação
            $table->enum('tipo_operacao', ['atracacao', 'desatracacao', 'transito', 'fundeio'])->default('atracacao');
            
            // Informações operacionais
            $table->string('berco')->nullable();
            $table->text('descricao')->nullable();
            
            // Datas da escala (ETA/ETD/ATA/ATD)
            $table->timestamp('eta_previsto')->nullable();
            $table->timestamp('etd_previsto')->nullable();
            $table->timestamp('ata_real')->nullable();
            $table->timestamp('atd_real')->nullable();
            
            // Status da escala
            $table->enum('status', ['planejada', 'confirmada', 'atracada', 'operando', 'desatracada', 'cancelada'])->default('planejada');
            
            // Controle dos formulários FAL
            $table->decimal('progresso_fals', 5, 2)->default(0.00);
            $table->boolean('fals_obrigatorios_aprovados')->default(false);
            
            // Observações
            $table->text('observacoes')->nullable();

            
            // Campos de auditoria
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            
            // Timestamps e soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Chaves estrangeiras
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Índices
            $table->index('numero_escala');
            $table->index('tipo_operacao');
            $table->index('status');
            $table->index(['eta_previsto', 'etd_previsto']);
            $table->index(['embarcacao_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalas');
    }
};