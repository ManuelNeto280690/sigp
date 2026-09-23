<x-modern-sigp-layout>
    <x-slot name="title">Dashboard</x-slot>
    
    <x-slot name="breadcrumbs">
        [['title' => 'Dashboard', 'url' => route('dashboard')]]
    </x-slot>

    <!-- Header -->
    <div class="mb-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold leading-6 text-gray-900">Dashboard</h1>
                <p class="mt-2 text-sm text-gray-700">Visão geral do Sistema Integrado de Gestão Portuária</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button type="button" class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    <i class="fas fa-download mr-2"></i>Exportar Relatório
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Embarcações Ativas -->
        <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
                <div class="absolute rounded-md bg-blue-500 p-3">
                    <i class="fas fa-ship h-6 w-6 text-white"></i>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Embarcações Ativas</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['embarcacoes_ativas'] ?? 0 }}</p>
                @if(isset($stats['embarcacoes_trend']) && $stats['embarcacoes_trend'] != 0)
                <p class="ml-2 flex items-baseline text-sm font-semibold {{ $stats['embarcacoes_trend'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-arrow-{{ $stats['embarcacoes_trend'] > 0 ? 'up' : 'down' }} h-5 w-5 flex-shrink-0 self-center"></i>
                    <span class="sr-only">{{ $stats['embarcacoes_trend'] > 0 ? 'Aumentou' : 'Diminuiu' }} em </span>
                    {{ abs($stats['embarcacoes_trend']) }}%
                </p>
                @endif
            </dd>
            <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                <div class="text-sm">
                    <a href="{{ route('embarcacoes.index') }}" class="font-medium text-blue-600 hover:text-blue-500">
                        Ver todas<span class="sr-only"> embarcações</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas Ativos -->
        <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
                <div class="absolute rounded-md bg-yellow-500 p-3">
                    <i class="fas fa-exclamation-triangle h-6 w-6 text-white"></i>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Alertas Ativos</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['alertas_ativos'] ?? 0 }}</p>
                @if(isset($stats['alertas_trend']) && $stats['alertas_trend'] != 0)
                <p class="ml-2 flex items-baseline text-sm font-semibold {{ $stats['alertas_trend'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    <i class="fas fa-arrow-{{ $stats['alertas_trend'] > 0 ? 'up' : 'down' }} h-5 w-5 flex-shrink-0 self-center"></i>
                    <span class="sr-only">{{ $stats['alertas_trend'] > 0 ? 'Aumentou' : 'Diminuiu' }} em </span>
                    {{ abs($stats['alertas_trend']) }}%
                </p>
                @endif
            </dd>
            <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                <div class="text-sm">
                    <a href="#" class="font-medium text-yellow-600 hover:text-yellow-500">
                        Ver todos<span class="sr-only"> alertas</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Incidentes do Mês -->
        <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
                <div class="absolute rounded-md bg-red-500 p-3">
                    <i class="fas fa-exclamation-circle h-6 w-6 text-white"></i>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Incidentes do Mês</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['incidentes_mes'] ?? 0 }}</p>
                @if(isset($stats['incidentes_trend']) && $stats['incidentes_trend'] != 0)
                <p class="ml-2 flex items-baseline text-sm font-semibold {{ $stats['incidentes_trend'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    <i class="fas fa-arrow-{{ $stats['incidentes_trend'] > 0 ? 'up' : 'down' }} h-5 w-5 flex-shrink-0 self-center"></i>
                    <span class="sr-only">{{ $stats['incidentes_trend'] > 0 ? 'Aumentou' : 'Diminuiu' }} em </span>
                    {{ abs($stats['incidentes_trend']) }}%
                </p>
                @endif
            </dd>
            <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                <div class="text-sm">
                    <a href="#" class="font-medium text-red-600 hover:text-red-500">
                        Ver todos<span class="sr-only"> incidentes</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Inspeções Realizadas -->
        <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
                <div class="absolute rounded-md bg-green-500 p-3">
                    <i class="fas fa-clipboard-check h-6 w-6 text-white"></i>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Inspeções Realizadas</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['inspecoes_realizadas'] ?? 0 }}</p>
                @if(isset($stats['inspecoes_trend']) && $stats['inspecoes_trend'] != 0)
                <p class="ml-2 flex items-baseline text-sm font-semibold {{ $stats['inspecoes_trend'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                    <i class="fas fa-arrow-{{ $stats['inspecoes_trend'] > 0 ? 'up' : 'down' }} h-5 w-5 flex-shrink-0 self-center"></i>
                    <span class="sr-only">{{ $stats['inspecoes_trend'] > 0 ? 'Aumentou' : 'Diminuiu' }} em </span>
                    {{ abs($stats['inspecoes_trend']) }}%
                </p>
                @endif
            </dd>
            <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                <div class="text-sm">
                    <a href="#" class="font-medium text-green-600 hover:text-green-500">
                        Ver todas<span class="sr-only"> inspeções</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <!-- Movimentação por Mês -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line h-8 w-8 text-gray-400"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Movimentação por Mês</dt>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="relative h-64">
                    <canvas id="movimentacaoChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Embarcações por Tipo -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-pie h-8 w-8 text-gray-400"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Embarcações por Tipo</dt>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="relative h-64">
                    <canvas id="embarcacoesTipoChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-8">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Movimentações Recentes -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-ship mr-2 text-blue-500"></i>Movimentações Recentes
                    </h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($movimentacoes_recentes ?? [] as $movimentacao)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-ship h-4 w-4 text-white"></i>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900">{{ $movimentacao->embarcacao->nome ?? 'N/A' }}</span>
                                                    {{ $movimentacao->tipo_movimento }}
                                                </p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                {{ $movimentacao->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-8">
                                <i class="fas fa-ship text-gray-300 text-3xl mb-2"></i>
                                <p class="text-gray-500">Nenhuma movimentação recente</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Alertas Recentes -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>Alertas Recentes
                    </h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($alertas_recentes ?? [] as $alerta)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full {{ $alerta->nivel === 'critico' ? 'bg-red-500' : ($alerta->nivel === 'aviso' ? 'bg-yellow-500' : 'bg-blue-500') }} flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-exclamation-triangle h-4 w-4 text-white"></i>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $alerta->titulo }}</p>
                                                <p class="text-sm text-gray-500">{{ Str::limit($alerta->descricao, 50) }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                {{ $alerta->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-8">
                                <i class="fas fa-check-circle text-green-300 text-3xl mb-2"></i>
                                <p class="text-gray-500">Nenhum alerta ativo</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Gráfico de Movimentação por Mês
        const movimentacaoCtx = document.getElementById('movimentacaoChart').getContext('2d');
        new Chart(movimentacaoCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($charts['movimentacao']['labels'] ?? []) !!},
                datasets: [{
                    label: 'Movimentações',
                    data: {!! json_encode($charts['movimentacao']['data'] ?? []) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de Embarcações por Tipo
        const embarcacoesTipoCtx = document.getElementById('embarcacoesTipoChart').getContext('2d');
        new Chart(embarcacoesTipoCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($charts['embarcacoes_tipo']['labels'] ?? []) !!},
                datasets: [{
                    data: {!! json_encode($charts['embarcacoes_tipo']['data'] ?? []) !!},
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-modern-sigp-layout>