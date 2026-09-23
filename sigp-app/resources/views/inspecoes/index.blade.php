<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-blue-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Inspeções do Navio') }}
                </h2>
                <p class="text-blue-600 text-xs mt-1">Controle e monitore todas as inspeções navais do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Botões de Relatório -->
               
                <button onclick="refreshData()" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                @can('inspecoes.create')
                <a href="{{ route('inspecoes.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Nova Inspeção
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
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Inspeções</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $inspecoes->total() }}</p>
                            <p class="text-blue-600 text-xs mt-1">Registradas no sistema</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-ship text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-600 text-xs font-semibold uppercase tracking-wide">Agendadas</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $inspecoes->where('status', 'agendada')->count() }}</p>
                            <p class="text-yellow-600 text-xs mt-1">Aguardando execução</p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar-alt text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Em Andamento</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $inspecoes->where('status', 'em_andamento')->count() }}</p>
                            <p class="text-blue-600 text-xs mt-1">Sendo executadas</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-cog text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Concluídas</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $inspecoes->where('status', 'concluida')->count() }}</p>
                            <p class="text-green-600 text-xs mt-1">Finalizadas com sucesso</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Avançados -->
            <div class="bg-white rounded-lg shadow-sm border border-blue-200">
                <div class="p-4 border-b border-blue-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-blue-900 flex items-center">
                            <i class="fas fa-filter text-blue-500 mr-2"></i>
                            Filtros Avançados
                        </h3>
                        <button type="button" onclick="toggleFilters()" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                            <span id="filter-toggle-text">Mostrar Filtros</span>
                            <i id="filter-toggle-icon" class="fas fa-chevron-down ml-1"></i>
                        </button>
                    </div>
                </div>
                
                <div id="filters-section" class="hidden p-4 bg-blue-50">
                    <form method="GET" action="{{ route('inspecoes.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Busca -->
                            <div>
                                <label for="search" class="block text-xs font-medium text-blue-700 mb-1">Buscar</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Número, local, descrição..." 
                                       class="w-full px-3 py-2 text-xs border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-xs font-medium text-blue-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 text-xs border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todos os Status</option>
                                    <option value="agendada" {{ request('status') == 'agendada' ? 'selected' : '' }}>Agendada</option>
                                    <option value="em_andamento" {{ request('status') == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="concluida" {{ request('status') == 'concluida' ? 'selected' : '' }}>Concluída</option>
                                    <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                            </div>

                            <!-- Tipo de Inspeção -->
                            <div>
                                <label for="tipo_inspecao" class="block text-xs font-medium text-blue-700 mb-1">Tipo de Inspeção</label>
                                <select name="tipo_inspecao" id="tipo_inspecao" class="w-full px-3 py-2 text-xs border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todos os Tipos</option>
                                    <option value="isps" {{ request('tipo_inspecao') == 'isps' ? 'selected' : '' }}>ISPS</option>
                                    <option value="ambiental" {{ request('tipo_inspecao') == 'ambiental' ? 'selected' : '' }}>Ambiental</option>
                                    <option value="cais" {{ request('tipo_inspecao') == 'cais' ? 'selected' : '' }}>Cais</option>
                                    <option value="seguranca" {{ request('tipo_inspecao') == 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="sanitaria" {{ request('tipo_inspecao') == 'sanitaria' ? 'selected' : '' }}>Sanitária</option>
                                </select>
                            </div>

                            <!-- Resultado -->
                            <div>
                                <label for="resultado" class="block text-xs font-medium text-blue-700 mb-1">Resultado</label>
                                <select name="resultado" id="resultado" class="w-full px-3 py-2 text-xs border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todos os Resultados</option>
                                    <option value="aprovado" {{ request('resultado') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                                    <option value="aprovado_com_restricoes" {{ request('resultado') == 'aprovado_com_restricoes' ? 'selected' : '' }}>Aprovado com Restrições</option>
                                    <option value="reprovado" {{ request('resultado') == 'reprovado' ? 'selected' : '' }}>Reprovado</option>
                                </select>
                            </div>

                            <!-- Data Início -->
                            <div>
                                <label for="data_inicio" class="block text-xs font-medium text-blue-700 mb-1">Data Início</label>
                                <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" 
                                       class="w-full px-3 py-2 text-xs border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Data Fim -->
                            <div>
                                <label for="data_fim" class="block text-xs font-medium text-blue-700 mb-1">Data Fim</label>
                                <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" 
                                       class="w-full px-3 py-2 text-xs border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Embarcação -->
                            <div>
                                <label for="embarcacao_id" class="block text-xs font-medium text-blue-700 mb-1">Embarcação</label>
                                <select name="embarcacao_id" id="embarcacao_id" class="w-full px-3 py-2 text-xs border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todas as Embarcações</option>
                                    @foreach($embarcacoes ?? [] as $embarcacao)
                                        <option value="{{ $embarcacao->id }}" {{ request('embarcacao_id') == $embarcacao->id ? 'selected' : '' }}>
                                            {{ $embarcacao->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Inspetor -->
                            <div>
                                <label for="inspetor_id" class="block text-xs font-medium text-blue-700 mb-1">Inspetor</label>
                                <select name="inspetor_id" id="inspetor_id" class="w-full px-3 py-2 text-xs border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Todos os Inspetores</option>
                                    @foreach($inspetores ?? [] as $inspetor)
                                        <option value="{{ $inspetor->id }}" {{ request('inspetor_id') == $inspetor->id ? 'selected' : '' }}>
                                            {{ $inspetor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-blue-200">
                            <div class="flex items-center space-x-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-xs flex items-center">
                                    <i class="fas fa-search mr-2"></i>Filtrar
                                </button>
                                <a href="{{ route('inspecoes.index') }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-2 px-4 rounded-lg transition duration-200 text-xs flex items-center">
                                    <i class="fas fa-times mr-2"></i>Limpar
                                </a>
                            </div>
                            <div class="text-xs text-blue-500">
                                Mostrando {{ $inspecoes->firstItem() ?? 0 }} a {{ $inspecoes->lastItem() ?? 0 }} de {{ $inspecoes->total() }} resultados
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inspecoes.relatorio-filtrado', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #dc2626;">
                    <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                </a>
                <a href="{{ route('inspecoes.relatorio-filtrado', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #16a34a;">
                    <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                </a>
                            <a href="{{ route('inspecoes.create') }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #0084de;">
                                <i class="fas fa-plus mr-1 text-xs"></i>Nova inspecção
                            </a>
            </div>
            
            <!-- Tabela de Inspeções -->
            <div class="bg-white rounded-lg shadow-sm border border-blue-200 overflow-hidden">
                
                @if($inspecoes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-200">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'numero_inspecao', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-blue-900">
                                            Número
                                            @if(request('sort') === 'numero_inspecao')
                                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-blue-400 group-hover:text-blue-500"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'local_inspecao', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-blue-900">
                                            Local
                                            @if(request('sort') === 'local_inspecao')
                                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-blue-400 group-hover:text-blue-500"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Resultado</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Embarcação</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">Inspetor</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-blue-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'data_inspecao', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="group inline-flex items-center hover:text-blue-900">
                                            Data Inspeção
                                            @if(request('sort') === 'data_inspecao')
                                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1 text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-blue-400 group-hover:text-blue-500"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-blue-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-blue-200">
                                @foreach($inspecoes as $inspecao)
                                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-blue-900">#{{ $inspecao->numero_inspecao }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-blue-900">{{ $inspecao->local_inspecao }}</div>
                                            <div class="text-xs text-blue-500 truncate max-w-xs">{{ $inspecao->observacoes }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @switch($inspecao->tipo_inspecao)
                                                    @case('isps') bg-purple-100 text-purple-800 @break
                                                    @case('ambiental') bg-green-100 text-green-800 @break
                                                    @case('cais') bg-blue-100 text-blue-800 @break
                                                    @case('seguranca') bg-red-100 text-red-800 @break
                                                    @case('sanitaria') bg-yellow-100 text-yellow-800 @break
                                                    @default bg-blue-100 text-blue-800
                                                @endswitch">
                                                {{ strtoupper($inspecao->tipo_inspecao) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @switch($inspecao->status)
                                                    @case('agendada') bg-yellow-100 text-yellow-800 @break
                                                    @case('em_andamento') bg-blue-100 text-blue-800 @break
                                                    @case('concluida') bg-green-100 text-green-800 @break
                                                    @case('cancelada') bg-blue-100 text-blue-800 @break
                                                    @default bg-blue-100 text-blue-800
                                                @endswitch">
                                                <i class="fas 
                                                    @switch($inspecao->status)
                                                        @case('agendada') fa-calendar-alt @break
                                                        @case('em_andamento') fa-cog @break
                                                        @case('concluida') fa-check-circle @break
                                                        @case('cancelada') fa-ban @break
                                                        @default fa-question-circle
                                                    @endswitch mr-1 text-xs"></i>
                                                {{ ucfirst(str_replace('_', ' ', $inspecao->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($inspecao->resultado)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @switch($inspecao->resultado)
                                                        @case('aprovado') bg-green-100 text-green-800 @break
                                                        @case('aprovado_com_restricoes') bg-yellow-100 text-yellow-800 @break
                                                        @case('reprovado') bg-red-100 text-red-800 @break
                                                        @default bg-blue-100 text-blue-800
                                                    @endswitch">
                                                    <i class="fas 
                                                        @switch($inspecao->resultado)
                                                            @case('aprovado') fa-check-circle @break
                                                            @case('aprovado_com_restricoes') fa-exclamation-triangle @break
                                                            @case('reprovado') fa-times-circle @break
                                                            @default fa-question-circle
                                                        @endswitch mr-1 text-xs"></i>
                                                    {{ ucfirst(str_replace('_', ' ', $inspecao->resultado)) }}
                                                </span>
                                            @else
                                                <span class="text-xs text-blue-400">Pendente</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($inspecao->embarcacao)
                                                <div class="text-sm font-medium text-blue-900">{{ $inspecao->embarcacao->nome }}</div>
                                                <div class="text-xs text-blue-500">{{ $inspecao->embarcacao->imo ?? 'N/A' }}</div>
                                            @else
                                                <span class="text-xs text-blue-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-white">
                                                            {{ substr($inspecao->inspetor->name ?? 'N/A', 0, 1) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-blue-900">{{ $inspecao->inspetor->name ?? 'N/A' }}</div>
                                                    <div class="text-xs text-blue-500">{{ $inspecao->inspetor->email ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-500">
                                            <div class="text-sm text-blue-900">{{ $inspecao->data_inspecao->format('d/m/Y') }}</div>
                                            <div class="text-xs text-blue-500">{{ $inspecao->data_inspecao->format('H:i') }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                               
                                                <a href="{{ route('inspecoes.show', $inspecao) }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="Visualizar">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </a>
                                                <a href="{{ route('inspecoes.exportPdf', $inspecao) }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="Exportar PDF">
                                                    <i class="fas fa-file-pdf text-sm"></i>
                                                </a>
                                                
                                                @can('inspecoes.edit')
                                                @if($inspecao->status !== 'concluida')
                                                <a href="{{ route('inspecoes.edit', $inspecao) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" title="Editar">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                                @endif
                                                @endcan

                                                @can('inspecoes.start')
                                                @if($inspecao->status === 'agendada')
                                                <button onclick="startInspection({{ $inspecao->id }})" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="Iniciar Inspeção">
                                                    <i class="fas fa-play text-sm"></i>
                                                </button>
                                                @endif
                                                @endcan

                                                @can('inspecoes.complete')
                                                @if($inspecao->status === 'em_andamento')
                                                <button onclick="completeInspection({{ $inspecao->id }})" class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Concluir Inspeção">
                                                    <i class="fas fa-check text-sm"></i>
                                                </button>
                                                @endif
                                                @endcan

                                                @can('inspecoes.delete')
                                                @if($inspecao->status !== 'concluida')
                                                <form method="POST" action="{{ route('inspecoes.destroy', $inspecao) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir a Inspeção #{{ $inspecao->numero_inspecao }}? Esta ação não pode ser desfeita.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200" title="Excluir">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>

                                    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2">Confirmar Exclusão</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Tem certeza que deseja excluir a inspeção <span id="deleteInspecaoName" class="font-medium"></span>?
                        Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" value="{{ $inspecao->id }}" />
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
                    <div class="bg-white px-4 py-3 border-t border-blue-200 sm:px-6">
                        {{ $inspecoes->links() }}
                    </div>
                @else
                    <!-- Estado Vazio -->
                    <div class="text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-ship text-blue-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-blue-900 mb-2">Nenhuma inspeção encontrada</h3>
                        <p class="text-blue-500 mb-6">Não há inspeções cadastradas no sistema ou que correspondam aos filtros aplicados.</p>
                        @can('inspecoes.create')
                        <a href="{{ route('inspecoes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Criar Primeira Inspeção
                        </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Conclusão de Inspeção -->
    <div id="completeModal" class="fixed inset-0 bg-blue-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-blue-900 mt-2">Concluir Inspeção</h3>
                <div class="mt-2 px-7 py-3">
                    <form id="completeForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-4">
                            <label for="resultado" class="block text-sm font-medium text-blue-700 mb-2">Resultado</label>
                            <select name="resultado" id="resultado" required class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Selecione o resultado</option>
                                <option value="aprovado">Aprovado</option>
                                <option value="aprovado_com_restricoes">Aprovado com Restrições</option>
                                <option value="reprovado">Reprovado</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="relatorio_final" class="block text-sm font-medium text-blue-700 mb-2">Relatório Final</label>
                            <textarea name="relatorio_final" id="relatorio_final" rows="3" required class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Descreva o resultado da inspeção..."></textarea>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                                Concluir Inspeção
                            </button>
                            <button type="button" onclick="closeCompleteModal()" class="mt-3 px-4 py-2 bg-blue-300 text-blue-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

 

    <!-- Scripts -->
    <script>
        function toggleFilters() {
            const filtersSection = document.getElementById('filters-section');
            const toggleText = document.getElementById('filter-toggle-text');
            const toggleIcon = document.getElementById('filter-toggle-icon');
            
            if (filtersSection.classList.contains('hidden')) {
                filtersSection.classList.remove('hidden');
                toggleText.textContent = 'Ocultar Filtros';
                toggleIcon.classList.remove('fa-chevron-down');
                toggleIcon.classList.add('fa-chevron-up');
            } else {
                filtersSection.classList.add('hidden');
                toggleText.textContent = 'Mostrar Filtros';
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
            }
        }

        function refreshData() {
            window.location.reload();
        }

        function startInspection(inspecaoId) {
            if (confirm('Tem certeza que deseja iniciar esta inspeção?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/inspecoes/${inspecaoId}/start`;
                form.innerHTML = `
                    @csrf
                    @method('PATCH')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function completeInspection(inspecaoId) {
            const modal = document.getElementById('completeModal');
            const form = document.getElementById('completeForm');
            form.action = `/inspecoes/${inspecaoId}/complete`;
            modal.classList.remove('hidden');
        }

        function closeCompleteModal() {
            const modal = document.getElementById('completeModal');
            modal.classList.add('hidden');
            document.getElementById('completeForm').reset();
        }

        // Auto-mostrar filtros se houver parâmetros de busca
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const hasFilters = Array.from(urlParams.keys()).some(key => 
                ['search', 'status', 'tipo_inspecao', 'resultado', 'data_inicio', 'data_fim', 'embarcacao_id', 'inspetor_id'].includes(key)
            );
            
            if (hasFilters) {
                toggleFilters();
            }
        });

        // Fechar modal ao clicar fora dele
        window.onclick = function(event) {
            const completeModal = document.getElementById('completeModal');
            
            if (event.target == completeModal) {
                closeCompleteModal();
            }
        }
    </script>
</x-app-layout>


function gerarRelatorio(formato) {
    // Capturar os filtros atuais
    const params = new URLSearchParams();
    
    // Capturar valores dos filtros
    const search = document.querySelector('input[name="search"]')?.value;
    const tipoInspecao = document.querySelector('select[name="tipo_inspecao"]')?.value;
    const status = document.querySelector('select[name="status"]')?.value;
    const resultado = document.querySelector('select[name="resultado"]')?.value;
    const dataInicio = document.querySelector('input[name="data_inicio"]')?.value;
    const dataFim = document.querySelector('input[name="data_fim"]')?.value;
    const embarcacaoId = document.querySelector('select[name="embarcacao_id"]')?.value;
    
    // Adicionar parâmetros não vazios
    if (search) params.append('search', search);
    if (tipoInspecao) params.append('tipo_inspecao', tipoInspecao);
    if (status) params.append('status', status);
    if (resultado) params.append('resultado', resultado);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    if (embarcacaoId) params.append('embarcacao_id', embarcacaoId);
    
    // Adicionar formato
    params.append('formato', formato);
    
    // Gerar URL e abrir
    const url = `{{ route('inspecoes.relatorio-filtrado') }}?${params.toString()}`;
    window.open(url, '_blank');
}