<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Relatório de Inspeções') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Período: {{ \Carbon\Carbon::parse($periodo['inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($periodo['fim'])->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('relatorios.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out">
                        <i class="fas fa-download mr-2"></i>
                        Exportar
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <a href="{{ route('inspecoes.relatorio', array_merge($filtros, ['formato' => 'pdf', 'export' => 1])) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">
                            <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                            Exportar PDF
                        </a>
                        <a href="{{ route('inspecoes.relatorio', array_merge($filtros, ['formato' => 'excel', 'export' => 1])) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-excel text-green-500 mr-3"></i>
                            Exportar Excel
                        </a>
                        <a href="{{ route('inspecoes.relatorio', array_merge($filtros, ['formato' => 'csv', 'export' => 1])) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-b-lg">
                            <i class="fas fa-file-csv text-blue-500 mr-3"></i>
                            Exportar CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-filter text-gray-500 mr-2"></i>
                        <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
                    </div>
                    <form method="GET" action="{{ route('inspecoes.relatorio') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                                <input type="date" id="data_inicio" name="data_inicio" 
                                       value="{{ $filtros['data_inicio'] ?? $periodo['inicio'] }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                                <input type="date" id="data_fim" name="data_fim" 
                                       value="{{ $filtros['data_fim'] ?? $periodo['fim'] }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select id="status" name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Todos os Status</option>
                                    <option value="pendente" {{ ($filtros['status'] ?? '') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                                    <option value="em andamento" {{ ($filtros['status'] ?? '') == 'em andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="concluída" {{ ($filtros['status'] ?? '') == 'concluída' ? 'selected' : '' }}>Concluída</option>
                                    <option value="aprovada" {{ ($filtros['status'] ?? '') == 'aprovada' ? 'selected' : '' }}>Aprovada</option>
                                </select>
                            </div>
                            <div>
                                <label for="tipo_inspecao" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select id="tipo_inspecao" name="tipo_inspecao" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Todos os Tipos</option>
                                    <option value="rotina" {{ ($filtros['tipo_inspecao'] ?? '') == 'rotina' ? 'selected' : '' }}>Rotina</option>
                                    <option value="especial" {{ ($filtros['tipo_inspecao'] ?? '') == 'especial' ? 'selected' : '' }}>Especial</option>
                                    <option value="emergencial" {{ ($filtros['tipo_inspecao'] ?? '') == 'emergencial' ? 'selected' : '' }}>Emergencial</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-150 ease-in-out">
                                    <i class="fas fa-search mr-2"></i>
                                    Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total de Inspeções -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-search text-blue-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total de Inspeções</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($estatisticas['total']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Concluídas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Concluídas</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($estatisticas['concluidas']) }}</p>
                                @if($estatisticas['total'] > 0)
                                    <p class="text-sm text-green-600">{{ number_format(($estatisticas['concluidas'] / $estatisticas['total']) * 100, 1) }}%</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Em Andamento -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Em Andamento</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($estatisticas['em_andamento']) }}</p>
                                @if($estatisticas['total'] > 0)
                                    <p class="text-sm text-yellow-600">{{ number_format(($estatisticas['em_andamento'] / $estatisticas['total']) * 100, 1) }}%</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aprovadas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-thumbs-up text-purple-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Aprovadas</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($estatisticas['aprovadas']) }}</p>
                                @if($estatisticas['total'] > 0)
                                    <p class="text-sm text-purple-600">{{ number_format(($estatisticas['aprovadas'] / $estatisticas['total']) * 100, 1) }}%</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Gráfico por Tipo -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Distribuição por Tipo</h3>
                        <div class="h-64">
                            <canvas id="tipoChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráfico por Mês -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Inspeções por Mês</h3>
                        <div class="h-64">
                            <canvas id="mesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Inspeções -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Lista de Inspeções</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">{{ $inspecoes->count() }} de {{ $estatisticas['total'] }} inspeções</span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="inspecoesTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Embarcação</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inspetor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($inspecoes as $inspecao)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $inspecao->numero_inspecao }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $inspecao->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($inspecao->tipo_inspecao) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $inspecao->embarcacao->nome ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $inspecao->local_inspecao }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $inspecao->inspetor->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @switch($inspecao->status)
                                            @case('pendente')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Pendente
                                                </span>
                                                @break
                                            @case('em andamento')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-play mr-1"></i>
                                                    Em Andamento
                                                </span>
                                                @break
                                            @case('concluída')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Concluída
                                                </span>
                                                @break
                                            @case('aprovada')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-thumbs-up mr-1"></i>
                                                    Aprovada
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ ucfirst($inspecao->status) }}
                                                </span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($inspecao->resultado)
                                            @switch($inspecao->resultado)
                                                @case('aprovado')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Aprovado
                                                    </span>
                                                    @break
                                                @case('reprovado')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-times-circle mr-1"></i>
                                                        Reprovado
                                                    </span>
                                                    @break
                                                @case('pendente')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Pendente
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ ucfirst($inspecao->resultado) }}
                                                    </span>
                                            @endswitch
                                        @else
                                            <span class="text-gray-400 text-sm">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-search text-gray-300 text-4xl mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma inspeção encontrada</h3>
                                            <p class="text-gray-500">Não há inspeções no período selecionado com os filtros aplicados.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configuração dos gráficos
        Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
        Chart.defaults.color = '#6B7280';

        // Gráfico por Tipo
        const tipoCtx = document.getElementById('tipoChart').getContext('2d');
        const tipoChart = new Chart(tipoCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($estatisticas['por_tipo']->toArray())) !!},
                datasets: [{
                    data: {!! json_encode(array_values($estatisticas['por_tipo']->toArray())) !!},
                    backgroundColor: [
                        '#3B82F6', // Blue
                        '#10B981', // Green
                        '#F59E0B', // Yellow
                        '#EF4444', // Red
                        '#8B5CF6'  // Purple
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
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
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false
                    }
                }
            }
        });

        // Gráfico por Mês
        const mesCtx = document.getElementById('mesChart').getContext('2d');
        const mesChart = new Chart(mesCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($estatisticas['por_mes']->toArray())) !!},
                datasets: [{
                    label: 'Inspeções',
                    data: {!! json_encode(array_values($estatisticas['por_mes']->toArray())) !!},
                    backgroundColor: '#3B82F6',
                    borderColor: '#2563EB',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1
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

        // Inicializar DataTable se houver dados
        @if($inspecoes->count() > 0)
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#inspecoesTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                    },
                    order: [[ 1, 'desc' ]],
                    pageLength: 25,
                    responsive: true,
                    dom: '<"flex justify-between items-center mb-4"<"flex items-center"l><"flex items-center"f>>rtip',
                    columnDefs: [
                        { orderable: false, targets: [2, 6, 7] }
                    ]
                });
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>