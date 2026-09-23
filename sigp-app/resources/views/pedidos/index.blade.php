<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Pedidos de Entrada/Saída') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Gerencie os pedidos de entrada e saída de embarcações</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="location.reload()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <a href="{{ route('pedidos.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Novo Pedido
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensagens de Sucesso/Erro -->
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

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form method="GET" action="{{ route('pedidos.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="Nome do navio, IMO, etc..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todos</option>
                            <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                            <option value="rejeitado" {{ request('status') == 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                        </select>
                    </div>

                    <div>
                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                        <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>

                    <div>
                        <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                        <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>
                        <a href="{{ route('pedidos.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                            <i class="fas fa-times mr-1"></i>Limpar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabela de Pedidos -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Lista de Pedidos</h3>
                        <p class="text-sm text-gray-600">Total: {{ $pedidos->total() }} pedidos</p>
                    </div>
                    <!-- Botões de Ação -->
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2">
                            <button onclick="location.reload()" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #6b7280;">
                                <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2 ml-2">
                            <a href="{{ route('pedidos.relatorio-filtrado', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #dc2626;">
                                <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                            </a>
                            <a href="{{ route('pedidos.relatorio-filtrado', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #16a34a;">
                                <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                            </a>
                           
                        </div>
                    </div>

                    <a href="{{ route('pedidos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm shadow-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Pedido
                    </a>
                </div>

                @if($pedidos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Embarcação</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IMO/Viagem</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chegada/Partida</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pedidos as $pedido)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <i class="fas fa-ship text-blue-600 text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $pedido->nome_navio }}</div>
                                                    <div class="text-sm text-gray-500">{{ $pedido->bandeira_navio }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">IMO: {{ $pedido->numero_imo }}</div>
                                            <div class="text-sm text-gray-500">Viagem: {{ $pedido->numero_viagem }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <i class="fas fa-arrow-down text-green-500 mr-1"></i>
                                                {{ $pedido->data_chegada ? \Carbon\Carbon::parse($pedido->data_chegada)->format('d/m/Y') : '-' }}
                                                {{ $pedido->hora_chegada ? \Carbon\Carbon::parse($pedido->hora_chegada)->format('H:i') : '' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <i class="fas fa-arrow-up text-red-500 mr-1"></i>
                                                {{ $pedido->data_partida ? \Carbon\Carbon::parse($pedido->data_partida)->format('d/m/Y') : '-' }}
                                                {{ $pedido->hora_partida ? \Carbon\Carbon::parse($pedido->hora_partida)->format('H:i') : '' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $pedido->nome_agente }}</div>
                                            <div class="text-sm text-gray-500">{{ $pedido->contato_agente }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($pedido->status == 'pendente')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>Pendente
                                                </span>
                                            @elseif($pedido->status == 'aprovado')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>Aprovado
                                                </span>
                                            @elseif($pedido->status == 'rejeitado')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times mr-1"></i>Rejeitado
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('pedidos.show', $pedido) }}" class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @role('admin|supervisor_portuario')
                                                <a href="{{ route('pedidos.edit', $pedido) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                @endrole
                                                
                                                @role('admin')
                                                @if($pedido->status == 'pendente')
                                                    <form method="POST" action="{{ route('pedidos.aprovar', $pedido) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900" title="Aprovar" onclick="return confirm('Tem certeza que deseja aprovar este pedido?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('pedidos.rejeitar', $pedido) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Rejeitar" onclick="return confirm('Tem certeza que deseja rejeitar este pedido?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                 @endrole
                                                 @role('admin')
                                                <form method="POST" action="{{ route('pedidos.destroy', $pedido) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este pedido?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endrole
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $pedidos->links() }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <i class="fas fa-ship text-4xl"></i>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum pedido encontrado</h3>
                        <p class="mt-1 text-sm text-gray-500">Comece criando um novo pedido de entrada/saída.</p>
                        <div class="mt-6">
                            <a href="{{ route('pedidos.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-plus mr-2"></i>
                                Novo Pedido
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function gerarRelatorio() {
            // Capturar os filtros atuais do formulário
            const form = document.getElementById('filtros-form');
            const formData = new FormData(form);
            
            // Construir URL com parâmetros
            const params = new URLSearchParams();
            for (let [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }
            
            // Redirecionar para a rota de relatório com os filtros
            const url = '{{ route("pedidos.relatorio-filtrado") }}' + '?' + params.toString();
            window.open(url, '_blank');
        }
    </script>
</x-app-layout>