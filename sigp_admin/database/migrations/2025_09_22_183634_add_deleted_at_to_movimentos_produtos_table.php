<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentos_produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('movimentos_produtos', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimentos_produtos', function (Blueprint $table) {
            if (Schema::hasColumn('movimentos_produtos', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};