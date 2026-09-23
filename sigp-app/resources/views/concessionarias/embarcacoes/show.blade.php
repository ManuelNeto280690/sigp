<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-ship mr-2 text-blue-600"></i>{{ $embarcacao->nome }}
                </h2>
                <p class="text-xs text-gray-600 mt-1">
                    <span class="bg-gray-100 px-2 py-1 rounded">IMO: {{ $embarcacao->imo ?? 'N/A' }}</span> 
                    <span class="bg-gray-100 px-2 py-1 rounded ml-2">MMSI: {{ $embarcacao->mmsi ?? 'N/A' }}</span>
                </p>
            </div>
            <div class="flex space-x-2">
                @can('update', $embarcacao)
                    <a href="{{ route('embarcacoes.edit', $embarcacao) }}" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md text-sm font-medium transition duration-200 flex items-center shadow-sm">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </a>
                @endcan
                <a href="{{ route('embarcacoes.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded-md text-sm font-medium transition duration-200 flex items-center shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
            <!-- Estatísticas Rápidas -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4" style="font-size: 14px;">
                <x-stat-card 
                    title="Status" 
                    :value="$embarcacao->is_active ? 'Ativa' : 'Inativa'" 
                    :color="$embarcacao->is_active ? 'green' : 'red'" 
                    icon="fas fa-ship" 
                    class="text-xs" />
                
                <x-stat-card 
                    title="Tipo" 
                    :value="$embarcacao->tipo_embarcacao ?? 'N/A'" 
                    color="blue" 
                    icon="fas fa-anchor" 
                    class="text-xs" />
                
                <x-stat-card 
                    title="Bandeira" 
                    :value="$embarcacao->bandeira ?? 'N/A'" 
                    color="purple" 
                    icon="fas fa-flag" 
                    class="text-xs" />
                
                <x-stat-card 
                    title="Atualizada" 
                    :value="$embarcacao->updated_at ? $embarcacao->updated_at->locale('pt_BR')->diffForHumans() : 'N/A'" 
                    color="gray" 
                    icon="fas fa-clock" 
                    class="text-xs" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Coluna 1 -->
                <div class="space-y-4 lg:col-span-1">
                    <!-- Informações Básicas -->
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Informações Básicas
                            </h3>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Concessionária</span>
                                <span class="font-medium">{{ $embarcacao->concessionaria->nome ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Nome</span>
                                <span class="font-medium">{{ $embarcacao->nome }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">IMO</span>
                                <span class="font-medium">{{ $embarcacao->imo ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">MMSI</span>
                                <span class="font-medium">{{ $embarcacao->mmsi ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Bandeira</span>
                                <span class="font-medium">{{ $embarcacao->bandeira ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Tipo</span>
                                <span class="font-medium">{{ $embarcacao->tipo_embarcacao ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </x-card>

                    <!-- Dimensões -->
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-ruler-combined mr-2 text-green-600"></i>
                                Dimensões
                            </h3>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Comprimento</span>
                                <span class="font-medium">{{ $embarcacao->comprimento ? $embarcacao->comprimento . ' m' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Largura</span>
                                <span class="font-medium">{{ $embarcacao->largura ? $embarcacao->largura . ' m' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Calado</span>
                                <span class="font-medium">{{ $embarcacao->calado ? $embarcacao->calado . ' m' : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Arq. Bruta</span>
                                <span class="font-medium">{{ $embarcacao->arqueacao_bruta ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Arq. Líquida</span>
                                <span class="font-medium">{{ $embarcacao->arqueacao_liquida ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Coluna 2 -->
                <div class="space-y-4 lg:col-span-1">
                    <!-- Informações Operacionais -->
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-users mr-2 text-purple-600"></i>
                                Operacional
                            </h3>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Armador</span>
                                <span class="font-medium">{{ $embarcacao->armador ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Agente</span>
                                <span class="font-medium">{{ $embarcacao->agente_maritimo ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">Capitão</span>
                                <span class="font-medium">{{ $embarcacao->capitao ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </x-card>

                    <!-- Movimentação Atual -->
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-route mr-2 text-indigo-600"></i>
                                Movimentação
                            </h3>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Origem</span>
                                <span class="font-medium">{{ $embarcacao->porto_origem ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">Destino</span>
                                <span class="font-medium">{{ $embarcacao->porto_destino ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">ETA</span>
                                <span class="font-medium">{{ $embarcacao->eta ? $embarcacao->eta->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">ATA</span>
                                <span class="font-medium">{{ $embarcacao->ata ? $embarcacao->ata->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-100">
                                <span class="text-gray-500">ETD</span>
                                <span class="font-medium">{{ $embarcacao->etd ? $embarcacao->etd->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-gray-500">ATD</span>
                                <span class="font-medium">{{ $embarcacao->atd ? $embarcacao->atd->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Coluna 3 -->
                <div class="space-y-4 lg:col-span-1">
                    <!-- Ações Rápidas -->
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-cogs mr-2 text-gray-600"></i>
                                Ações
                            </h3>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-2">
                            <!--a href="{{ route('entrada-saida-embarcacao.create', ['embarcacao' => $embarcacao->id]) }}" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md text-xs font-medium transition duration-200 flex items-center justify-center shadow-sm">
                                <i class="fas fa-plus mr-2"></i>Nova Movimentação
                            </a>
                            
                            <a href="{{ route('embarcacoes.export', ['format' => 'pdf', 'id' => $embarcacao->id]) }}" 
                               class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-md text-xs font-medium transition duration-200 flex items-center justify-center shadow-sm">
                                <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                            </a->
                            
                            @can('delete', $embarcacao)
                                <button type="button" 
                                        onclick="confirmDelete('{{ $embarcacao->id }}', '{{ $embarcacao->nome }}')" 
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-xs font-medium transition duration-200 flex items-center justify-center shadow-sm">
                                    <i class="fas fa-trash mr-2"></i>Excluir
                                </button>
                            @endcan
                        </div>
                    </x-card>

                    <!-- Observações -->
                    @if($embarcacao->observacoes)
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-sticky-note mr-2 text-yellow-600"></i>
                                Observações
                            </h3>
                        </div>
                        
                        <div class="bg-gray-50 rounded-md p-2">
                            <p class="text-xs text-gray-700 whitespace-pre-wrap">{{ $embarcacao->observacoes }}</p>
                        </div>
                    </x-card>
                    @endif
                </div>
            </div>

            <!-- Histórico de Movimentações -->
            @if($embarcacao->movimentacoes && $embarcacao->movimentacoes->count() > 0)
            <div class="mt-4">
                <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="border-b border-gray-200 pb-2 mb-3">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-history mr-2 text-indigo-600"></i>
                            Histórico de Movimentações
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terminal</th>
                                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($embarcacao->movimentacoes->take(5) as $movimentacao)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $movimentacao->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $movimentacao->tipo }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $movimentacao->getStatusBadgeClass() }}">{{ $movimentacao->getStatusLabel() }}</span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $movimentacao->terminal->nome ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-sm">
                                        <a href="{{ route('entrada-saida-embarcacao.show', $movimentacao) }}" class="text-indigo-600 hover:text-indigo-900 mr-2"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($embarcacao->movimentacoes->count() > 5)
                    <div class="mt-3 text-right">
                        <a href="#" class="text-xs text-indigo-600 hover:text-indigo-900">Ver todas ({{ $embarcacao->movimentacoes->count() }})</a>
                    </div>
                    @endif
                </x-card>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmar Exclusão</h3>
                <p class="text-sm text-gray-500 mb-6">Tem certeza que deseja excluir a embarcação <span id="embarcacaoNome" class="font-semibold"></span>? Esta ação não pode ser desfeita.</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-300 transition duration-200">
                    Cancelar
                </button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-600 transition duration-200">
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id, nome) {
            document.getElementById('embarcacaoNome').textContent = nome;
            document.getElementById('deleteForm').action = `/embarcacoes/${id}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>