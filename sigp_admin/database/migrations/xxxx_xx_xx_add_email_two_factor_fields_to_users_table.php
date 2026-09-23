<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Adicionar campos faltantes se não existirem
            if (!Schema::hasColumn('users', 'two_factor_email_code')) {
                $table->string('two_factor_email_code')->nullable();
            }
            if (!Schema::hasColumn('users', 'two_factor_email_code_expires_at')) {
                $table->timestamp('two_factor_email_code_expires_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'current_session_id')) {
                $table->string('current_session_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_activity')) {
                $table->timestamp('last_activity')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_email_code',
                'two_factor_email_code_expires_at',
                'current_session_id',
                'last_activity'
            ]);
        });
    }
};