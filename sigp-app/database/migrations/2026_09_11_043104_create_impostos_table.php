<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('impostos', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: IVA, Retenção na Fonte, Imposto de Selo
            $table->string('sigla'); // Ex: IVA, II, IS
            $table->decimal('taxa', 5, 2); // Ex: 14.00, 6.50
            $table->enum('tipo', ['iva', 'retencao', 'selo', 'outro'])->default('iva');
            $table->string('motivo_isencao_codigo')->nullable(); // Ex: M02, M04
            $table->string('motivo_isencao_descricao')->nullable(); // Ex: Artigo 14.º do CIVA
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impostos');
    }
};
