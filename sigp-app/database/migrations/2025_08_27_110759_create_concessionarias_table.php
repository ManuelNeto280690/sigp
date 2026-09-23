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
        Schema::create('concessionarias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('nif')->unique();
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->text('endereco')->nullable();
            $table->string('responsavel_nome')->nullable();
            $table->string('responsavel_email')->nullable();
            $table->string('responsavel_telefone')->nullable();
            $table->date('data_inicio_concessao')->nullable();
            $table->date('data_fim_concessao')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concessionarias');
    }
};
