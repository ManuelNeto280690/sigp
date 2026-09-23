<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-eye text-white text-xs"></i>
                    </div>
                    {{ __('Detalhes do Pedido') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Visualize as informações completas do pedido</p>
            </div>
            <div class="flex items-center space-x-3">
                @if($pedido->podeSerEditado())
                    <a href="{{ route('pedidos.edit', $pedido) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                        <i class="fas fa-edit mr-1 text-xs"></i>Editar
                    </a>
                @endif
                
                @if($pedido->status === 'pendente')
                    <form action="{{ route('pedidos.aprovar', $pedido) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs"
                                onclick="return confirm('Tem certeza que deseja aprovar este pedido?')">
                            <i class="fas fa-check mr-1 text-xs"></i>Aprovar
                        </button>
                    </form>
                    
                    <form action="{{ route('pedidos.rejeitar', $pedido) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs"
                                onclick="return confirm('Tem certeza que deseja rejeitar este pedido?')">
                            <i class="fas fa-times mr-1 text-xs"></i>Rejeitar
                        </button>
                    </form>
                @endif

               
                
                <a href="{{ route('pedidos.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
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

            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <!-- Status e Informações Gerais -->
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Status do Pedido</h3>
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $pedido->status === 'aprovado' ? 'bg-green-100 text-green-800' : ($pedido->status === 'rejeitado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    <i class="fas {{ $pedido->status === 'aprovado' ? 'fa-check-circle' : ($pedido->status === 'rejeitado' ? 'fa-times-circle' : 'fa-clock') }} mr-1"></i>
                                    {{ ucfirst($pedido->status) }}
                                </span>
                                <span class="text-sm text-gray-600">ID: {{ $pedido->id }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Criado em: {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                            <p class="text-sm text-gray-600">Por: {{ $pedido->criadoPor->name ?? 'N/A' }}</p>
                            @if($pedido->decidido_por_id)
                                <p class="text-sm text-gray-600">Decidido por: {{ $pedido->decididoPor->name ?? 'N/A' }}</p>
                            @endif
                        </div>
                         @if(auth()->user()->hasRole('admin') && in_array($pedido->status, ['aprovado', 'rejeitado']))
                    <button type="button" class="bg-orange-100 hover:bg-orange-200 text-orange-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs"
                            onclick="showRevogarModal()">
                        <i class="fas fa-undo mr-1 text-xs"></i>Revogar
                    </button>
                @endif
                    </div>
                </div>

                

                <!-- Informações do Navio -->
                <div class="p-6 border-b border-gray-200">

                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-ship text-blue-500 mr-2"></i>
                        Informações do Navio
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Navio</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->nome_navio }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número IMO</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->numero_imo }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Indicativo de Chamada</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->indicativo_chamada }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número da Viagem</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->numero_viagem ?: 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bandeira do Navio</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->bandeira_navio }}</p>
                        </div>
                    </div>
                </div>

                <!-- Datas e Horários -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-calendar text-blue-500 mr-2"></i>
                        Datas e Horários
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Chegada</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ \Carbon\Carbon::parse($pedido->data_chegada)->format('d/m/Y') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hora de Chegada</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->hora_chegada }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Partida</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->data_partida ? \Carbon\Carbon::parse($pedido->data_partida)->format('d/m/Y') : 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hora de Partida</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->hora_partida ?: 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informações do Agente -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user-tie text-blue-500 mr-2"></i>
                        Informações do Agente
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Agente</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->nome_agente }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contato do Agente</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->contato_agente }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tripulação e Passageiros -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-users text-blue-500 mr-2"></i>
                        Tripulação e Passageiros
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Tripulantes</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->numero_tripulantes }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Passageiros</label>
                            <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $pedido->numero_passageiros ?: '0' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                @if($pedido->observacoes)
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-comment text-blue-500 mr-2"></i>
                        Observações
                    </h3>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $pedido->observacoes }}</p>
                    </div>
                </div>
                @endif

                <!-- Documentos -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                        Documentos
                    </h3>
                    
                    @php
                        $documentTypes = [
                            'certificados_navio' => 'Certificados do Navio',
                            'declaracoes_cargas' => 'Declarações de Cargas',
                            'declaracao_provisoes' => 'Declaração de Provisões de Bordo',
                            'declaracao_pertences' => 'Declaração de Pertences da Tripulação',
                            'documentos_tripulantes' => 'Documentos de Identificação de Tripulantes',
                            'documentos_passageiros' => 'Documentos de Identificação de Passageiros',
                            'declaracao_mercadorias_perigosas' => 'Declaração de Mercadorias Perigosas'
                        ];
                        $hasDocuments = false;
                    @endphp

                    @foreach($documentTypes as $field => $label)
                        @if($pedido->$field && count($pedido->$field) > 0)
                            @php $hasDocuments = true; @endphp
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-800 mb-3">{{ $label }}</h4>
                                <div class="space-y-3">
                                    @foreach($pedido->$field as $index => $arquivo)
                                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-file-pdf text-red-500"></i>
                                                <span class="text-sm text-gray-700 truncate">{{ basename($arquivo) }}</span>
                                            </div>
                                            <a href="{{ route('pedidos.download-arquivo', ['pedido' => $pedido, 'tipo' => $field, 'index' => $index]) }}" 
                                               class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if(!$hasDocuments)
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-500">Nenhum documento foi anexado a este pedido.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Revogação -->
    <div id="modalRevogar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Revogar Pedido</h3>
                    <button type="button" onclick="closeRevogarModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form action="{{ route('pedidos.revogar', $pedido) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Atenção!</strong> Esta ação irá alterar o status do pedido para "Pendente" e remover a decisão anterior.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <label for="motivo_revogacao" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo da Revogação *
                        </label>
                        <textarea 
                            id="motivo_revogacao" 
                            name="motivo_revogacao" 
                            rows="4" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Descreva o motivo da revogação..." 
                            required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRevogarModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition duration-200 flex items-center">
                            <i class="fas fa-undo mr-2"></i>Revogar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showRevogarModal() {
            document.getElementById('modalRevogar').classList.remove('hidden');
        }
        
        function closeRevogarModal() {
            document.getElementById('modalRevogar').classList.add('hidden');
        }
        
        // Fechar modal ao clicar fora dele
        document.getElementById('modalRevogar').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRevogarModal();
            }
        });
    </script>
</x-app-layout>