<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Campos para controle geral do 2FA
            $table->boolean('two_factor_enabled')->default(false)->change();
            $table->string('two_factor_method')->nullable()->change(); // 'email', 'authenticator', null
            $table->boolean('two_factor_required')->default(false); // Novo campo para controle administrativo
            $table->timestamp('two_factor_setup_at')->nullable(); // Quando foi configurado pela primeira vez
            
            // Campos para autenticador (Google Authenticator, etc.)
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable();
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable();
            }
            
            // Campos para 2FA por email
            if (!Schema::hasColumn('users', 'two_factor_email_code')) {
                $table->string('two_factor_email_code', 6)->nullable();
            }
            if (!Schema::hasColumn('users', 'two_factor_email_code_expires_at')) {
                $table->timestamp('two_factor_email_code_expires_at')->nullable();
            }
            $table->integer('two_factor_email_attempts')->default(0); // Novo campo para controlar tentativas
            
            // Campos para controle de segurança
            $table->integer('two_factor_failed_attempts')->default(0);
            $table->timestamp('two_factor_locked_until')->nullable();
            
            // Índices para performance
            $table->index(['two_factor_enabled', 'two_factor_method']);
            $table->index('two_factor_email_code_expires_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_required',
                'two_factor_setup_at',
                'two_factor_email_attempts',
                'two_factor_failed_attempts',
                'two_factor_locked_until'
            ]);
            
            $table->dropIndex(['two_factor_enabled', 'two_factor_method']);
            $table->dropIndex(['two_factor_email_code_expires_at']);
        });
    }
};