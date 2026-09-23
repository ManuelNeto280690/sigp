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
        Schema::create('bercos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('terminal_id');
            $table->string('nome'); // Ex: Berço 1, Berço A
            $table->decimal('calado_maximo', 8, 2)->nullable(); // Pode ter calado específico
            $table->decimal('comprimento_maximo', 8, 2)->nullable(); // Opcional
            $table->enum('status', ['disponivel', 'ocupado', 'manutencao'])->default('disponivel');
            $table->timestamps();

            $table->foreign('terminal_id')->references('id')->on('terminais')->onDelete('cascade');
            $table->index(['terminal_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bercos');
    }
};
