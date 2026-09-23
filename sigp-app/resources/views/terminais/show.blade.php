<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-warehouse text-white text-xs"></i>
                    </div>
                    {{ $terminal->nome }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Detalhes do terminal portuário</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('terminais.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
                @can('update', $terminal)
                <a href="{{ route('terminais.edit', $terminal->id) }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #8b5cf6;">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Terminal
                </a>
                @endcan
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

            <!-- Estatísticas do Terminal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Movimentos Este Mês</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['movimentos_mes'] }}</p>
                            <p class="text-blue-600 text-xs mt-1">Operações realizadas</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-exchange-alt text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Embarcações Este Mês</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['embarcacoes_mes'] }}</p>
                            <p class="text-green-600 text-xs mt-1">Diferentes embarcações</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-ship text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Capacidade Utilizada</p>
                            <p class="text-2xl font-bold text-orange-900 mt-1">{{ number_format($stats['capacidade_utilizada'], 0, ',', '.') }}</p>
                            <p class="text-orange-600 text-xs mt-1">Toneladas este mês</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-weight-hanging text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-xs font-semibold uppercase tracking-wide">Ocupação</p>
                            <p class="text-2xl font-bold text-purple-900 mt-1">{{ number_format($stats['percentual_ocupacao'], 1) }}%</p>
                            <p class="text-purple-600 text-xs mt-1">Da capacidade máxima</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-chart-pie text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Básicas -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                        Informações Básicas
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Terminal</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">{{ $terminal->nome }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">{{ $terminal->codigo }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $terminal->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $terminal->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                {{ $terminal->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">{{ ucfirst($terminal->tipo ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">{{ $terminal->descricao ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Concessionária</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">{{ $terminal->concessionaria->nome ?? 'N/A' }}</p>
                        </div>
                      
                    </div>
                </div>
            </div>

            <!-- Capacidade e Especificações -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-cogs text-purple-600 mr-2"></i>
                        Capacidade e Especificações
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacidade de Armazanamento</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                             
                                    {{ number_format($terminal->capacidade_armazenagem, 0, ',', '.') }} toneladas
                            
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Área Total</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                @if($terminal->area_total)
                                    {{ number_format($terminal->area_total, 0, ',', '.') }} m²
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Área Operacional</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                              
                                    {{ $terminal->area_operacional }} metros
                               
                            </p>
                        </div>

                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Números de Bercos</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                              
                                    {{ $terminal->numero_bercos }} 
                               
                            </p>
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Calado Máximo</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                              
                                    {{ $terminal->calado_maximo }} mentros
                               
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contatos e Informações Adicionais - REMOVER ESTA SEÇÃO -->
            @if($terminal->observacoes)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clipboard-list text-purple-600 mr-2"></i>
                        Observações
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">{{ $terminal->observacoes }}</p>
                </div>
            </div>
            @endif

            <!-- Equipamentos e Observações -->
            @if($terminal->equipamentos || $terminal->observacoes)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Adicionais
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6">
                            @if($terminal->equipamentos)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Equipamentos</label>
                                <div class="bg-gray-50 rounded-lg px-3 py-2">
                                    @if(is_array($terminal->equipamentos))
                                        <ul class="list-disc list-inside text-sm text-gray-900">
                                            @foreach($terminal->equipamentos as $equipamento)
                                                <li>{{ $equipamento }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-900">{{ $terminal->equipamentos }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($terminal->observacoes)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                                <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">{{ $terminal->observacoes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Movimentos Recentes -->
            @if($terminal->movimentosTerminais->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-exchange-alt text-purple-600 mr-2"></i>
                        Movimentos Recentes
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($terminal->movimentosTerminais as $movimento)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $movimento->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $movimento->produto->nome ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $movimento->tipo == 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($movimento->tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($movimento->quantidade, 2, ',', '.') }} t
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Embarcações Recentes -->
            @if($terminal->entradasSaidas->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <i class="fas fa-ship text-purple-600 mr-2"></i>
                        Embarcações Recentes
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Embarcação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($terminal->entradasSaidas as $movimento)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $movimento->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center mr-2">
                                            <i class="fas fa-ship text-blue-600 text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $movimento->embarcacao->nome ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ $movimento->embarcacao->imo ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $movimento->tipo_movimento == 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($movimento->tipo_movimento) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($movimento->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Datas de Registro -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-calendar text-purple-600 mr-2"></i>
                        Informações de Registro
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Criação</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                {{ $terminal->created_at ? $terminal->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Última Atualização</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                {{ $terminal->updated_at ? $terminal->updated_at->format('d/m/Y H:i:s') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>