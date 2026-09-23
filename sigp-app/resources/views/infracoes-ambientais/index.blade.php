<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-blue-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-leaf text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Infrações Ambientais') }}
                </h2>
                <p class="text-blue-600 text-xs mt-1">Controle e monitore todas as infrações ambientais do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Botões de Relatório -->
               
                
                <button onclick="refreshData()" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                @can('infracoes-ambientais.create')
                <a href="{{ route('infracoes-ambientais.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Nova Infração
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Seção de Mensagens de Erro e Sucesso -->
            @if(session('success'))
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-blue-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">{{ session('success') }}</p>
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
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Infrações</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $infracoes->total() }}</p>
                            <p class="text-blue-600 text-xs mt-1">Registradas no sistema</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-leaf text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-600 text-xs font-semibold uppercase tracking-wide">Registradas</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $infracoes->where('status', 'registrada')->count() }}</p>
                            <p class="text-yellow-600 text-xs mt-1">Aguardando notificação</p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Notificadas</p>
                            <p class="text-2xl font-bold text-orange-900 mt-1">{{ $infracoes->where('status', 'notificada')->count() }}</p>
                            <p class="text-orange-600 text-xs mt-1">Aguardando defesa</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-bell text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!--div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-600 text-xs font-semibold uppercase tracking-wide">Valor Total Multas</p>
                            <p class="text-2xl font-bold text-red-900 mt-1">Kz {{ number_format($infracoes->sum('valor_multa'), 2, ',', '.') }}</p>
                            <p class="text-red-600 text-xs mt-1">Em multas aplicadas</p>
                        </div>
                        <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-dollar-sign text-white text-sm"></i>
                        </div>
                    </div>
                </div-->
            </div>

            <!-- Filtros Avançados -->
            <div class="bg-white rounded-lg shadow-sm border border-blue-200">
                <div class="p-4 border-b border-blue-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-blue-900 flex items-center">
                            <i class="fas fa-filter text-blue-500 mr-2"></i>
                            Filtros Avançados
                        </h3>
                    </div>
                </div>
                
                <div class="p-4 bg-blue-50">
                    <form method="GET" action="{{ route('infracoes-ambientais.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Busca -->
                            <div>
                                <label for="search" class="block text-xs font-medium text-blue-700 mb-1">Buscar</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Número do auto, descrição..."
                                       class="w-full px-3 py-2 border border-blue-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-xs font-medium text-blue-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-blue-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todos os Status</option>
                                    <option value="registrada" {{ request('status') == 'registrada' ? 'selected' : '' }}>Registrada</option>
                                    <option value="notificada" {{ request('status') == 'notificada' ? 'selected' : '' }}>Notificada</option>
                                    <option value="contestada" {{ request('status') == 'contestada' ? 'selected' : '' }}>Contestada</option>
                                    <option value="paga" {{ request('status') == 'paga' ? 'selected' : '' }}>Paga</option>
                                    <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                            </div>

                            <!-- Tipo de Infração -->
                            <div>
                                <label for="tipo_infracao" class="block text-xs font-medium text-blue-700 mb-1">Tipo de Infração</label>
                                <select name="tipo_infracao" id="tipo_infracao" class="w-full px-3 py-2 border border-blue-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todos os Tipos</option>
                                    <option value="descarga_irregular" {{ request('tipo_infracao') == 'descarga_irregular' ? 'selected' : '' }}>Descarga Irregular</option>
                                    <option value="poluicao_atmosferica" {{ request('tipo_infracao') == 'poluicao_atmosferica' ? 'selected' : '' }}>Poluição Atmosférica</option>
                                    <option value="residuos_solidos" {{ request('tipo_infracao') == 'residuos_solidos' ? 'selected' : '' }}>Resíduos Sólidos</option>
                                    <option value="ruido_excessivo" {{ request('tipo_infracao') == 'ruido_excessivo' ? 'selected' : '' }}>Ruído Excessivo</option>
                                    <option value="outras" {{ request('tipo_infracao') == 'outras' ? 'selected' : '' }}>Outras</option>
                                </select>
                            </div>

                            <!-- Gravidade -->
                            <div>
                                <label for="gravidade" class="block text-xs font-medium text-blue-700 mb-1">Gravidade</label>
                                <select name="gravidade" id="gravidade" class="w-full px-3 py-2 border border-blue-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todas as Gravidades</option>
                                    <option value="leve" {{ request('gravidade') == 'leve' ? 'selected' : '' }}>Leve</option>
                                    <option value="media" {{ request('gravidade') == 'media' ? 'selected' : '' }}>Média</option>
                                    <option value="grave" {{ request('gravidade') == 'grave' ? 'selected' : '' }}>Grave</option>
                                    <option value="gravissima" {{ request('gravidade') == 'gravissima' ? 'selected' : '' }}>Gravíssima</option>
                                </select>
                            </div>
                            
                            <!-- Data Início -->
                            <div>
                                <label for="data_inicio" class="block text-xs font-medium text-blue-700 mb-1">Data Início</label>
                                <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}"
                                       class="w-full px-3 py-2 border border-blue-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Data Fim -->
                            <div>
                                <label for="data_fim" class="block text-xs font-medium text-blue-700 mb-1">Data Fim</label>
                                <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}"
                                       class="w-full px-3 py-2 border border-blue-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Embarcação -->
                            <div>
                                <label for="embarcacao_id" class="block text-xs font-medium text-blue-700 mb-1">Embarcação</label>
                                <select name="embarcacao_id" id="embarcacao_id" class="w-full px-3 py-2 border border-blue-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todas as Embarcações</option>
                                    @foreach(\App\Models\Embarcacao::orderBy('nome')->get() as $embarcacao)
                                        <option value="{{ $embarcacao->id }}" {{ request('embarcacao_id') == $embarcacao->id ? 'selected' : '' }}>
                                            {{ $embarcacao->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Terminal -->
                            <div>
                                <label for="terminal_id" class="block text-xs font-medium text-blue-700 mb-1">Terminal</label>
                                <select name="terminal_id" id="terminal_id" class="w-full px-3 py-2 border border-blue-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todos os Terminais</option>
                                    @foreach(\App\Models\Terminal::orderBy('nome')->get() as $terminal)
                                        <option value="{{ $terminal->id }}" {{ request('terminal_id') == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-blue-200">
                            <div class="flex items-center space-x-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-xs flex items-center">
                                    <i class="fas fa-search mr-2"></i>
                                    Filtrar
                                </button>
                                <a href="{{ route('infracoes-ambientais.index') }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-2 px-4 rounded-lg transition duration-200 text-xs flex items-center">
                                    <i class="fas fa-times mr-2"></i>
                                    Limpar
                                </a>
                            </div>
                            <div class="text-xs text-blue-500">
                                Mostrando {{ $infracoes->count() }} de {{ $infracoes->total() }} infrações
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Infrações Ambientais -->
            <div class="bg-white rounded-lg shadow-sm border border-blue-200 overflow-hidden">
                <div class="p-4 border-b border-blue-200 bg-blue-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-blue-900 flex items-center">
                            <i class="fas fa-list text-blue-500 mr-2"></i>
                            Lista de Infrações Ambientais
                        </h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-blue-500">{{ $infracoes->count() }} infrações encontradas</span>
                             <a href="{{ route('infracoes-ambientais.relatorio-filtrado', array_merge(request()->all(), ['formato' => 'pdf'])) }}" 
                   class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                </a>
                <a href="{{ route('infracoes-ambientais.relatorio-filtrado', array_merge(request()->all(), ['formato' => 'excel'])) }}" 
                   class="bg-green-100 hover:bg-green-200 text-green-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                </a>
                             @can('incidentes.create')
                            <a href="{{ route('infracoes-ambientais.create') }}" 
                               class="text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs shadow-md hover:shadow-lg"
                               style="background: #3b82f6;">
                                <i class="fas fa-plus mr-1"></i>Registrar infrações ambientais
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>

                @if($infracoes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        Auto de Infração
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        Tipo / Gravidade
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        Local / Data
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <!--th class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        Valor Multa
                                    </th-->
                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-blue-200">
                                @foreach($infracoes as $infracao)
                                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center
                                                        @if($infracao->gravidade == 'gravissima') bg-red-100 text-red-600
                                                        @elseif($infracao->gravidade == 'grave') bg-orange-100 text-orange-600
                                                        @elseif($infracao->gravidade == 'media') bg-yellow-100 text-yellow-600
                                                        @else bg-blue-100 text-blue-600 @endif">
                                                        <i class="fas fa-leaf text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-blue-900">
                                                        {{ $infracao->numero_auto }}
                                                    </div>
                                                    <div class="text-xs text-blue-500 mt-1">
                                                        {{ Str::limit($infracao->descricao_infracao, 50) }}
                                                    </div>
                                                    @if($infracao->embarcacao)
                                                        <div class="text-xs text-blue-600 mt-1 flex items-center">
                                                            <i class="fas fa-ship mr-1"></i>
                                                            {{ $infracao->embarcacao->nome }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @if($infracao->tipo_infracao == 'descarga_irregular') bg-red-100 text-red-800
                                                    @elseif($infracao->tipo_infracao == 'poluicao_atmosferica') bg-purple-100 text-purple-800
                                                    @elseif($infracao->tipo_infracao == 'residuos_solidos') bg-brown-100 text-brown-800
                                                    @elseif($infracao->tipo_infracao == 'ruido_excessivo') bg-orange-100 text-orange-800
                                                    @elseif($infracao->tipo_infracao == 'poluicao_agua') bg-blue-100 text-blue-800
                                                    @elseif($infracao->tipo_infracao == 'vazamento') bg-blue-100 text-blue-800
                                                    @elseif($infracao->tipo_infracao == 'outros') bg-yellow-100 text-yellow-800
                                                    @else bg-blue-100 text-blue-800 @endif">
                                                    {{ $infracao->getTipoInfracaoLabel() }}
                                                </span>
                                                <div>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                        @if($infracao->gravidade == 'gravissima') bg-red-100 text-red-800
                                                        @elseif($infracao->gravidade == 'grave') bg-orange-100 text-orange-800
                                                        @elseif($infracao->gravidade == 'media') bg-yellow-100 text-yellow-800
                                                        @else bg-blue-100 text-blue-800 @endif">
                                                        {{ ucfirst($infracao->gravidade) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-blue-900">
                                                <i class="fas fa-map-marker-alt text-blue-400 mr-1"></i>
                                                {{ Str::limit($infracao->local_infracao, 30) }}
                                            </div>
                                            <div class="text-xs text-blue-500 mt-1">
                                                <i class="fas fa-calendar text-blue-400 mr-1"></i>
                                                {{ $infracao->data_infracao->format('d/m/Y H:i') }}
                                            </div>
                                            @if($infracao->prazo_regularizacao)
                                                <div class="text-xs text-orange-600 mt-1">
                                                    <i class="fas fa-clock text-orange-400 mr-1"></i>
                                                    Prazo: {{ $infracao->prazo_regularizacao->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                                @if($infracao->status == 'registrada') bg-yellow-100 text-yellow-800
                                                @elseif($infracao->status == 'notificada') bg-blue-100 text-blue-800
                                                @elseif($infracao->status == 'contestada') bg-orange-100 text-orange-800
                                                @elseif($infracao->status == 'paga') bg-blue-100 text-blue-800
                                                @else bg-red-100 text-red-800 @endif">
                                                @if($infracao->status == 'registrada')
                                                    <i class="fas fa-clock mr-1"></i>Registrada
                                                @elseif($infracao->status == 'notificada')
                                                    <i class="fas fa-bell mr-1"></i>Notificada
                                                @elseif($infracao->status == 'contestada')
                                                    <i class="fas fa-gavel mr-1"></i>Contestada
                                                @elseif($infracao->status == 'paga')
                                                    <i class="fas fa-check mr-1"></i>Paga
                                                @else
                                                    <i class="fas fa-times mr-1"></i>Cancelada
                                                @endif
                                            </span>
                                        </td>
                                        <!--td class="px-4 py-4 whitespace-nowrap">
                                            @if($infracao->valor_multa)
                                                <div class="text-sm font-medium text-blue-900">
                                                    R$ {{ number_format($infracao->valor_multa, 2, ',', '.') }}
                                                </div>
                                            @else
                                                <span class="text-xs text-blue-500">Não definido</span>
                                            @endif
                                        </td-->
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                            
                                                <a href="{{ route('infracoes-ambientais.show', $infracao) }}" 
                                                   class="text-blue-600 hover:text-blue-900 transition-colors duration-200" 
                                                   title="Visualizar">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                               
                                                 <a href="{{ route('infracoes-ambientais.exportPdf', $infracao) }}" 
                                               class="hover:text-blue-900 transition-colors duration-200" 
                                               style="color:rgb(235, 164, 13);"
                                               title="Baixar PDF">
                                                <i class="fas fa-file-pdf text-xs"></i>
                                            </a>
                                                
                                              
                                                <a href="{{ route('infracoes-ambientais.edit', $infracao) }}" 
                                                   class="text-blue-600 hover:text-blue-900 transition-colors duration-200" 
                                                   title="Editar">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                            
                                                
                                             
                                                <!--button onclick="confirmDelete('{{ $infracao->id }}', '{{ $infracao->numero_auto }}')" 
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                                                        title="Excluir">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button-->
                                                @can('admin')
                                                 <button type="button" 
                                                        data-incidente-id="{{ $infracao->id }}"
                                                        data-incidente-info="{{ $infracao->numero_auto }}"
                                                        onclick="confirmDelete(this.dataset.infracaoId, this.dataset.infracaoInfo)" 
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                        title="Excluir">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                                @endcan
                                               
                                            </div>
                                        </td>
                                    </tr>

                                       <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2">Confirmar Exclusão</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Tem certeza que deseja excluir a infração ambiental <span id="deleteInfracaoName" class="font-medium"></span>?
                        Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" value="{{ $infracao->id }}" />
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Excluir
                        </button>
                    </form>
                    <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="px-4 py-3 border-t border-blue-200 bg-blue-50">
                        {{ $infracoes->links() }}
                    </div>
                @else
                    <!-- Estado Vazio -->
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-leaf text-blue-500 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-blue-900 mb-2">Nenhuma infração ambiental encontrada</h3>
                        <p class="text-blue-500 mb-6">Não há infrações ambientais cadastradas no sistema ou que correspondam aos filtros aplicados.</p>
                        @can('infracoes-ambientais.create')
                        <a href="{{ route('infracoes-ambientais.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Registrar Nova Infração
                        </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>

  

    <script>
        function refreshData() {
            window.location.reload();
        }

        function confirmDelete(infracaoId, numeroAuto) {
            const modal = document.getElementById('deleteModal');
            const infoSpan = document.getElementById('deleteInfracaoName');
            const deleteForm = document.getElementById('deleteForm');
            
            if (modal && infoSpan && deleteForm) {
                infoSpan.textContent = numeroAuto;
                deleteForm.action = `/infracoes-ambientais/${infracaoId}`;
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
        });
    </script>
</x-app-layout>