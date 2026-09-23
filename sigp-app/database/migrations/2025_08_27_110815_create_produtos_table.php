<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('codigo_ncm')->nullable(); // Nomenclatura Comum do Mercosul
            $table->string('codigo_interno')->unique()->nullable();
            $table->enum('categoria', ['container', 'granel_solido', 'granel_liquido', 'carga_geral', 'perigosa']);
            $table->text('descricao')->nullable();
            $table->string('unidade_medida'); // toneladas, m³, unidades, etc.
            $table->boolean('produto_perigoso')->default(false);
            $table->string('classe_risco')->nullable(); // Para produtos perigosos
            $table->json('caracteristicas_especiais')->nullable();
            $table->text('restricoes_manuseio')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['categoria', 'produto_perigoso']);
            $table->index('codigo_ncm');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};