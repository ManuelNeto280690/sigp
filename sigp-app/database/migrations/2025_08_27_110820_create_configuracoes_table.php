<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('chave')->unique();
            $table->text('valor')->nullable();
            $table->text('logo')->nullable();
            $table->string('tipo')->default('string'); // string, integer, boolean, json
            $table->text('descricao')->nullable();
            $table->string('categoria')->default('geral');
            $table->boolean('editavel')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['categoria', 'chave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};