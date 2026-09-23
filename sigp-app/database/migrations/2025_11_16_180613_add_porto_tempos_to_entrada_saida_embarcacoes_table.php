<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'porto_origem')) {
                $table->string('porto_origem')->nullable()->after('capitania_destino');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'porto_destino')) {
                $table->string('porto_destino')->nullable()->after('porto_origem');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta')) {
                $table->timestamp('eta')->nullable()->after('porto_destino');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'etd')) {
                $table->timestamp('etd')->nullable()->after('eta');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'ata')) {
                $table->timestamp('ata')->nullable()->after('etd');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'atd')) {
                $table->timestamp('atd')->nullable()->after('ata');
            }
             if (!Schema::hasColumn('entrada_saida_embarcacoes', 'estado_embarcacao')) {
                $table->enum('estado_embarcacao', ['esperado', 'atracado', 'operando', 'partido'])->default('esperado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'atd')) {
                $table->dropColumn('atd');
            }
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'ata')) {
                $table->dropColumn('ata');
            }
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'etd')) {
                $table->dropColumn('etd');
            }
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'eta')) {
                $table->dropColumn('eta');
            }
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'porto_destino')) {
                $table->dropColumn('porto_destino');
            }
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'porto_origem')) {
                $table->dropColumn('porto_origem');
            }
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'estado_embarcacao')) {
                $table->dropColumn('estado_embarcacao');
            }
        });
    }
};
