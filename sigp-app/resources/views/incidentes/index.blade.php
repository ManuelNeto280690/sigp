<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                        <i class="fas fa-exclamation-triangle text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Incidentes') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Controle e monitore todos os incidentes do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-sync-alt mr-2 text-sm"></i>Atualizar
                </button>
                
                <!-- Botões de Relatório -->
               
                
                @can('incidentes.create')
                <a href="{{ route('incidentes.create') }}" class="text-white font-bold py-2 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-sm" style="background: #3b82f6; hover:background: #2563eb;">
                    <i class="fas fa-plus mr-2"></i>
                    Registrar Incidente
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Seção de Mensagens de Erro e Sucesso -->
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

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 mb-2">Foram encontrados os seguintes erros:</h3>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Estatísticas Compactas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe);">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: #3b82f6;">Total de Incidentes</p>
                            <p class="text-2xl font-bold mt-1" style="color: #1e40af;">{{ $incidentes->total() }}</p>
                            <p class="text-xs mt-1" style="color: #3b82f6;">Registrados no sistema</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" style="background: #3b82f6;">
                            <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-600 text-xs font-semibold uppercase tracking-wide">Abertos</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $incidentes->where('status', 'aberto')->count() }}</p>
                            <p class="text-yellow-600 text-xs mt-1">Aguardando investigação</p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Em Investigação</p>
                            <p class="text-2xl font-bold text-orange-900 mt-1">{{ $incidentes->where('status', 'investigando')->count() }}</p>
                            <p class="text-orange-600 text-xs mt-1">Sendo analisados</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-search text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Resolvidos</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $incidentes->where('status', 'resolvido')->count() }}</p>
                            <p class="text-green-600 text-xs mt-1">Finalizados</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-check text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter mr-2" style="color: #3b82f6;"></i>
                        Filtros de Pesquisa
                    </h3>
                </div>
                <div class="p-4">
                    <form method="GET" action="{{ route('incidentes.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Pesquisa Geral -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Pesquisar</label>
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Número, título, descrição..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm"
                                       style="focus:ring-color: #3b82f6;">
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <select name="tipo" 
                                        id="tipo"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm"
                                        style="focus:ring-color: #3b82f6;">
                                    <option value="">Todos os tipos</option>
                                    <option value="seguranca" {{ request('tipo') == 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="ambiental" {{ request('tipo') == 'ambiental' ? 'selected' : '' }}>Ambiental</option>
                                    <option value="operacional" {{ request('tipo') == 'operacional' ? 'selected' : '' }}>Operacional</option>
                                    <option value="equipamento" {{ request('tipo') == 'equipamento' ? 'selected' : '' }}>Equipamento</option>
                                    <option value="outros" {{ request('tipo') == 'outros' ? 'selected' : '' }}>Outros</option>
                                </select>
                            </div>

                            <!-- Gravidade -->
                            <div>
                                <label for="gravidade" class="block text-sm font-medium text-gray-700 mb-1">Gravidade</label>
                                <select name="gravidade" 
                                        id="gravidade"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm"
                                        style="focus:ring-color: #3b82f6;">
                                    <option value="">Todas as gravidades</option>
                                    <option value="baixa" {{ request('gravidade') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                    <option value="media" {{ request('gravidade') == 'media' ? 'selected' : '' }}>Média</option>
                                    <option value="alta" {{ request('gravidade') == 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="critica" {{ request('gravidade') == 'critica' ? 'selected' : '' }}>Crítica</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" 
                                        id="status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm"
                                        style="focus:ring-color: #3b82f6;">
                                    <option value="">Todos os status</option>
                                    <option value="aberto" {{ request('status') == 'aberto' ? 'selected' : '' }}>Aberto</option>
                                    <option value="investigando" {{ request('status') == 'investigando' ? 'selected' : '' }}>Em Investigação</option>
                                    <option value="resolvido" {{ request('status') == 'resolvido' ? 'selected' : '' }}>Resolvido</option>
                                    <option value="fechado" {{ request('status') == 'fechado' ? 'selected' : '' }}>Fechado</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Data Início -->
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                                <input type="date" 
                                       name="data_inicio" 
                                       id="data_inicio"
                                       value="{{ request('data_inicio') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm"
                                       style="focus:ring-color: #3b82f6;">
                            </div>

                            <!-- Data Fim -->
                            <div>
                                <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                                <input type="date" 
                                       name="data_fim" 
                                       id="data_fim"
                                       value="{{ request('data_fim') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm"
                                       style="focus:ring-color: #3b82f6;">
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div class="flex space-x-2">
                                <button type="submit" 
                                        class="text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm shadow-lg hover:shadow-xl"
                                        style="background: #3b82f6;">
                                    <i class="fas fa-search mr-2"></i>Filtrar
                                </button>
                                <a href="{{ route('incidentes.index') }}" 
                                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                                    <i class="fas fa-times mr-2"></i>Limpar
                                </a>
                            </div>
                            <div class="text-xs text-gray-500">
                                Mostrando {{ $incidentes->count() }} de {{ $incidentes->total() }} incidentes
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Incidentes -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list mr-2" style="color: #3b82f6;"></i>
                            Lista de Incidentes
                        </h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">{{ $incidentes->count() }} incidentes encontrados</span>
                             <a href="{{ route('incidentes.relatorioFiltrado', array_merge(request()->query(), ['formato' => 'pdf'])) }}" 
                   class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm shadow-md">
                    <i class="fas fa-file-pdf mr-2 text-sm"></i>Relatório PDF
                </a>
                <a href="{{ route('incidentes.relatorioFiltrado', array_merge(request()->query(), ['formato' => 'excel'])) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm shadow-md">
                    <i class="fas fa-file-excel mr-2 text-sm"></i>Relatório Excel
                </a>
                            @can('incidentes.create')
                            <a href="{{ route('incidentes.create') }}" 
                               class="text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs shadow-md hover:shadow-lg"
                               style="background: #3b82f6;">
                                <i class="fas fa-plus mr-1"></i>Registrar
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>

                @if($incidentes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Incidente
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo / Gravidade
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Local / Data
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Responsável
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($incidentes as $incidente)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                                        @if($incidente->gravidade == 'critica') bg-red-100 text-red-600
                                                        @elseif($incidente->gravidade == 'alta') bg-orange-100 text-orange-600
                                                        @elseif($incidente->gravidade == 'media') bg-yellow-100 text-yellow-600
                                                        @else bg-green-100 text-green-600
                                                        @endif">
                                                        <i class="fas fa-exclamation-triangle text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $incidente->numero_incidente }}
                                                    </div>
                                                    <div class="text-sm text-gray-600 max-w-xs truncate">
                                                        {{ $incidente->titulo }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @if($incidente->tipo == 'seguranca') bg-red-100 text-red-800
                                                    @elseif($incidente->tipo == 'ambiental') bg-green-100 text-green-800
                                                    @elseif($incidente->tipo == 'operacional') text-white
                                                    @elseif($incidente->tipo == 'equipamento') bg-purple-100 text-purple-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif"
                                                    @if($incidente->tipo == 'operacional') style="background: #3b82f6;" @endif>
                                                    @if($incidente->tipo == 'seguranca') Segurança
                                                    @elseif($incidente->tipo == 'ambiental') Ambiental
                                                    @elseif($incidente->tipo == 'operacional') Operacional
                                                    @elseif($incidente->tipo == 'equipamento') Equipamento
                                                    @else Outros
                                                    @endif
                                                </span>
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                        @if($incidente->gravidade == 'critica') bg-red-100 text-red-800
                                                        @elseif($incidente->gravidade == 'alta') bg-orange-100 text-orange-800
                                                        @elseif($incidente->gravidade == 'media') bg-yellow-100 text-yellow-800
                                                        @else bg-green-100 text-green-800
                                                        @endif">
                                                        @if($incidente->gravidade == 'critica') Crítica
                                                        @elseif($incidente->gravidade == 'alta') Alta
                                                        @elseif($incidente->gravidade == 'media') Média
                                                        @else Baixa
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $incidente->local_ocorrencia }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $incidente->data_ocorrencia->format('d/m/Y H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($incidente->status == 'aberto') bg-yellow-100 text-yellow-800
                                                @elseif($incidente->status == 'investigando') bg-orange-100 text-orange-800
                                                @elseif($incidente->status == 'resolvido') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                @if($incidente->status == 'aberto') Aberto
                                                @elseif($incidente->status == 'investigando') Em Investigação
                                                @elseif($incidente->status == 'resolvido') Resolvido
                                                @else Fechado
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $incidente->user->name ?? 'N/A' }}
                                            </div>
                                            @if($incidente->responsavelInvestigacao)
                                                <div class="text-sm text-gray-500">
                                                    Investigador: {{ $incidente->responsavelInvestigacao->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                               
                                                <a href="{{ route('incidentes.show', $incidente) }}" 
                                                   class="hover:text-blue-900 transition-colors duration-200"
                                                   style="color: #3b82f6;"
                                                   title="Visualizar">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>


                                                <a href="{{ route('incidentes.exportPdf', $incidente) }}" 
                                               class="hover:text-blue-900 transition-colors duration-200" 
                                               style="color:rgb(235, 164, 13);"
                                               title="Baixar PDF">
                                                <i class="fas fa-file-pdf text-xs"></i>
                                            </a>
                                               
                                                
                                                @can('incidentes.edit')
                                                <a href="{{ route('incidentes.edit', $incidente) }}" 
                                                   class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                   title="Editar">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                                @endcan
                                                
                                                @can('incidentes.delete')
                                                <button type="button" 
                                                        data-incidente-id="{{ $incidente->id }}"
                                                        data-incidente-info="{{ $incidente->numero_incidente }} - {{ $incidente->titulo }}"
                                                        onclick="confirmDelete(this.dataset.incidenteId, this.dataset.incidenteInfo)" 
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                        title="Excluir">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                        {{ $incidentes->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum incidente encontrado</h3>
                        <p class="text-gray-500 mb-4">Não há incidentes que correspondam aos filtros aplicados.</p>
                        @can('incidentes.create')
                        <a href="{{ route('incidentes.create') }}" 
                           class="text-white font-medium py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl inline-flex items-center"
                           style="background: #3b82f6;">
                            <i class="fas fa-plus mr-2"></i>
                            Registrar Primeiro Incidente
                        </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4 mb-2">Confirmar Exclusão</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Tem certeza que deseja excluir o incidente <span id="incidenteInfo" class="font-semibold"></span>?
                    <br><span class="text-xs text-red-600 mt-2 block">Esta ação não pode ser desfeita.</span>
                </p>
                <div class="flex justify-center space-x-4">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition duration-200 shadow-lg hover:shadow-xl">
                            Excluir Incidente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshData() {
            window.location.reload();
        }

        function confirmDelete(id, incidenteInfo) {
            const modal = document.getElementById('deleteModal');
            const infoSpan = document.getElementById('incidenteInfo');
            const deleteForm = document.getElementById('deleteForm');
            
            if (modal && infoSpan && deleteForm) {
                infoSpan.textContent = incidenteInfo;
                deleteForm.action = `/incidentes/${id}`;
                modal.classList.remove('hidden');
            }
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Aguardar o DOM carregar antes de adicionar event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Fechar modal ao clicar fora dele
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeDeleteModal();
                    }
                });
            }

            // Adicionar estilo de foco personalizado para inputs
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#3b82f6';
                    this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                });
                input.addEventListener('blur', function() {
                    this.style.borderColor = '#d1d5db';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</x-app-layout>