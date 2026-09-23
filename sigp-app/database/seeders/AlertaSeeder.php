<?php

namespace Database\Seeders;

use App\Models\Alerta;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlertaSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first(); // Pega o primeiro usuário disponível
        
        if (!$user) {
            // Se não há usuários, cria um usuário admin básico
            $user = User::create([
                'name' => 'Admin Sistema',
                'email' => 'admin@sigp.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $alertas = [
            [
                'user_id' => $user->id,
                'titulo' => 'Condições Meteorológicas Adversas',
                'descricao' => 'Ventos fortes previstos para as próximas 6 horas. Velocidade estimada: 45 km/h.',
                'tipo' => 'meteorologico',
                'nivel' => 'warning',
                'status' => 'ativo',
                'data_inicio' => now(),
                'data_fim' => now()->addHours(6),
                'areas_afetadas' => ['Terminal 1', 'Terminal 2', 'Área de Fundeio'],
                'acoes_tomadas' => 'Suspensão de operações de carga/descarga em contêineres',
                'notificar_usuarios' => true,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'titulo' => 'Manutenção Programada - Guindaste 3',
                'descricao' => 'Manutenção preventiva programada para o guindaste do Terminal 3.',
                'tipo' => 'manutencao',
                'nivel' => 'info',
                'status' => 'ativo',
                'data_inicio' => now()->addHours(2),
                'data_fim' => now()->addHours(8),
                'areas_afetadas' => ['Terminal 3'],
                'acoes_tomadas' => 'Redirecionamento de operações para outros terminais',
                'notificar_usuarios' => true,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'titulo' => 'Congestionamento no Acesso Rodoviário',
                'descricao' => 'Tráfego intenso na entrada principal do porto devido a acidente na rodovia.',
                'tipo' => 'operacional',
                'nivel' => 'warning',
                'status' => 'ativo',
                'data_inicio' => now()->subHour(),
                'data_fim' => now()->addHours(3),
                'areas_afetadas' => ['Portaria Principal', 'Acesso Rodoviário'],
                'acoes_tomadas' => 'Ativação de rota alternativa via Portaria Secundária',
                'notificar_usuarios' => true,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'titulo' => 'Sistema de Comunicação - Falha Parcial',
                'descricao' => 'Intermitência no sistema de rádio comunicação entre torre de controle e embarcações.',
                'tipo' => 'tecnico',
                'nivel' => 'error',
                'status' => 'ativo',
                'data_inicio' => now()->subMinutes(30),
                'data_fim' => null,
                'areas_afetadas' => ['Torre de Controle', 'Canal de Navegação'],
                'acoes_tomadas' => 'Equipe técnica acionada. Uso de sistema backup ativado.',
                'notificar_usuarios' => true,
                'is_active' => true,
            ],
            [
                'user_id' => $user->id,
                'titulo' => 'Inspeção Ambiental Programada',
                'descricao' => 'Inspeção de rotina dos sistemas de tratamento de efluentes.',
                'tipo' => 'ambiental',
                'nivel' => 'info',
                'status' => 'ativo',
                'data_inicio' => now()->addDay(),
                'data_fim' => now()->addDay()->addHours(4),
                'areas_afetadas' => ['Estação de Tratamento', 'Área Industrial'],
                'acoes_tomadas' => 'Agendamento com órgão ambiental confirmado',
                'notificar_usuarios' => false,
                'is_active' => true,
            ],
        ];

        foreach ($alertas as $alerta) {
            Alerta::create($alerta);
        }
    }
}