<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // Usuário que criou o alerta
            $table->string('titulo');
            $table->text('descricao');
            $table->enum('tipo', ['seguranca', 'ambiental', 'operacional', 'manutencao', 'emergencia']);
            $table->enum('nivel', ['info', 'aviso', 'critico', 'emergencia'])->default('info');
            $table->enum('status', ['ativo', 'resolvido', 'cancelado'])->default('ativo');
            $table->timestamp('data_inicio');
            $table->timestamp('data_fim')->nullable();
            $table->json('areas_afetadas')->nullable(); // Terminais, berços, etc.
            $table->text('acoes_tomadas')->nullable();
            $table->uuid('resolvido_por')->nullable();
            $table->timestamp('resolvido_em')->nullable();
            $table->boolean('notificar_usuarios')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('resolvido_por')->references('id')->on('users')->onDelete('set null');
            $table->index(['tipo', 'nivel', 'status']);
            $table->index('data_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};