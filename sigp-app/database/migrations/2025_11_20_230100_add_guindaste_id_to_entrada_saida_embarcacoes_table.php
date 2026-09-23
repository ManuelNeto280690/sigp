<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'guindaste_id')) {
                $table->uuid('guindaste_id')->nullable()->after('berco');
            }
        });

        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'guindaste_id')) {
                $table->foreign('guindaste_id')->references('id')->on('guindastes')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'guindaste_id')) {
                $table->dropForeign(['guindaste_id']);
                $table->dropColumn('guindaste_id');
            }
        });
    }
};