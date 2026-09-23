<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable(); // Usuário que executou a ação
            $table->string('user_type')->nullable(); // Tipo do usuário (para polimorfismo)
            $table->string('event'); // created, updated, deleted, login, logout, etc.
            $table->string('auditable_type'); // Modelo afetado
            $table->uuid('auditable_id')->nullable(); // ID do registro afetado 
            $table->text('description')->nullable();
            $table->json('old_values')->nullable(); // Valores antigos
            $table->json('new_values')->nullable(); // Valores novos
            $table->string('url')->nullable(); // URL da requisição
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('tags')->nullable(); // Tags para categorização
            $table->timestamp('created_at');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'event']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};