<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-building text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Concessionárias') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Controle e monitore todas as concessionárias portuárias</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                
                <!-- Botão Relatório -->
                <button onclick="gerarRelatorio()" class="bg-green-600 hover:bg-green-700 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório
                </button>
                
                @can('concessionarias.create')
                <a href="{{ route('concessionarias.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #0084de;">
                    <i class="fas fa-plus mr-1"></i>
                    Nova Concessionária
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Concessionárias</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $concessionarias->total() }}</p>
                            <p class="text-blue-600 text-xs mt-1">Registradas no sistema</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" style="background: #0084de;">
                            <i class="fas fa-building text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Concessionárias Ativas</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $concessionarias->where('is_active', true)->count() }}</p>
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
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Embarcações</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $concessionarias->sum('embarcacoes_count') }}</p>
                            <p class="text-blue-600 text-xs mt-1">Vinculadas</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-ship text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form method="GET" action="{{ route('concessionarias.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="Nome, NIF, email, telefone..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm" style="--tw-ring-color: #0084de;">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm" style="--tw-ring-color: #0084de;">
                                <option value="">Todos os status</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Ativa</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inativa</option>
                            </select>
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm hover:opacity-90" style="background: #0084de;">
                                <i class="fas fa-search mr-2"></i>Filtrar
                            </button>
                            <a href="{{ route('concessionarias.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                                <i class="fas fa-times mr-2"></i>Limpar
                            </a>
                        </div>
                       
                    </div>
                </form>
            </div>

             <div class="flex items-center space-x-3">
                                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                                </button>
                                 <a href="{{ route('concessionarias.relatorioFiltrado', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #dc2626;">
                                <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                            </a>
                            <a href="{{ route('concessionarias.relatorioFiltrado', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #16a34a;">
                                <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                            </a>
                                <a href="{{ route('concessionarias.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3395da;">
                                    <i class="fas fa-plus mr-1"></i>
                                    Nova Concessionária
                                </a>
                       </div>

            <!-- Tabela de Concessionárias -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                
                @if($concessionarias->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nome', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-700">
                                            Concessionária
                                            @if(request('sort') === 'nome')
                                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                            @else
                                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usuário Principal
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contato
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Embarcações
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>

                                
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($concessionarias as $concessionaria)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r flex items-center justify-center" style="background: linear-gradient(to right, #0084de, #0066b3);">
                                                        <span class="text-sm font-medium text-white">{{ substr($concessionaria->nome, 0, 2) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $concessionaria->nome }}</div>
                                                    <div class="text-sm text-gray-500">NIF: {{ $concessionaria->nif }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($concessionaria->user)
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-white">{{ substr($concessionaria->user->name, 0, 2) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $concessionaria->user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $concessionaria->user->email }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400 italic">Não definido</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $concessionaria->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $concessionaria->telefone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-ship mr-1"></i>
                                                    {{ $concessionaria->embarcacoes_count }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($concessionaria->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Ativa
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i>
                                                    Inativa
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                @can('concessionarias.view')
                                                <a href="{{ route('concessionarias.show', $concessionaria) }}" class="transition-colors duration-200 hover:opacity-75" style="color: #0084de;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endcan
                                                @can('concessionarias.edit')
                                                <a href="{{ route('concessionarias.edit', $concessionaria) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                @can('concessionarias.delete')
                                                <button onclick="confirmDelete('{{ $concessionaria->id }}', '{{ $concessionaria->nome }}')" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                                    <i class="fas fa-trash"></i>
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
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $concessionarias->links() }}
                    </div>
                @else
                    <!-- Estado Vazio -->
                    <div class="text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-building text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma concessionária encontrada</h3>
                        <p class="text-gray-500 mb-6">Não há concessionárias cadastradas ou que correspondam aos filtros aplicados.</p>
                        @can('concessionarias.create')
                        <a href="{{ route('concessionarias.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white transition-colors duration-200 hover:opacity-90" style="background: #0084de;">
                            <i class="fas fa-plus mr-2"></i>
                            Cadastrar primeira concessionária
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
                        Tem certeza que deseja excluir a concessionária <span id="concessionariaName" class="font-medium"></span>?
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

    <!-- Modal de Relatório -->
    <div id="relatorioModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <i class="fas fa-file-pdf text-green-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-2 text-center">Gerar Relatório</h3>
                <div class="mt-4">
                    <form id="relatorioForm" method="GET" action="{{ route('concessionarias.relatorio') }}">
                        <div class="mb-4">
                            <label for="formato" class="block text-sm font-medium text-gray-700 mb-1">Formato</label>
                            <select name="formato" id="formato" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent text-sm" style="--tw-ring-color: #0084de;">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        
                        <!-- Incluir filtros atuais -->
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        
                        <div class="flex justify-center space-x-3 mt-6">
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                                <i class="fas fa-download mr-1"></i>Gerar
                            </button>
                            <button type="button" onclick="closeRelatorioModal()" class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshData() {
            window.location.reload();
        }

        function gerarRelatorio() {
            document.getElementById('relatorioModal').classList.remove('hidden');
        }

        function closeRelatorioModal() {
            document.getElementById('relatorioModal').classList.add('hidden');
        }

        // Fechar modal ao clicar fora dele
        document.getElementById('relatorioModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRelatorioModal();
            }
        });
    </script>
</x-app-layout>