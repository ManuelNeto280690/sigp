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
        Schema::create('guindastes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('terminal_id');
            $table->string('nome'); // Ex: Guindaste 1, Guindaste 2
            $table->enum('tipo', ['container', 'graneis_solidos', 'graneis_liquidos', 'carga_geral', 'passageiros', 'tanque', 'ro_ro', 'frigorifico']);
            $table->enum('status', ['disponivel', 'ocupado', 'manutencao'])->default('disponivel');
            $table->timestamps();

            $table->foreign('terminal_id')->references('id')->on('terminais')->onDelete('cascade');
            $table->index(['terminal_id', 'tipo', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guindastes');
    }
};
