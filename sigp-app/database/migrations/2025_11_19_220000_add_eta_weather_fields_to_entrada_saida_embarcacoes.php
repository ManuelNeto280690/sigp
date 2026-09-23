<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_temperatura_c')) {
                $table->decimal('eta_temperatura_c', 5, 2)->nullable()->after('eta');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_vento_max_kph')) {
                $table->unsignedInteger('eta_vento_max_kph')->nullable()->after('eta_temperatura_c');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_rajada_kph')) {
                $table->unsignedInteger('eta_rajada_kph')->nullable()->after('eta_vento_max_kph');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_chuva_chance_pct')) {
                $table->unsignedInteger('eta_chuva_chance_pct')->nullable()->after('eta_rajada_kph');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_swell_altura_m')) {
                $table->decimal('eta_swell_altura_m', 5, 2)->nullable()->after('eta_chuva_chance_pct');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_swell_periodo_s')) {
                $table->decimal('eta_swell_periodo_s', 5, 2)->nullable()->after('eta_swell_altura_m');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_swell_direcao')) {
                $table->string('eta_swell_direcao', 16)->nullable()->after('eta_swell_periodo_s');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_onda_altura_m')) {
                $table->decimal('eta_onda_altura_m', 5, 2)->nullable()->after('eta_swell_direcao');
            }
            if (!Schema::hasColumn('entrada_saida_embarcacoes', 'eta_onda_direcao')) {
                $table->string('eta_onda_direcao', 16)->nullable()->after('eta_onda_altura_m');
            }
        });
    }

    public function down(): void
    {
        Schema::table('entrada_saida_embarcacoes', function (Blueprint $table) {
            $cols = [
                'eta_temperatura_c',
                'eta_vento_max_kph',
                'eta_rajada_kph',
                'eta_chuva_chance_pct',
                'eta_swell_altura_m',
                'eta_swell_periodo_s',
                'eta_swell_direcao',
                'eta_onda_altura_m',
                'eta_onda_direcao',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('entrada_saida_embarcacoes', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};