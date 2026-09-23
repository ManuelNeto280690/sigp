<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentos_produtos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('embarcacao_id');
            $table->uuid('terminal_id');
            $table->uuid('produto_id');
            $table->uuid('user_id'); // Usuário que registrou
            $table->string('numero_movimento')->unique();
            $table->enum('tipo_movimento', ['embarque', 'desembarque', 'transbordo']);
            $table->decimal('quantidade', 12, 3);
            $table->string('unidade_medida');
            $table->timestamp('data_inicio');
            $table->timestamp('data_fim')->nullable();
            $table->string('origem')->nullable();
            $table->string('destino')->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('status', ['planejado', 'em_andamento', 'concluido', 'cancelado'])->default('planejado');
            $table->json('documentos_associados')->nullable();
            $table->uuid('autorizado_por')->nullable();
            $table->timestamp('autorizado_em')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('embarcacao_id')->references('id')->on('embarcacoes')->onDelete('cascade');
            $table->foreign('terminal_id')->references('id')->on('terminais')->onDelete('cascade');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('autorizado_por')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo_movimento', 'status']);
            $table->index(['data_inicio', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentos_produtos');
    }
};