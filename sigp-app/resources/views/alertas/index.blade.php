<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-red-500 to-red-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-exclamation-triangle text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Alertas') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Monitore e gerencie todos os alertas do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
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
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Alertas</p>
                            <p class="text-2xl font-bold text-red-900 mt-1">{{ $alertas->total() }}</p>
                            <p class="text-blue-600 text-xs mt-1">Registrados no sistema</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-600 text-xs font-semibold uppercase tracking-wide">Alertas Pendentes</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $alertas->where('status', 'pendente')->count() }}</p>
                            <p class="text-yellow-600 text-xs mt-1">Aguardando resolução</p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Alertas Resolvidos</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $alertas->where('status', 'resolvido')->count() }}</p>
                            <p class="text-green-600 text-xs mt-1">Finalizados</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-xs font-semibold uppercase tracking-wide">Alertas Críticos</p>
                            <p class="text-2xl font-bold text-purple-900 mt-1">{{ $alertas->where('prioridade', 'critica')->count() }}</p>
                            <p class="text-purple-600 text-xs mt-1">Alta prioridade</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-fire text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form method="GET" action="{{ route('alertas.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Título, descrição..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="">Todos os status</option>
                                <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                <option value="resolvido" {{ request('status') == 'resolvido' ? 'selected' : '' }}>Resolvido</option>
                                <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>

                        <div>
                            <label for="nivel" class="block text-sm font-medium text-gray-700 mb-1">Nível</label>
                            <select name="nivel" id="nivel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="">Todos os níveis</option>
                                <option value="baixa" {{ request('nivel') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                <option value="media" {{ request('nivel') == 'media' ? 'selected' : '' }}>Média</option>
                                <option value="alta" {{ request('nivel') == 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="critica" {{ request('nivel') == 'critica' ? 'selected' : '' }}>Crítica</option>
                            </select>
                        </div>

                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" id="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="">Todos os tipos</option>
                                <option value="sistema" {{ request('tipo') == 'sistema' ? 'selected' : '' }}>Sistema</option>
                                <option value="seguranca" {{ request('tipo') == 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                <option value="operacional" {{ request('tipo') == 'operacional' ? 'selected' : '' }}>Operacional</option>
                                <option value="ambiental" {{ request('tipo') == 'ambiental' ? 'selected' : '' }}>Ambiental</option>
                                <option value="manutencao" {{ request('tipo') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                            </select>
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm text-white" style="background: #3b82f6;" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                                <i class="fas fa-search mr-2"></i>Filtrar
                            </button>
                            <a href="{{ route('alertas.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                                <i class="fas fa-times mr-2"></i>Limpar
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Botões de Relatório e Novo Alerta -->
                
            </div>

            <div class="mt-4 flex justify-between items-center">
                    <!-- Botões de Relatório -->
                    <div class="flex space-x-3">
                        <a href="{{ route('alertas.relatorioFiltrado', array_merge(request()->all(), ['formato' => 'pdf'])) }}" 
                           class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-file-pdf mr-2"></i>Relatório PDF
                        </a>
                        <a href="{{ route('alertas.relatorioFiltrado', array_merge(request()->all(), ['formato' => 'excel'])) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-file-excel mr-2"></i>Relatório Excel
                        </a>
                         @can('alertas.create')
                    <a href="{{ route('alertas.create') }}" class="text-white font-bold py-2 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-sm" style="background: #3b82f6;" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Alerta
                    </a>
                    @endcan
                    </div>

                    <!-- Botão Novo Alerta -->
                   
                </div>

            <!-- Tabela de Alertas -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                @if($alertas->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'titulo', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-700">
                                            Alerta
                                            @if(request('sort') === 'titulo')
                                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo & Nível
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-700">
                                            Data de Criação
                                            @if(request('sort') === 'created_at')
                                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Responsável
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Ações</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($alertas as $alerta)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center
                                                        @if($alerta->nivel === 'critica') bg-red-100 text-red-600
                                                        @elseif($alerta->nivel === 'alta') bg-orange-100 text-orange-600
                                                        @elseif($alerta->nivel === 'media') bg-yellow-100 text-yellow-600
                                                        @else bg-blue-100 text-blue-600
                                                        @endif">
                                                        <i class="fas fa-exclamation-triangle text-sm"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ Str::limit($alerta->titulo, 40) }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ Str::limit($alerta->descricao, 60) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col space-y-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($alerta->tipo === 'sistema') bg-blue-100 text-blue-800
                                                    @elseif($alerta->tipo === 'seguranca') bg-red-100 text-red-800
                                                    @elseif($alerta->tipo === 'operacional') bg-green-100 text-green-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($alerta->tipo) }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($alerta->nivel === 'critica') bg-red-100 text-red-800
                                                    @elseif($alerta->nivel === 'alta') bg-orange-100 text-orange-800
                                                    @elseif($alerta->nivel === 'media') bg-yellow-100 text-yellow-800
                                                    @else bg-blue-100 text-blue-800
                                                    @endif">
                                                    {{ ucfirst($alerta->nivel) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($alerta->status === 'pendente') bg-yellow-100 text-yellow-800
                                                @elseif($alerta->status === 'em_andamento') bg-blue-100 text-blue-800
                                                @elseif($alerta->status === 'resolvido') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                @if($alerta->status === 'activo')
                                                    <i class="fas fa-clock mr-1"></i>Activo
                                                @elseif($alerta->status === 'em_andamento')
                                                    <i class="fas fa-spinner mr-1"></i>Em Andamento
                                                @elseif($alerta->status === 'resolvido')
                                                    <i class="fas fa-check mr-1"></i>Resolvido
                                                @else
                                                    <i class="fas fa-times mr-1"></i>Cancelado
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex flex-col">
                                                <span>{{ $alerta->created_at->format('d/m/Y') }}</span>
                                                <span class="text-xs text-gray-500">{{ $alerta->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($alerta->user->name)
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-gray-700">
                                                                {{ substr($alerta->user->name, 0, 2) }} 
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-2">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $alerta->user->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-sm">Não atribuído</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                @can('alertas.show')
                                                <a href="{{ route('alertas.show', $alerta) }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endcan
                                                @can('alertas.edit')
                                                <a href="{{ route('alertas.edit', $alerta) }}" class="text-purple-600 hover:text-purple-900 transition-colors duration-200" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                @if($alerta->status === 'pendente' && auth()->user()->can('alertas.resolve'))
                                                <form action="{{ route('alertas.resolve', $alerta) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Resolver" onclick="return confirm('Tem certeza que deseja marcar este alerta como resolvido?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                @can('alertas.delete')
                                                <form action="{{ route('alertas.destroy', $alerta) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            data-alerta-id="{{ $alerta->id }}"
                                                            data-alerta-info="{{ $alerta->titulo }}"
                                                            onclick="confirmDelete(this.dataset.alertaId, this.dataset.alertaInfo)" 
                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                                                            title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                @if($alertas->previousPageUrl())
                                    <a href="{{ $alertas->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Anterior
                                    </a>
                                @endif
                                @if($alertas->nextPageUrl())
                                    <a href="{{ $alertas->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Próximo
                                    </a>
                                @endif
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando
                                        <span class="font-medium">{{ $alertas->firstItem() }}</span>
                                        até
                                        <span class="font-medium">{{ $alertas->lastItem() }}</span>
                                        de
                                        <span class="font-medium">{{ $alertas->total() }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    {{ $alertas->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum alerta encontrado</h3>
                        <p class="text-gray-500 mb-6">Não há alertas cadastrados no sistema ou que correspondam aos filtros aplicados.</p>
                        @can('alertas.create')
                        <a href="{{ route('alertas.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-plus mr-2"></i>
                            Criar Primeiro Alerta
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
                    Tem certeza que deseja excluir o alerta <span id="alertaInfo" class="font-semibold"></span>?
                    <br><span class="text-xs text-red-600 mt-2 block">Esta ação não pode ser desfeita.</span>
                </p>
                <div class="flex justify-center space-x-4">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition duration-200">
                            Excluir Alerta
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

        function confirmDelete(id, alertaInfo) {
            const modal = document.getElementById('deleteModal');
            const infoSpan = document.getElementById('alertaInfo');
            const deleteForm = document.getElementById('deleteForm');
            
            if (modal && infoSpan && deleteForm) {
                infoSpan.textContent = alertaInfo;
                deleteForm.action = `/alertas/${id}`;
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