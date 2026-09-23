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
        Schema::table('impostos', function (Blueprint $table) {
            $table->string('tipo', 50)->default('IVA')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('impostos', function (Blueprint $table) {
            // This is complex to reverse safely, keeping as string is safer
        });
    }
};
