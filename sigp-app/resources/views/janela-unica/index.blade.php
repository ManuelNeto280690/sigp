<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-list text-white text-xs"></i>
                    </div>
                    {{ __('Escalas') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Gerencie todas as escalas portuárias</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                
                @can('escalas.create')
                <a href="{{ route('janela-unica.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Nova Escala
                </a>
                @endcan
                
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filtros -->
            <x-card>
                <form method="GET" action="{{ route('janela-unica.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Número da escala, embarcação..." 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="programada" {{ request('status') == 'programada' ? 'selected' : '' }}>Programada</option>
                                <option value="confirmada" {{ request('status') == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                                <option value="atracada" {{ request('status') == 'atracada' ? 'selected' : '' }}>Atracada</option>
                                <option value="desatracada" {{ request('status') == 'desatracada' ? 'selected' : '' }}>Desatracada</option>
                                <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                      
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('janela-unica.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Limpar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>

                        <a href="{{ route('janela-unica.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Nova Escala
                </a>
                    </div>

                </form>
                
            </x-card>

            <!-- Tabela de Escalas -->
            <x-card>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'numero_escala', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-700">
                                        Escala
                                        <i class="fas fa-sort ml-1"></i>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Embarcação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'eta_previsto', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-700">
                                        ETA
                                        <i class="fas fa-sort ml-1"></i>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FALs</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($escalas as $escala)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $escala->numero_escala }}</div>
                                    <div class="text-sm text-gray-500">{{ ucfirst($escala->tipo_operacao) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-ship text-blue-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $escala->embarcacao->nome }}</div>
                                            <div class="text-sm text-gray-500">IMO: {{ $escala->embarcacao->imo }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $escala->eta_previsto?->format('d/m/Y H:i') }}</div>
                                    @if($escala->ata_real)
                                    <div class="text-sm text-green-600">Real: {{ $escala->ata_real->format('d/m/Y H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge :type="$escala->status_escala">{{ ucfirst($escala->status_escala) }}</x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $escala->progresso_fals }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $escala->progresso_fals }}%</span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $escala->fals_obrigatorios_aprovados }}/7 aprovados
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('janela-unica.show', $escala) }}" 
                                           class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('escalas.edit')
                                        <a href="{{ route('janela-unica.edit', $escala) }}" 
                                           class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('escalas.delete')
                                        <button onclick="confirmDelete('{{ $escala->id }}')" 
                                                class="text-red-600 hover:text-red-900" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center py-8">
                                        <i class="fas fa-ship text-gray-300 text-4xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-500">Nenhuma escala encontrada</p>
                                        <p class="text-sm text-gray-400">Tente ajustar os filtros ou criar uma nova escala</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($escalas->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $escalas->links() }}
                </div>
                @endif
            </x-card>

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

    @push('scripts')
    <script>
        function confirmDelete(escalaId) {
            if (confirm('Tem certeza que deseja excluir esta escala?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/janela-unica/${escalaId}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function refreshData() {
            location.reload();
        }
    </script>
    @endpush
</x-app-layout>