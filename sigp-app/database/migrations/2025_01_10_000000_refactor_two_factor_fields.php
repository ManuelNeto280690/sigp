<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remover campos antigos se existirem
            $columnsToCheck = [
                'two_factor_code',
                'two_factor_expires_at',
                'two_factor_recovery_codes',
                'google2fa_secret',
                'google2fa_enabled',
                'google2fa_enabled_at'
            ];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Adicionar novos campos organizados
            if (!Schema::hasColumn('users', 'two_factor_email_code')) {
                $table->string('two_factor_email_code', 6)->nullable()->after('two_factor_method');
            }
            if (!Schema::hasColumn('users', 'two_factor_email_code_expires_at')) {
                $table->timestamp('two_factor_email_code_expires_at')->nullable()->after('two_factor_email_code');
            }
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('password');
            }
            
            // Modificar campos existentes
            $table->string('two_factor_method')->nullable()->default(null)->change();
            $table->boolean('two_factor_enabled')->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_email_code',
                'two_factor_email_code_expires_at',
                'password_changed_at'
            ]);
            
            // Restaurar campos antigos se necessário
            $table->string('two_factor_method')->default('email')->change();
        });
    }
};