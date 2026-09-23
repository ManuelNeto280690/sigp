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
        Schema::table('facturas', function (Blueprint $table) {
            // Document types and series
            $table->enum('tipo_documento', ['FT', 'FR', 'NC', 'ND', 'FA'])->default('FT')->after('movimento_carga_id');
            $table->string('serie')->default('2026')->after('tipo_documento');
            $table->integer('numero_sequencial')->default(0)->after('numero');
            
            // AGT Cryptography and control
            $table->text('hash')->nullable()->after('numero_sequencial');
            $table->text('hash_anterior')->nullable()->after('hash');
            $table->string('hash_control')->default('1')->after('hash_anterior');
            
            // Tax totals
            $table->decimal('total_s_iva', 14, 2)->default(0)->after('valor_total');
            $table->decimal('total_iva', 14, 2)->default(0)->after('total_s_iva');
            $table->decimal('total_imposto_selo', 14, 2)->default(0)->after('total_iva');
            $table->decimal('total_retencao', 14, 2)->default(0)->after('total_imposto_selo');
            $table->decimal('total_a_pagar', 14, 2)->default(0)->after('total_retencao');
            
            // Electronic transmission status
            $table->enum('agt_status', ['pendente', 'transmitido', 'erro'])->default('pendente')->after('status');
        });

        Schema::table('factura_items', function (Blueprint $table) {
            $table->unsignedBigInteger('taxa_iva_id')->nullable()->after('factura_id');
            $table->decimal('valor_iva', 14, 2)->default(0)->after('taxa_iva_id');
            $table->string('motivo_isencao_codigo')->nullable()->after('valor_iva');
            $table->boolean('sujeito_retencao')->default(false)->after('motivo_isencao_codigo');

            $table->foreign('taxa_iva_id')->references('id')->on('impostos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factura_items', function (Blueprint $table) {
            $table->dropForeign(['taxa_iva_id']);
            $table->dropColumn(['taxa_iva_id', 'valor_iva', 'motivo_isencao_codigo', 'sujeito_retencao']);
        });

        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_documento', 'serie', 'numero_sequencial', 'hash', 'hash_anterior', 
                'hash_control', 'total_s_iva', 'total_iva', 'total_imposto_selo', 
                'total_retencao', 'total_a_pagar', 'agt_status'
            ]);
        });
    }
};
