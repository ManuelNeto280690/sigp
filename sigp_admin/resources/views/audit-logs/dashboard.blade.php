<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-chart-bar text-white text-xs"></i>
                    </div>
                    {{ __('Dashboard de Auditoria') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Análise estatística e monitoramento da atividade do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <select id="periodSelect" onchange="changePeriod()" class="bg-white border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="7" {{ $period == '7' ? 'selected' : '' }}>Últimos 7 dias</option>
                    <option value="30" {{ $period == '30' ? 'selected' : '' }}>Últimos 30 dias</option>
                    <option value="90" {{ $period == '90' ? 'selected' : '' }}>Últimos 90 dias</option>
                </select>
                <a href="{{ route('audit-logs.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-list mr-1 text-xs"></i>Ver Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Estatísticas Principais -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-sm font-semibold uppercase tracking-wide">Total de Logs</p>
                            <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($stats['total_logs']) }}</p>
                            <p class="text-blue-600 text-xs mt-1">Últimos {{ $period }} dias</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-list text-white text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-sm font-semibold uppercase tracking-wide">Usuários Únicos</p>
                            <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($stats['unique_users']) }}</p>
                            <p class="text-blue-600 text-xs mt-1">Usuários ativos</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-sm font-semibold uppercase tracking-wide">IPs Únicos</p>
                            <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($stats['unique_ips']) }}</p>
                            <p class="text-green-600 text-xs mt-1">Endereços diferentes</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-globe text-white text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-6 border border-red-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-600 text-sm font-semibold uppercase tracking-wide">Tentativas Falhadas</p>
                            <p class="text-3xl font-bold text-red-900 mt-2">{{ number_format($stats['failed_attempts']) }}</p>
                            <p class="text-red-600 text-xs mt-1">Logins falhados</p>
                        </div>
                        <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Atividade Diária -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                    Atividade Diária
                </h3>
                <div class="h-64">
                    <canvas id="dailyActivityChart"></canvas>
                </div>
            </div>

            <!-- Rankings -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Top Usuários -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user-crown text-blue-500 mr-2"></i>
                        Usuários Mais Ativos
                    </h3>
                    <div class="space-y-3">
                        @foreach($topUsers as $index => $userStat)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-xs font-bold text-blue-600">{{ $index + 1 }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $userStat->user->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $userStat->user->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-blue-600">{{ number_format($userStat->total) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Ações -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-bolt text-blue-500 mr-2"></i>
                        Ações Mais Frequentes
                    </h3>
                    <div class="space-y-3">
                        @foreach($topActions as $index => $action)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-xs font-bold text-blue-600">{{ $index + 1 }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ ucfirst($action->action) }}</span>
                            </div>
                            <span class="text-sm font-bold text-blue-600">{{ number_format($action->total) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Modelos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-database text-blue-500 mr-2"></i>
                        Modelos Mais Auditados
                    </h3>
                    <div class="space-y-3">
                        @foreach($topModels as $index => $model)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-xs font-bold text-green-600">{{ $index + 1 }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ class_basename($model->model_type) }}</span>
                            </div>
                            <span class="text-sm font-bold text-green-600">{{ number_format($model->total) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- IPs Suspeitos -->
            @if($suspiciousIps->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-shield-alt text-red-500 mr-2"></i>
                    IPs Suspeitos
                    <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $suspiciousIps->count() }} detectados
                    </span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endereço IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tentativas Falhadas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível de Risco</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($suspiciousIps as $ip)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-globe text-red-500 mr-2"></i>
                                        <span class="text-sm font-mono text-gray-900">{{ $ip->ip_address }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-red-600">{{ $ip->attempts }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $riskLevel = $ip->attempts >= 20 ? 'Alto' : ($ip->attempts >= 10 ? 'Médio' : 'Baixo');
                                        $riskColor = $ip->attempts >= 20 ? 'bg-red-100 text-red-800' : ($ip->attempts >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-orange-100 text-orange-800');
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $riskColor }}">
                                        {{ $riskLevel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="blockIP('{{ $ip->ip_address }}')" class="text-red-600 hover:text-red-900 mr-3" title="Bloquear IP">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    <button onclick="viewIPDetails('{{ $ip->ip_address }}')" class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico de Atividade Diária
        const ctx = document.getElementById('dailyActivityChart').getContext('2d');
        const dailyActivityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyLogs->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d/m'); })) !!},
                datasets: [{
                    label: 'Logs por Dia',
                    data: {!! json_encode($dailyLogs->pluck('total')) !!},
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        function changePeriod() {
            const period = document.getElementById('periodSelect').value;
            window.location.href = `{{ route('audit-logs.dashboard') }}?period=${period}`;
        }

        function blockIP(ip) {
            if (confirm(`Deseja realmente bloquear o IP ${ip}?`)) {
                // Implementar bloqueio de IP
                alert('Funcionalidade de bloqueio será implementada');
            }
        }

        function viewIPDetails(ip) {
            window.location.href = `{{ route('audit-logs.index') }}?ip_address=${ip}`;
        }
    </script>
</x-app-layout>