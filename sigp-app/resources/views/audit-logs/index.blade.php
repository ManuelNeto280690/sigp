<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-history text-white text-xs"></i>
                    </div>
                    {{ __('Auditoria do Sistema') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Monitore todas as ações e alterações realizadas no sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <a href="{{ route('audit-logs.dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-chart-bar mr-1 text-xs"></i>Dashboard
                </a>
                <button onclick="exportLogs()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-download mr-1 text-xs"></i>Exportar
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Seção de Mensagens -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Estatísticas Rápidas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Logs</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($stats['total_logs'] ?? 0) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-list text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Hoje</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($stats['logs_today'] ?? 0) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar-day text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Esta Semana</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ number_format($stats['logs_this_week'] ?? 0) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar-week text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-600 text-xs font-semibold uppercase tracking-wide">Este Mês</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ number_format($stats['logs_this_month'] ?? 0) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar-alt text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-indigo-600 text-xs font-semibold uppercase tracking-wide">Usuários Únicos</p>
                            <p class="text-2xl font-bold text-indigo-900 mt-1">{{ number_format($stats['unique_users'] ?? 0) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-users text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Avançados -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filtros de Auditoria
                </h3>
                
                <form method="GET" action="{{ route('audit-logs.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Descrição, usuário, IP..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
                            <select name="user_id" id="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Todos os usuários</option>
                                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="action" class="block text-sm font-medium text-gray-700 mb-1">Ação</label>
                            <select name="action" id="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Todas as ações</option>
                                <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Criar</option>
                                <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Atualizar</option>
                                <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Excluir</option>
                                <option value="view" {{ request('action') == 'view' ? 'selected' : '' }}>Visualizar</option>
                                <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                                <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                                <option value="login_failed" {{ request('action') == 'login_failed' ? 'selected' : '' }}>Login Falhado</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="model_type" class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                            <select name="model_type" id="model_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Todos os modelos</option>
                                <option value="User" {{ request('model_type') == 'User' ? 'selected' : '' }}>Usuário</option>
                                <option value="Embarcacao" {{ request('model_type') == 'Embarcacao' ? 'selected' : '' }}>Embarcação</option>
                                <option value="Terminal" {{ request('model_type') == 'Terminal' ? 'selected' : '' }}>Terminal</option>
                                <option value="Inspecao" {{ request('model_type') == 'Inspecao' ? 'selected' : '' }}>Inspeção</option>
                                <option value="Incidente" {{ request('model_type') == 'Incidente' ? 'selected' : '' }}>Incidente</option>
                                <option value="Alerta" {{ request('model_type') == 'Alerta' ? 'selected' : '' }}>Alerta</option>
                                <option value="Configuracao" {{ request('model_type') == 'Configuracao' ? 'selected' : '' }}>Configuração</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-search mr-1"></i>Filtrar
                            </button>
                            <a href="{{ route('audit-logs.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabela de Logs de Auditoria -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list text-blue-500 mr-2"></i>
                        Logs de Auditoria
                        <span class="ml-2 text-sm font-normal text-gray-500">({{ $logs->total() }} registros)</span>
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $log->created_at->format('d/m/Y') }}</span>
                                        <span class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-blue-600 text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $log->user->name ?? 'Sistema' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $log->user->email ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $actionColors = [
                                            'create' => 'bg-green-100 text-green-800',
                                            'update' => 'bg-blue-100 text-blue-800',
                                            'delete' => 'bg-red-100 text-red-800',
                                            'view' => 'bg-gray-100 text-gray-800',
                                            'login' => 'bg-blue-100 text-blue-800',
                                            'logout' => 'bg-yellow-100 text-yellow-800',
                                            'login_failed' => 'bg-red-100 text-red-800',
                                        ];
                                        $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->getModelName() }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                    <div class="truncate" title="{{ $log->description }}">
                                        {{ $log->description ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="font-mono">{{ $log->ip_address }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('audit-logs.show', $log) }}" 
                                       class="text-blue-600 hover:text-blue-900 mr-3" 
                                       title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($log->old_values || $log->new_values)
                                    <button onclick="showChanges('{{ $log->id }}')" 
                                            class="text-blue-600 hover:text-blue-900" 
                                            title="Ver alterações">
                                        <i class="fas fa-code-branch"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-history text-gray-300 text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum log encontrado</h3>
                                        <p class="text-gray-500">Não há logs de auditoria que correspondam aos filtros aplicados.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
                @endif
            </div>

            <!-- Resumo de Ações Mais Frequentes -->
            @if(isset($stats['actions_count']) && $stats['actions_count']->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Ações Mais Frequentes
                    </h3>
                    <div class="space-y-3">
                        @foreach($stats['actions_count'] as $action)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-900">{{ ucfirst($action->action) }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ number_format($action->total) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if(isset($stats['models_count']) && $stats['models_count']->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-database text-blue-500 mr-2"></i>
                        Modelos Mais Auditados
                    </h3>
                    <div class="space-y-3">
                        @foreach($stats['models_count'] as $model)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-indigo-500 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-900">{{ class_basename($model->model_type) }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ number_format($model->total) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Modal para Exibir Alterações -->
    <div id="changesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detalhes das Alterações</h3>
                    <button onclick="closeChangesModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="changesContent" class="space-y-4">
                    <!-- Conteúdo será carregado via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script>
        function refreshData() {
            window.location.reload();
        }

        function exportLogs() {
            const params = new URLSearchParams(window.location.search);
            params.set('format', 'csv');
            window.open(`{{ route('audit-logs.export') }}?${params.toString()}`, '_blank');
        }

        function showChanges(logId) {
            // Implementar busca AJAX dos detalhes das alterações
            fetch(`/audit-logs/${logId}/changes`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('changesContent');
                    content.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Valores Anteriores</h4>
                                <pre class="bg-red-50 border border-red-200 rounded p-3 text-sm overflow-x-auto">${JSON.stringify(data.old_values, null, 2)}</pre>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Novos Valores</h4>
                                <pre class="bg-green-50 border border-green-200 rounded p-3 text-sm overflow-x-auto">${JSON.stringify(data.new_values, null, 2)}</pre>
                            </div>
                        </div>
                    `;
                    document.getElementById('changesModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Erro ao carregar alterações:', error);
                    alert('Erro ao carregar detalhes das alterações');
                });
        }

        function closeChangesModal() {
            document.getElementById('changesModal').classList.add('hidden');
        }

        // Auto-refresh a cada 30 segundos
        setInterval(function() {
            if (!document.hidden) {
                // Atualizar apenas as estatísticas via AJAX
                fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Atualizar estatísticas sem recarregar a página
                    console.log('Estatísticas atualizadas');
                })
                .catch(error => console.log('Erro na atualização automática:', error));
            }
        }, 30000);
    </script>
</x-app-layout>