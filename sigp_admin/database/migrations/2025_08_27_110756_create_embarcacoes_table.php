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
        Schema::create('embarcacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('imo')->unique(); // International Maritime Organization number
            $table->string('mmsi')->unique(); // Maritime Mobile Service Identity
            $table->string('bandeira'); // País da bandeira
            $table->string('tipo_embarcacao');
            $table->decimal('comprimento', 8, 2)->nullable();
            $table->decimal('largura', 8, 2)->nullable();
            $table->decimal('calado', 8, 2)->nullable();
            $table->integer('arqueacao_bruta')->nullable();
            $table->integer('arqueacao_liquida')->nullable();
            $table->string('armador')->nullable();
            $table->string('agente_maritimo')->nullable();
            $table->string('capitao')->nullable();
            $table->string('porto_origem')->nullable();
            $table->string('porto_destino')->nullable();
            $table->timestamp('eta')->nullable(); // Estimated Time of Arrival
            $table->timestamp('etd')->nullable(); // Estimated Time of Departure
            $table->timestamp('ata')->nullable(); // Actual Time of Arrival
            $table->timestamp('atd')->nullable(); // Actual Time of Departure
            $table->enum('status', ['esperado', 'atracado', 'operando', 'partido'])->default('esperado');
            $table->text('observacoes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index(['imo', 'mmsi']);
            $table->index('status');
            $table->index('tipo_embarcacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embarcacoes');
    }
};
