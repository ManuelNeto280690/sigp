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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'current_session_id')) {
                $table->string('current_session_id')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'last_activity')) {
                $table->timestamp('last_activity')->nullable()->after('current_session_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'current_session_id')) {
                $table->dropColumn('current_session_id');
            }
            if (Schema::hasColumn('users', 'last_activity')) {
                $table->dropColumn('last_activity');
            }
        });
    }
};