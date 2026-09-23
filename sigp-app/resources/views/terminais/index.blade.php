<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-warehouse text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Terminais') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Controle e monitore todos os terminais portuários</p>
            </div>
            <div class="flex items-center space-x-3 justify-end">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                
                <!-- Botões de Relatório -->
               
                
                @can('create', App\Models\Terminal::class)
                <a href="{{ route('terminais.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Novo Terminal
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
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Terminais</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $terminais->total() }}</p>
                            <p class="text-blue-600 text-xs mt-1">Registrados no sistema</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-warehouse text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Terminais Ativos</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $terminais->where('is_active', true)->count() }}</p>
                            <p class="text-green-600 text-xs mt-1">Em operação</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Movimentos Este Mês</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $terminais->sum('movimentos_terminais_count') }}</p>
                            <p class="text-blue-600 text-xs mt-1">Operações realizadas</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-exchange-alt text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Embarcações Atendidas</p>
                            <p class="text-2xl font-bold text-orange-900 mt-1">{{ $terminais->sum('entrada_saidas_count') }}</p>
                            <p class="text-orange-600 text-xs mt-1">Neste período</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-ship text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

       

            <!-- Filtros e Busca -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form method="GET" action="{{ route('terminais.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Busca -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Nome, código, localização..."
                                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="">Todos os status</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" id="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <option value="">Todos os tipos</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                                        {{ ucfirst($tipo) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ações -->
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                                <i class="fas fa-filter mr-2"></i>
                                Filtrar
                            </button>
                            <a href="{{ route('terminais.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                                <i class="fas fa-times mr-2"></i>
                                Limpar
                            </a>
                        </div>

                         

                    </div>
                </form>
            </div>

            <!-- Tabela de Terminais -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Lista de Terminais</h3>
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <span>Mostrando {{ $terminais->firstItem() ?? 0 }} a {{ $terminais->lastItem() ?? 0 }} de {{ $terminais->total() }} resultados</span>
                        </div>

                        
                             <a href="{{ route('terminais.relatorioFiltrado', array_merge(request()->all(), ['formato' => 'pdf'])) }}" 
                                class="bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                                    <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                                </a>
                
                                    <a href="{{ route('terminais.relatorioFiltrado', array_merge(request()->all(), ['formato' => 'excel'])) }}" 
                                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                                        <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                                    </a>
                                                    <a href="{{ route('terminais.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                    <i class="fas fa-plus mr-2"></i>
                                    Cadastrar Novo Terminal
                                </a>
                       
                    </div>
                </div>

                @if($terminais->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nome', 'direction' => request('sort') == 'nome' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-700">
                                            <i class="fas fa-warehouse mr-2"></i>
                                            Terminal
                                            @if(request('sort') == 'nome')
                                                <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 opacity-50"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center">
                                            <i class="fas fa-tags mr-2"></i>
                                            Tipo
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-anchor mr-2"></i>
                                            Berços
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-warehouse mr-2"></i>
                                            Capacidade
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-toggle-on mr-2"></i>
                                            Status
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-chart-line mr-2"></i>
                                            Movimentos
                                        </div>
                                    </th>
                                   
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-eye mr-2"></i>
                                            Acções
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($terminais as $terminal)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-warehouse text-blue-600 text-sm"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $terminal->nome }}</div>
                                                    <div class="text-sm text-gray-500">{{ $terminal->codigo }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ ucfirst($terminal->tipo ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $terminal->localizacao ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($terminal->capacidade_maxima)
                                                {{ number_format($terminal->capacidade_maxima, 0, ',', '.') }} t
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $terminal->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $terminal->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                                {{ $terminal->is_active ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex items-center space-x-4">
                                                <div class="text-center">
                                                    <div class="font-medium">{{ $terminal->movimentos_terminais_count }}</div>
                                                    <div class="text-xs text-gray-500">Movimentos</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="font-medium">{{ $terminal->entrada_saidas_count }}</div>
                                                    <div class="text-xs text-gray-500">Embarcações</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                               
                                                <a href="{{ route('terminais.show', $terminal->id) }}" 
                                                   class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                   title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                              
                                                
                                              
                                                <a href="{{ route('terminais.edit', $terminal->id) }}" 
                                                   class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                              

                                                <!-- Botão de Exclusão -->
                                                <button onclick="confirmDelete('{{ $terminal->id }}', '{{ $terminal->nome }}')" 
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                        title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>

                                             
                                                <form action="{{ route('terminais.toggle-status', $terminal->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="text-{{ $terminal->is_active ? 'red' : 'green' }}-600 hover:text-{{ $terminal->is_active ? 'red' : 'green' }}-900 transition-colors duration-200"
                                                            title="{{ $terminal->is_active ? 'Desativar' : 'Ativar' }}"
                                                            onclick="return confirm('Tem certeza que deseja {{ $terminal->is_active ? 'desativar' : 'ativar' }} este terminal?')">
                                                        <i class="fas fa-{{ $terminal->is_active ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                </form>
                                            
                                            </div>
                                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        {{ $terminais->links() }}
    </div>
@else
    <div class="px-6 py-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-warehouse text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum terminal encontrado</h3>
        <p class="text-gray-500 mb-6">Não há terminais cadastrados que correspondam aos filtros aplicados.</p>
        @can('create', App\Models\Terminal::class)
        <a href="{{ route('terminais.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200">
            <i class="fas fa-plus mr-2"></i>
            Cadastrar Primeiro Terminal
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
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2">Confirmar Exclusão</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Tem certeza que deseja excluir o terminal <span id="terminalName" class="font-medium"></span>?
                        Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
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

    <script>
        function refreshData() {
            window.location.reload();
        }

        function confirmDelete(id, name) {
            document.getElementById('terminalName').textContent = name;
            document.getElementById('deleteForm').action = `/terminais/${id}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Fechar modal ao clicar fora dele
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</x-app-layout>