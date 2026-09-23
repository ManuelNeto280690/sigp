<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Janela Única Marítima') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Controle integrado de escalas e formulários FAL</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                
                <!-- Botão para ver todas as escalas -->
                <a href="{{ route('janela-unica.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-list mr-1 text-xs"></i>Ver Escalas
                </a>
                
                <!-- Botão para criar pedidos gerais -->
                <a href="{{ route('pedidos.create') }}" class="bg-green-500 hover:bg-green-600 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs shadow-lg">
                    <i class="fas fa-file-plus mr-1 text-xs"></i>Novo Pedido
                </a>
                
                <!-- Botão para criar nova escala -->
                <a href="{{ route('janela-unica.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Nova Escala
                </a>
                
                <!-- Menu dropdown para ações rápidas -->
                <div class="relative inline-block text-left">
                    <button type="button" onclick="toggleDropdown()" class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs shadow-lg">
                        <i class="fas fa-ellipsis-v mr-1 text-xs"></i>Mais Ações
                    </button>
                    
                    <div id="actionsDropdown" class="hidden absolute right-0 z-10 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1" role="menu">
                            <a href="{{ route('movimento-terminal.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <i class="fas fa-truck mr-2"></i>Novo Movimento Terminal
                            </a>
                            
                            <a href="{{ route('inspecoes.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <i class="fas fa-search mr-2"></i>Nova Inspeção
                            </a>
                            
                            <a href="{{ route('embarcacoes.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <i class="fas fa-ship mr-2"></i>Nova Embarcação
                            </a>
                            
                            <div class="border-t border-gray-100"></div>
                            
                            <a href="{{ route('janela-unica.relatorios.escalas') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <i class="fas fa-chart-bar mr-2"></i>Relatórios
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-stat-card 
                    title="Escalas Ativas" 
                    :value="$stats['escalas_ativas']" 
                    icon="fas fa-anchor" 
                    color="blue"
                    :trend="$stats['trend_escalas']"
                />
                <x-stat-card 
                    title="FALs Pendentes" 
                    :value="$stats['fals_pendentes']" 
                    icon="fas fa-file-alt" 
                    color="yellow"
                    :trend="$stats['trend_fals']"
                />
                <x-stat-card 
                    title="Aprovações Hoje" 
                    :value="$stats['aprovacoes_hoje']" 
                    icon="fas fa-check-circle" 
                    color="green"
                    :trend="$stats['trend_aprovacoes']"
                />
                <x-stat-card 
                    title="Embarcações" 
                    :value="$stats['total_embarcacoes']" 
                    icon="fas fa-ship" 
                    color="purple"
                    :trend="$stats['trend_embarcacoes']"
                />
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gráfico de Escalas por Status -->
                <x-card title="Escalas por Status" icon="fas fa-chart-pie">
                    <canvas id="escalasStatusChart" width="400" height="200"></canvas>
                </x-card>

                <!-- Gráfico de FALs por Tipo -->
                <x-card title="FALs por Tipo" icon="fas fa-chart-bar">
                    <canvas id="falsTipoChart" width="400" height="200"></canvas>
                </x-card>
            </div>

            <!-- Escalas Recentes -->
            <x-card title="Escalas Recentes" icon="fas fa-clock">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Escala</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Embarcação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terminal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progresso FALs</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($escalasRecentes as $escala)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $escala->numero_escala }}</div>
                                    <div class="text-sm text-gray-500">{{ $escala->tipo_operacao }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $escala->embarcacao->nome }}</div>
                                    <div class="text-sm text-gray-500">{{ $escala->embarcacao->imo }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    N/A
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :type="$escala->status">{{ ucfirst($escala->status) }}</x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $escala->progresso_fals ?? 0 }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $escala->progresso_fals ?? 0 }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('janela-unica.show', $escala) }}" class="text-blue-600 hover:text-blue-900 mr-3">Ver</a>
                                    <a href="{{ route('janela-unica.edit', $escala) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhuma escala encontrada</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

        </div>
    </div>

    @push('scripts')
    <script>
        // Gráfico de Escalas por Status
        const escalasCtx = document.getElementById('escalasStatusChart').getContext('2d');
        new Chart(escalasCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($stats['escalas_por_status'])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($stats['escalas_por_status'])) !!},
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de FALs por Tipo
        const falsCtx = document.getElementById('falsTipoChart').getContext('2d');
        new Chart(falsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($stats['fals_por_tipo'])) !!},
                datasets: [{
                    label: 'Quantidade',
                    data: {!! json_encode(array_values($stats['fals_por_tipo'])) !!},
                    backgroundColor: '#3B82F6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function refreshData() {
            location.reload();
        }
        
        // Função para toggle do dropdown
        function toggleDropdown() {
            const dropdown = document.getElementById('actionsDropdown');
            dropdown.classList.toggle('hidden');
        }
        
        // Fechar dropdown quando clicar fora
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('actionsDropdown');
            const button = event.target.closest('button');
            
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleDropdown') === -1) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>