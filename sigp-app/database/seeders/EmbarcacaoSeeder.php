<?php

namespace Database\Seeders;

use App\Models\Embarcacao;
use Illuminate\Database\Seeder;

class EmbarcacaoSeeder extends Seeder
{
    public function run(): void
    {
        $embarcacoes = [
            [
                'nome' => 'MSC Splendida',
                'imo' => '9359787',
                'mmsi' => '247234300',
                'bandeira' => 'Panamá',
                'tipo_embarcacao' => 'Navio de Cruzeiro',
                'comprimento' => 333.30,
                'largura' => 38.00,
                'calado' => 8.55,
                'arqueacao_bruta' => 137936,
                'arqueacao_liquida' => 41338,
                'armador' => 'MSC Cruises',
                'agente_maritimo' => 'Agência Marítima Santos',
                'capitao' => 'Giuseppe Marotta',
                'porto_origem' => 'Buenos Aires',
                'porto_destino' => 'Santos',
                'status' => 'atracado',
                'is_active' => true,
            ],
            [
                'nome' => 'Ever Given',
                'imo' => '9811000',
                'mmsi' => '353136000',
                'bandeira' => 'Panamá',
                'tipo_embarcacao' => 'Porta-contêineres',
                'comprimento' => 400.00,
                'largura' => 58.80,
                'calado' => 16.00,
                'arqueacao_bruta' => 220940,
                'arqueacao_liquida' => 99155,
                'armador' => 'Evergreen Marine',
                'agente_maritimo' => 'Wilson Sons',
                'capitao' => 'Kanthavel Krishnan',
                'porto_origem' => 'Singapura',
                'porto_destino' => 'Santos',
                'status' => 'operando',
                'is_active' => true,
            ],
            [
                'nome' => 'Valemax Brasil',
                'imo' => '9618439',
                'mmsi' => '710004570',
                'bandeira' => 'Brasil',
                'tipo_embarcacao' => 'Graneleiro',
                'comprimento' => 362.00,
                'largura' => 65.00,
                'calado' => 23.00,
                'arqueacao_bruta' => 200000,
                'arqueacao_liquida' => 120000,
                'armador' => 'Vale S.A.',
                'agente_maritimo' => 'Codesp',
                'capitao' => 'Carlos Silva',
                'porto_origem' => 'Tubarão',
                'porto_destino' => 'Qingdao',
                'status' => 'esperado',
                'is_active' => true,
            ],
        ];

        foreach ($embarcacoes as $embarcacao) {
            Embarcacao::create($embarcacao);
        }
    }
}