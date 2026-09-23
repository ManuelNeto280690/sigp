<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('endereco')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->nullable();
            $table->string('responsavel')->nullable();
            $table->string('telefone_responsavel')->nullable();
            $table->unsignedInteger('num_funcionarios')->default(0);
            $table->string('dominio')->unique();
            $table->string('db_nome')->nullable();
            $table->string('db_usuario')->nullable();
            $table->string('db_senha')->nullable();
            $table->string('path')->nullable();
            $table->string('api_token')->nullable();
             $table->string('email_smtp')->nullable();
             $table->string('email_smtp_senha')->nullable();
            $table->enum('worker_status', ['inactive', 'active'])->default('inactive');
            $table->timestamps();

            $table->index('worker_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portos');
    }
};