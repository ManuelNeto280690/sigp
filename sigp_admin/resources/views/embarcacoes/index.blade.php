<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Embarcações') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Controle e monitore todas as embarcações do sistema portuário</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <a href="{{ route('embarcacoes.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3395da;">
                    <i class="fas fa-plus mr-1"></i>
                    Nova Embarcação
                </a>
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
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Embarcações</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</p>
                            <p class="text-blue-600 text-xs mt-1">Registradas no sistema</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-ship text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Embarcações Ativas</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['ativas'] }}</p>
                            <p class="text-green-600 text-xs mt-1">Em operação</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-600 text-xs font-semibold uppercase tracking-wide">Embarcações Inativas</p>
                            <p class="text-2xl font-bold text-red-900 mt-1">{{ $stats['inativas'] }}</p>
                            <p class="text-red-600 text-xs mt-1">Fora de operação</p>
                        </div>
                        <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-times-circle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-600 text-xs font-semibold uppercase tracking-wide">No Porto</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $stats['no_porto'] }}</p>
                            <p class="text-yellow-600 text-xs mt-1">Atracadas</p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-anchor text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário de Filtros Único e Compacto -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-600 text-xs"></i>
                        Filtros de Pesquisa
                    </h3>
                </div>
                <div class="p-4">
                    <form method="GET" action="{{ route('embarcacoes.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Buscar Embarcação</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-xs"></i>
                                </div>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Nome, IMO, MMSI..." 
                                       class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                            </div>
                        </div>
                        
                        <div>
                            <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status"
                                class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                <option value="">Todos os status</option>
                                <option value="esperado" {{ request('status') == 'esperado' ? 'selected' : '' }}>Esperado</option>
                                <option value="atracado" {{ request('status') == 'atracado' ? 'selected' : '' }}>Atracado</option>
                                <option value="operando" {{ request('status') == 'operando' ? 'selected' : '' }}>Operando</option>
                                <option value="partido" {{ request('status') == 'partido' ? 'selected' : '' }}>Partido</option>
                            </select>
                        </div>

                        <div>
                            <label for="tipo_embarcacao" class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo_embarcacao" id="tipo_embarcacao"
                                class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                <option value="">Todos os tipos</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo }}" {{ request('tipo_embarcacao') == $tipo ? 'selected' : '' }}>
                                        {{ ucfirst($tipo) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="bandeira" class="block text-xs font-medium text-gray-700 mb-1">Bandeira</label>
                            <select name="bandeira" id="bandeira"
                                class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                <option value="">Todas as bandeiras</option>
                                @foreach($bandeiras as $bandeira)
                                    <option value="{{ $bandeira }}" {{ request('bandeira') == $bandeira ? 'selected' : '' }}>
                                        {{ $bandeira }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 text-white font-medium py-2 px-3 rounded-md transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 text-xs" style="background: #3395da;">
                                <i class="fas fa-search mr-1 text-xs"></i>Filtrar
                            </button>
                            <a href="{{ route('embarcacoes.index') }}" class="text-white font-medium py-2 px-3 rounded-md transition duration-200 flex items-center justify-center text-xs" style="background: #6b7280;">
                                <i class="fas fa-times text-xs"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Embarcações Aprimorada -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-list mr-2 text-blue-600 text-xs"></i>
                                Lista de Embarcações
                            </h3>
                            <p class="text-xs text-gray-600 mt-1">{{ $embarcacoes->total() }} embarcações encontradas</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('embarcacoes.relatorioFiltrado', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #dc2626;">
                                <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                            </a>
                            <a href="{{ route('embarcacoes.relatorioFiltrado', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #16a34a;">
                                <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                            </a>
                            <a href="{{ route('embarcacoes.create') }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #3395da;">
                                <i class="fas fa-plus mr-1 text-xs"></i>Adicionar Embarcação
                            </a>
                            <!--button onclick="exportData('csv')" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center" style="background: #10b981;">
                                <i class="fas fa-file-csv mr-1 text-xs"></i>CSV
                            </button>
                            <button onclick="exportData('excel')" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center" style="background: #3395da;">
                                <i class="fas fa-file-excel mr-1 text-xs"></i>Excel
                            </button>
                            <button onclick="exportData('pdf')" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center" style="background: #3b82f6;">
                                <i class="fas fa-file-pdf mr-1 text-xs"></i>PDF
                            </button-->
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Embarcação</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Identificação</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Última Movimentação</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($embarcacoes as $embarcacao)
                                <tr class="hover:bg-blue-50 transition-colors duration-200 group">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-lg flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-200" style="background: #3395da;">
                                                    <i class="fas fa-ship text-white text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-xs font-bold text-gray-900">{{ $embarcacao->nome }}</div>
                                                <div class="text-xs text-gray-500 flex items-center">
                                                    <i class="fas fa-flag mr-1 text-xs"></i>
                                                    {{ $embarcacao->bandeira }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="text-xs font-medium text-gray-900 flex items-center">
                                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded mr-2">IMO</span>
                                                {{ $embarcacao->imo ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500 flex items-center">
                                                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-0.5 rounded mr-2">MMSI</span>
                                                {{ $embarcacao->mmsi ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $tipoColors = [
                                                'Cargueiro' => 'bg-blue-100 text-blue-800',
                                                'Tanque' => 'bg-orange-100 text-orange-800',
                                                'Passageiros' => 'bg-green-100 text-green-800',
                                                'Pesqueiro' => 'bg-purple-100 text-purple-800',
                                                'Rebocador' => 'bg-yellow-100 text-yellow-800'
                                            ];
                                            $colorClass = $tipoColors[$embarcacao->tipo_embarcacao] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $colorClass }}">
                                            {{ $embarcacao->tipo_embarcacao }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($embarcacao->status == 'Ativa')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                                                Ativa
                                            </span>
                                        @elseif($embarcacao->status == 'Inativa')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1"></span>
                                                Inativa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1"></span>
                                                {{ $embarcacao->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs text-gray-900 font-medium">{{ $embarcacao->updated_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $embarcacao->updated_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="flex justify-end space-x-1">
                                            
                                            <a href="{{ route('embarcacoes.show', $embarcacao) }}" 
                                               class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                               style="background: #3395da;"
                                               title="Visualizar">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            
                                         
                                            <a href="{{ route('embarcacoes.exportPdf', $embarcacao) }}" 
                                               class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                               style="background: #dc2626;"
                                               title="Baixar PDF">
                                                <i class="fas fa-file-pdf text-xs"></i>
                                            </a>
                                            
                                          
                                            <a href="{{ route('embarcacoes.edit', $embarcacao) }}" 
                                               class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                               style="background: #3395da;"
                                               title="Editar">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                           

                                             @role('admin')
                                            <button onclick="confirmDelete('{{ $embarcacao->id }}', '{{ $embarcacao->nome }}')" 
                                                    class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                                    style="background: #3b82f6;"
                                                    title="Excluir">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                            @endrole
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                <i class="fas fa-ship text-gray-400 text-xl"></i>
                                            </div>
                                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Nenhuma embarcação encontrada</h3>
                                            <p class="text-gray-500 mb-4 text-xs">Não há embarcações que correspondam aos filtros aplicados.</p>
                                            <a href="{{ route('embarcacoes.create') }}" class="text-white font-semibold py-2 px-4 rounded-md transition duration-200 flex items-center text-xs" style="background: #3395da;">
                                                <i class="fas fa-plus mr-2"></i>Adicionar Primeira Embarcação
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginação Compacta -->
                @if($embarcacoes->hasPages())
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-700">
                                Mostrando {{ $embarcacoes->firstItem() }} a {{ $embarcacoes->lastItem() }} de {{ $embarcacoes->total() }} resultados
                            </div>
                            <div>
                                {{ $embarcacoes->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão Compacto -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 items-center justify-center p-4" style="display: none;">
        <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm transform transition-all mx-auto mt-20">
            <div class="p-4">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-3">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Confirmar Exclusão</h3>
                <p class="text-gray-600 text-center mb-4 text-sm">
                    Tem certeza que deseja excluir a embarcação <strong id="embarcacaoNome" class="text-gray-900"></strong>?
                    <br><span class="text-xs text-red-600">Esta ação não pode ser desfeita.</span>
                </p>
                <div class="flex space-x-2">
                    <button onclick="closeDeleteModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-3 rounded-md transition duration-200 text-xs">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-white font-semibold py-2 px-3 rounded-md transition duration-200 text-xs" style="background: #dc2626;">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

  
    <script>
        function confirmDelete(id, nome) {
            console.log('Confirmando exclusão:', id, nome); // Debug
            document.getElementById('embarcacaoNome').textContent = nome;
            document.getElementById('deleteForm').action = `/embarcacoes/${id}`;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Fechar modal ao clicar fora
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>


     <script>
        function exportData(format) {
            const params = new URLSearchParams(window.location.search);
            params.set('export', format);
            window.location.href = `{{ route('embarcacoes.index') }}?${params.toString()}`;
        }

        function refreshData() {
            window.location.reload();
        }

        // Fechar modal ao clicar fora
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Animação de entrada para as linhas da tabela
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
 
</x-app-layout>