<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Entrada e Saída de Embarcações') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Controle e monitore todos os movimentos de embarcações</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #0084de;">
                    <i class="fas fa-plus mr-1"></i>
                    Novo Movimento
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
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1" style="background: linear-gradient(135deg, rgba(0, 132, 222, 0.1), rgba(0, 132, 222, 0.2)); border-color: #0084de;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: #0084de;">Total de Movimentos</p>
                            <p class="text-2xl font-bold mt-1" style="color: #0084de;">{{ $movimentos->total() }}</p>
                            <p class="text-xs mt-1" style="color: #0084de;">Registrados no sistema</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" style="background: #0084de;">
                            <i class="fas fa-ship text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Programados</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $movimentos->where('status', 'programado')->count() }}</p>
                            <p class="text-green-600 text-xs mt-1">Aguardando autorização</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-clock text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-600 text-xs font-semibold uppercase tracking-wide">Em Andamento</p>
                            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $movimentos->where('status', 'em_andamento')->count() }}</p>
                            <p class="text-yellow-600 text-xs mt-1">Movimentos ativos</p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-play text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-xs font-semibold uppercase tracking-wide">Concluídos Hoje</p>
                            <p class="text-2xl font-bold text-purple-900 mt-1">{{ $movimentos->where('status', 'concluido')->filter(function($movimento) { return $movimento->updated_at->isToday(); })->count() }}</p>
                            <p class="text-purple-600 text-xs mt-1">Finalizados hoje</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-check text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Avançados -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter mr-2 text-xs" style="color: #0084de;"></i>
                        Filtros de Pesquisa
                    </h3>
                </div>
                <div class="p-4">
                    <form method="GET" action="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Pesquisar</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Embarcação, número movimento..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                                    <option value="">Todos os status</option>                           
                                    <option value="programado" {{ request('status') == 'programado' ? 'selected' : '' }}>Programado</option>
                                    <option value="autorizado" {{ request('status') == 'autorizado' ? 'selected' : '' }}>Autorizado</option>
                                    <option value="em_andamento" {{ request('status') == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="concluido" {{ request('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                                    <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Tipo de Movimento</label>
                                <select name="tipo_movimento" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                                    <option value="">Todos os tipos</option>
                                    <option value="entrada" {{ request('tipo_movimento') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                    <option value="saida" {{ request('tipo_movimento') == 'saida' ? 'selected' : '' }}>Saída</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Data Início</label>
                                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Data Fim</label>
                                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Ou Data Específica</label>
                                <input type="date" name="data" value="{{ request('data') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Deixe vazio para usar intervalo acima">
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="text-white px-4 py-2 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg" style="background: #0084de;">
                                <i class="fas fa-search mr-1 text-xs"></i>Filtrar
                            </button>
                            <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-xs font-medium transition duration-200 flex items-center">
                                <i class="fas fa-times mr-1 text-xs"></i>Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Movimentos -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-list mr-2 text-xs" style="color: #0084de;"></i>
                                Lista de Movimentos
                            </h3>
                            <p class="text-xs text-gray-600 mt-1">{{ $movimentos->total() }} movimentos encontrados</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.relatorio-filtrado', array_merge(request()->query(), ['formato' => 'pdf'])) }}" 
                               class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" 
                               style="background: #dc2626;" target="_blank">
                                <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                            </a>
                            <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.relatorio-filtrado', array_merge(request()->query(), ['formato' => 'excel'])) }}" 
                               class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" 
                               style="background: #059669;" target="_blank">
                                <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                            </a>
                            <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.create') }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #0084de;">
                                <i class="fas fa-plus mr-1 text-xs"></i>Novo Movimento
                            </a>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Movimento</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Embarcação</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terminal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($movimentos as $movimento)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-md {{ $movimento->tipo_movimento == 'entrada' ? 'bg-green-500' : 'bg-red-500' }}">
                                                    <i class="fas {{ $movimento->tipo_movimento == 'entrada' ? 'fa-arrow-down' : 'fa-arrow-up' }} text-white text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-xs font-bold text-gray-900">#{{ $movimento->numero_movimento }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ ucfirst($movimento->tipo_movimento) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="text-xs font-medium text-gray-900">{{ $movimento->embarcacao->nome ?? 'N/A' }}</div>
                                            @if($movimento->embarcacao)
                                                <div class="text-xs text-gray-500 flex items-center">
                                                    <i class="fas fa-anchor mr-1 text-xs"></i>
                                                    {{ $movimento->embarcacao->tipo ?? 'N/A' }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-900">{{ $movimento->terminal->nome ?? 'N/A' }}</div>
                                        @if($movimento->terminal)
                                            <div class="text-xs text-gray-500">{{ $movimento->terminal->codigo ?? '' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <div class="text-xs font-medium text-gray-900">
                                                {{ $movimento->data_programada ? $movimento->data_programada->format('d/m/Y') : 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $movimento->hora_programada ? $movimento->hora_programada->format('H:i') : 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'programado' => 'bg-blue-100 text-blue-800',
                                                'autorizado' => 'bg-green-100 text-green-800',
                                                'em_andamento' => 'bg-yellow-100 text-yellow-800',
                                                'concluido' => 'bg-purple-100 text-purple-800',
                                                'cancelado' => 'bg-red-100 text-red-800'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$movimento->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $movimento->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.show', $movimento) }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200" title="Visualizar">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                         
                                                <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.edit', $movimento) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" title="Editar">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                           
                                                @role('admin')  
                                                <form action="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.autorizar', $movimento) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900 transition-colors duration-200" title="Autorizar" onclick="return confirm('Deseja autorizar este movimento?')">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                        
                                                <form action="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.destroy', $movimento) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            data-movimento-id="{{ $movimento->id }}"
                                                            data-movimento-info="{{ ($movimento->embarcacao->nome ?? 'N/A') . ' - ' . $movimento->tipo_movimento }}"
                                                            onclick="confirmDelete(this.dataset.movimentoId, this.dataset.movimentoInfo)" 
                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                                                            title="Excluir">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                           @endrole
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-ship text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium">Nenhum movimento encontrado</p>
                                            <p class="text-sm">Não há movimentos cadastrados ou que correspondam aos filtros aplicados.</p>
                                            <a href="{{ route('entrada-saida-embarcacao.create') }}" class="mt-4 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center hover:shadow-lg" style="background: #0084de;">
                                                <i class="fas fa-plus mr-2"></i>Criar Primeiro Movimento
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($movimentos->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200">
                        {{ $movimentos->links() }}
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
                    Tem certeza que deseja excluir o movimento de <span id="movimentoInfo" class="font-semibold"></span>?
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
                            Excluir Movimento
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

        function confirmDelete(id, movimentoInfo) {
            const modal = document.getElementById('deleteModal');
            const infoSpan = document.getElementById('movimentoInfo');
            const deleteForm = document.getElementById('deleteForm');
            
            if (modal && infoSpan && deleteForm) {
                infoSpan.textContent = movimentoInfo;
                deleteForm.action = `{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.destroy', $movimento->id) }}`;
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