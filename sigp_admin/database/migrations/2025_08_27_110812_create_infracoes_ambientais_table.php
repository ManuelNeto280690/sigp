<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infracoes_ambientais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('embarcacao_id')->nullable();
            $table->uuid('terminal_id')->nullable();
            $table->uuid('user_id'); // Inspetor que registrou
            $table->string('numero_auto')->unique();
            $table->enum('tipo_infracao', ['descarga_irregular', 'poluicao_atmosferica', 'residuos_solidos', 'ruido_excessivo', 'outras']);
            $table->text('descricao_infracao');
            $table->timestamp('data_infracao');
            $table->string('local_infracao');
            $table->enum('gravidade', ['leve', 'media', 'grave', 'gravissima'])->default('media');
            $table->decimal('valor_multa', 10, 2)->nullable();
            $table->text('medidas_corretivas')->nullable();
            $table->enum('status', ['registrada', 'notificada', 'contestada', 'paga', 'cancelada'])->default('registrada');
            $table->timestamp('prazo_regularizacao')->nullable();
            $table->text('observacoes')->nullable();
            $table->json('evidencias')->nullable(); // Fotos, documentos, etc.
            $table->uuid('aprovado_por')->nullable();
            $table->timestamp('aprovado_em')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('set null');
            $table->foreign('terminal_id')->references('id')->on('terminais')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('aprovado_por')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo_infracao', 'gravidade', 'status']);
            $table->index('data_infracao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infracoes_ambientais');
    }
};