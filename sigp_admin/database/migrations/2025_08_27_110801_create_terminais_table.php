<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminais', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('concessionaria_id');
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->enum('tipo', ['container', 'graneis_solidos', 'graneis_liquidos', 'carga_geral', 'passageiros']);
            $table->text('descricao')->nullable();
            $table->decimal('area_total', 10, 2)->nullable();
            $table->decimal('area_operacional', 10, 2)->nullable();
            $table->integer('numero_bercos')->default(1);
            $table->decimal('calado_maximo', 8, 2)->nullable();
            $table->integer('capacidade_armazenagem')->nullable();
            $table->json('equipamentos')->nullable();
            $table->enum('status', ['ativo', 'inativo', 'manutencao'])->default('ativo');
            $table->text('observacoes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('concessionaria_id')->references('id')->on('concessionarias')->onDelete('cascade');
            $table->index(['tipo', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminais');
    }
};