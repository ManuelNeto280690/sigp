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
        // Adicionar campos de revogação à tabela entrada_saida_embarcacoes
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'motivo_revogacao')) {
                $table->text('motivo_revogacao')->nullable()->after('observacoes');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'revogado_por')) {
                $table->uuid('revogado_por')->nullable()->after('motivo_revogacao');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'revogado_em')) {
                $table->timestamp('revogado_em')->nullable()->after('revogado_por');
            }
        });

        // Adicionar campos de revogação à tabela entrada_saida_embarcacao_concessionarias
        Schema::table('entrada_saida_embarcacao_concessionarias', function (Blueprint $table) {
            if (!Schema::hasColumn('entrada_saida_embarcacao_concessionarias', 'motivo_revogacao')) {
                $table->text('motivo_revogacao')->nullable()->after('observacoes');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacao_concessionarias', 'revogado_por')) {
                $table->uuid('revogado_por')->nullable()->after('motivo_revogacao');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacao_concessionarias', 'revogado_em')) {
                $table->timestamp('revogado_em')->nullable()->after('revogado_por');
            }
        });

        // Adicionar chaves estrangeiras
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'revogado_por')) {
                $table->foreign('revogado_por')->references('id')->on('users')->onDelete('set null');
            }
        });

        Schema::table('entrada_saida_embarcacao_concessionarias', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacao_concessionarias', 'revogado_por')) {
                $table->foreign('revogado_por')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover chaves estrangeiras primeiro
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'revogado_por')) {
                $table->dropForeign(['revogado_por']);
            }
        });

        Schema::table('entrada_saida_embarcacao_concessionarias', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacao_concessionarias', 'revogado_por')) {
                $table->dropForeign(['revogado_por']);
            }
        });

        // Remover colunas da tabela entrada_saida_embarcacoes
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'revogado_em')) {
                $table->dropColumn('revogado_em');
            }
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'revogado_por')) {
                $table->dropColumn('revogado_por');
            }
            if (Schema::hasColumn('entrada_saida_embarcacoes', 'motivo_revogacao')) {
                $table->dropColumn('motivo_revogacao');
            }
        });

        // Remover colunas da tabela entrada_saida_embarcacao_concessionarias
        Schema::table('entrada_saida_embarcacao_concessionarias', function (Blueprint $table) {
            if (Schema::hasColumn('entrada_saida_embarcacao_concessionarias', 'revogado_em')) {
                $table->dropColumn('revogado_em');
            }
            if (Schema::hasColumn('entrada_saida_embarcacao_concessionarias', 'revogado_por')) {
                $table->dropColumn('revogado_por');
            }
            if (Schema::hasColumn('entrada_saida_embarcacao_concessionarias', 'motivo_revogacao')) {
                $table->dropColumn('motivo_revogacao');
            }
        });
    }
};