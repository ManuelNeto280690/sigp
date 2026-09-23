<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Require dbal if not present, though usually handled via `string()`
        Schema::table('facturas', function (Blueprint $table) {
            $table->string('tipo_documento', 50)->default('FT')->change();
        });
    }

    public function down(): void
    {
        // Reverting this is generally lossy if types outside the enum exist
        Schema::table('facturas', function (Blueprint $table) {
            $table->enum('tipo_documento', ['FT','FR','NC','ND','FA'])->default('FT')->change();
        });
    }
};
