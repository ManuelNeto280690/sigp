<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-chart-bar text-white text-xs"></i>
                    </div>
                    {{ __('Relatórios do Sistema') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Gere relatórios detalhados de todos os módulos do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <button onclick="exportAllReports()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-download mr-1 text-xs"></i>Exportar Tudo
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

            <!-- Filtros Globais -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-filter text-indigo-500 mr-2"></i>
                    Filtros Globais
                </h3>
                
                <form method="GET" action="{{ route('relatorios.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                            <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        
                        <div>
                            <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                            <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        
                        <div>
                            <label for="formato" class="block text-sm font-medium text-gray-700 mb-1">Formato</label>
                            <select name="formato" id="formato" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="pdf" {{ request('formato') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="excel" {{ request('formato') == 'excel' ? 'selected' : '' }}>Excel</option>
                                <option value="csv" {{ request('formato') == 'csv' ? 'selected' : '' }}>CSV</option>
                            </select>
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-search mr-1"></i>Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cards de Relatórios por Módulo -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Relatório de Concessionárias -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-building text-white text-lg"></i>
                            </div>
                            <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Concessionárias</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatório de Concessionárias</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório das concessionárias ativas, incluindo dados de contrato, terminais e operações.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total de Concessionárias:</span>
                                <span class="font-medium text-gray-900">{{ $estatisticas['concessionarias']['total'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Ativas:</span>
                                <span class="font-medium text-green-600">{{ $estatisticas['concessionarias']['ativas'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Com Terminais:</span>
                                <span class="font-medium text-blue-600">{{ $estatisticas['concessionarias']['com_terminais'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('concessionarias.relatorio', request()->all()) }}" 
                               class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Visualizar
                            </a>
                            <button onclick="exportReport('concessionarias')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório de Terminais -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-anchor text-white text-lg"></i>
                            </div>
                            <span class="bg-cyan-100 text-cyan-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Terminais</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatório de Terminais</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório dos terminais portuários, incluindo capacidade, equipamentos e utilização.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total de Terminais:</span>
                                <span class="font-medium text-gray-900">{{ $estatisticas['terminais']['total'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Operacionais:</span>
                                <span class="font-medium text-green-600">{{ $estatisticas['terminais']['operacionais'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Em Manutenção:</span>
                                <span class="font-medium text-orange-600">{{ $estatisticas['terminais']['manutencao'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('terminais.relatorio', request()->all()) }}" 
                               class="flex-1 bg-cyan-600 hover:bg-cyan-700 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Visualizar
                            </a>
                            <button onclick="exportReport('terminais')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório de Entrada/Saída de Embarcações -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-exchange-alt text-white text-lg"></i>
                            </div>
                            <span class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Movimentação</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Entrada/Saída de Embarcações</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório de movimentação portuária, incluindo entradas, saídas e tempos de permanência.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total de Movimentos:</span>
                                <span class="font-medium text-gray-900">{{ $estatisticas['movimentos']['total'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Entradas:</span>
                                <span class="font-medium text-green-600">{{ $estatisticas['movimentos']['entradas'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Saídas:</span>
                                <span class="font-medium text-blue-600">{{ $estatisticas['movimentos']['saidas'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('entrada-saida-embarcacao.relatorio', request()->all()) }}" 
                               class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Visualizar
                            </a>
                            <button onclick="exportReport('movimentos')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório de Incidentes -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                            </div>
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Incidentes</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatório de Incidentes</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório detalhado dos incidentes registrados, incluindo gravidade, status e investigações.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total de Incidentes:</span>
                                <span class="font-medium text-gray-900">{{ $estatisticas['incidentes']['total'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Críticos:</span>
                                <span class="font-medium text-red-600">{{ $estatisticas['incidentes']['criticos'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Em Investigação:</span>
                                <span class="font-medium text-yellow-600">{{ $estatisticas['incidentes']['em_investigacao'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('incidentes.relatorio', request()->all()) }}" 
                               class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Visualizar
                            </a>
                            <button onclick="exportReport('incidentes')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório de Alertas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-bell text-white text-lg"></i>
                            </div>
                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Alertas</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatório de Alertas</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório dos alertas do sistema, incluindo prioridades e status de resolução.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total de Alertas:</span>
                                <span class="font-medium text-gray-900">{{ $estatisticas['alertas']['total'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Críticos:</span>
                                <span class="font-medium text-red-600">{{ $estatisticas['alertas']['criticos'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Ativos:</span>
                                <span class="font-medium text-yellow-600">{{ $estatisticas['alertas']['ativos'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('alertas.relatorio', request()->all()) }}" 
                               class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Visualizar
                            </a>
                            <button onclick="exportReport('alertas')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório de Inspeção de Navio -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-search text-white text-lg"></i>
                            </div>
                            <span class="bg-teal-100 text-teal-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Inspeções</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Inspeção de Navio</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório completo das inspeções navais realizadas, incluindo status, resultados e estatísticas.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total de Inspeções:</span>
                                <span class="font-medium text-gray-900">{{ $estatisticas['inspecoes']['total'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Concluídas:</span>
                                <span class="font-medium text-green-600">{{ $estatisticas['inspecoes']['concluidas'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Em Andamento:</span>
                                <span class="font-medium text-yellow-600">{{ $estatisticas['inspecoes']['em_andamento'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('inspecoes.relatorio', request()->all()) }}" 
                               class="flex-1 bg-teal-600 hover:bg-teal-700 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Visualizar
                            </a>
                            <button onclick="exportReport('inspecoes')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório de Inspeção Ambiental -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-leaf text-white text-lg"></i>
                            </div>
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Ambiental</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Inspeção Ambiental</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório das inspeções ambientais e infrações registradas, incluindo medidas de correção aplicadas.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total de Inspeções:</span>
                                <span class="font-medium text-gray-900">{{ $estatisticas['inspecoes_ambientais']['total'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Conformes:</span>
                                <span class="font-medium text-green-600">{{ $estatisticas['inspecoes_ambientais']['conformes'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Com Infrações:</span>
                                <span class="font-medium text-orange-600">{{ $estatisticas['inspecoes_ambientais']['com_infracoes'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('inspecoes-ambientais.relatorio', request()->all()) }}" 
                               class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Visualizar
                            </a>
                            <button onclick="exportReport('inspecoes_ambientais')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório de Pedidos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-file-alt text-white text-lg"></i>
                            </div>
                            <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Pedidos</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatório de Pedidos</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório dos pedidos de operação portuária, incluindo status, aprovações e tempos de processamento.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Total de Pedidos:</span>
                                <span class="font-medium text-gray-900">{{ $estatisticas['pedidos']['total'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Aprovados:</span>
                                <span class="font-medium text-green-600">{{ $estatisticas['pedidos']['aprovados'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Pendentes:</span>
                                <span class="font-medium text-yellow-600">{{ $estatisticas['pedidos']['pendentes'] ?? 0 }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('pedidos.relatorio', request()->all()) }}" 
                               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>Visualizar
                            </a>
                            <button onclick="exportReport('pedidos')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Relatório Consolidado -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-gray-700 to-gray-800 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-chart-pie text-white text-lg"></i>
                            </div>
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Consolidado</span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatório Consolidado</h3>
                        <p class="text-gray-600 text-sm mb-4">Relatório executivo com dados consolidados de todos os módulos do sistema portuário.</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Módulos Ativos:</span>
                                <span class="font-medium text-gray-900">9</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Período:</span>
                                <span class="font-medium text-blue-600">{{ request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : 'Todos' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Última Atualização:</span>
                                <span class="font-medium text-indigo-600">{{ now()->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button onclick="generateConsolidatedReport()" 
                                    class="flex-1 bg-gray-700 hover:bg-gray-800 text-white text-center py-2 px-3 rounded-lg transition duration-200 text-sm font-medium">
                                <i class="fas fa-chart-line mr-1"></i>Gerar
                            </button>
                            <button onclick="exportReport('consolidado')" 
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Relatórios Recentes -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-clock text-indigo-500 mr-2"></i>
                    Relatórios Gerados Recentemente
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Formato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gerado por</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($relatoriosRecentes ?? [] as $relatorio)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-file-alt text-indigo-600 text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $relatorio->tipo }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $relatorio->periodo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ strtoupper($relatorio->formato) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $relatorio->usuario->name ?? 'Sistema' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $relatorio->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Nenhum relatório gerado recentemente
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script>
        function refreshData() {
            window.location.reload();
        }

        function exportReport(tipo) {
            const dataInicio = document.getElementById('data_inicio').value;
            const dataFim = document.getElementById('data_fim').value;
            const formato = document.getElementById('formato').value;
            
            let url = '';
            switch(tipo) {
                case 'embarcacoes':
                    url = '{{ route("embarcacoes.relatorio") }}';
                    break;
                case 'concessionarias':
                    url = '{{ route("concessionarias.relatorio") }}';
                    break;
                case 'terminais':
                    url = '{{ route("terminais.relatorio") }}';
                    break;
                case 'movimentos':
                    url = '{{ route("entrada-saida-embarcacao.relatorio") }}';
                    break;
                case 'incidentes':
                    url = '{{ route("incidentes.relatorio") }}';
                    break;
                case 'alertas':
                    url = '{{ route("alertas.relatorio") }}';
                    break;
                case 'inspecoes':
                    url = '{{ route("inspecoes.relatorio") }}';
                    break;
                case 'inspecoes_ambientais':
                    url = '{{ route("inspecoes-ambientais.relatorio") }}';
                    break;
                case 'pedidos':
                    url = '{{ route("pedidos.relatorio") }}';
                    break;
                case 'consolidado':
                    url = '{{ route("relatorios.consolidado") }}';
                    break;
            }
            
            const params = new URLSearchParams();
            if (dataInicio) params.append('data_inicio', dataInicio);
            if (dataFim) params.append('data_fim', dataFim);
            params.append('formato', formato);
            
            window.open(`${url}?${params.toString()}`, '_blank');
        }

        function exportAllReports() {
            if (!confirm('Deseja gerar todos os relatórios? Esta operação pode demorar alguns minutos.')) {
                return;
            }
            
            const tipos = ['embarcacoes', 'concessionarias', 'terminais', 'movimentos', 'incidentes', 'alertas', 'inspecoes', 'inspecoes_ambientais', 'pedidos'];
            tipos.forEach((tipo, index) => {
                setTimeout(() => {
                    exportReport(tipo);
                }, index * 2000); // Delay de 2 segundos entre cada relatório
            });
        }

        function generateConsolidatedReport() {
            const dataInicio = document.getElementById('data_inicio').value;
            const dataFim = document.getElementById('data_fim').value;
            const formato = document.getElementById('formato').value;
            
            if (!dataInicio || !dataFim) {
                alert('Por favor, selecione o período para gerar o relatório consolidado.');
                return;
            }
            
            const params = new URLSearchParams();
            params.append('data_inicio', dataInicio);
            params.append('data_fim', dataFim);
            params.append('formato', formato);
            
            window.open(`{{ route('relatorios.consolidado') }}?${params.toString()}`, '_blank');
        }

        // Auto-refresh a cada 5 minutos
        setInterval(function() {
            const now = new Date();
            document.querySelector('[data-last-update]')?.textContent = now.toLocaleString('pt-BR');
        }, 300000);
    </script>
</x-app-layout>