<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Cabeçalho -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center mb-4 sm:mb-0">
                            <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-search text-teal-600"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Inspeção #{{ $inspecao->numero_inspecao }}</h2>
                                <p class="text-sm text-gray-600 mt-1">{{ $inspecao->local_inspecao }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('inspecoes.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Voltar
                            </a>
                            @can('inspecoes.edit')
                                <a href="{{ route('inspecoes.edit', $inspecao) }}" class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                                    <i class="fas fa-edit mr-2"></i>
                                    Editar
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensagens de Erro/Sucesso -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Cards de Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Status -->
                <div class="bg-gradient-to-br from-{{ $inspecao->status === 'concluida' ? 'green' : ($inspecao->status === 'em_andamento' ? 'blue' : ($inspecao->status === 'agendada' ? 'yellow' : 'gray')) }}-50 to-{{ $inspecao->status === 'concluida' ? 'green' : ($inspecao->status === 'em_andamento' ? 'blue' : ($inspecao->status === 'agendada' ? 'yellow' : 'gray')) }}-100 rounded-lg p-4 border border-{{ $inspecao->status === 'concluida' ? 'green' : ($inspecao->status === 'em_andamento' ? 'blue' : ($inspecao->status === 'agendada' ? 'yellow' : 'gray')) }}-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-{{ $inspecao->status === 'concluida' ? 'green' : ($inspecao->status === 'em_andamento' ? 'blue' : ($inspecao->status === 'agendada' ? 'yellow' : 'gray')) }}-600 text-xs font-semibold uppercase tracking-wide">Status</p>
                            <p class="text-lg font-bold text-{{ $inspecao->status === 'concluida' ? 'green' : ($inspecao->status === 'em_andamento' ? 'blue' : ($inspecao->status === 'agendada' ? 'yellow' : 'gray')) }}-900 mt-1 capitalize">
                                {{ str_replace('_', ' ', $inspecao->status) }}
                            </p>
                        </div>
                        <div class="w-10 h-10 bg-{{ $inspecao->status === 'concluida' ? 'green' : ($inspecao->status === 'em_andamento' ? 'blue' : ($inspecao->status === 'agendada' ? 'yellow' : 'gray')) }}-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-{{ $inspecao->status === 'concluida' ? 'check' : ($inspecao->status === 'em_andamento' ? 'play' : ($inspecao->status === 'agendada' ? 'clock' : 'pause')) }} text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Tipo -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-xs font-semibold uppercase tracking-wide">Tipo</p>
                            <p class="text-lg font-bold text-purple-900 mt-1 capitalize">{{ str_replace('_', ' ', $inspecao->tipo_inspecao) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-clipboard-list text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Resultado -->
                @if($inspecao->resultado)
                <div class="bg-gradient-to-br from-{{ $inspecao->resultado === 'aprovado' ? 'green' : ($inspecao->resultado === 'reprovado' ? 'red' : 'yellow') }}-50 to-{{ $inspecao->resultado === 'aprovado' ? 'green' : ($inspecao->resultado === 'reprovado' ? 'red' : 'yellow') }}-100 rounded-lg p-4 border border-{{ $inspecao->resultado === 'aprovado' ? 'green' : ($inspecao->resultado === 'reprovado' ? 'red' : 'yellow') }}-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-{{ $inspecao->resultado === 'aprovado' ? 'green' : ($inspecao->resultado === 'reprovado' ? 'red' : 'yellow') }}-600 text-xs font-semibold uppercase tracking-wide">Resultado</p>
                            <p class="text-lg font-bold text-{{ $inspecao->resultado === 'aprovado' ? 'green' : ($inspecao->resultado === 'reprovado' ? 'red' : 'yellow') }}-900 mt-1 capitalize">{{ $inspecao->resultado }}</p>
                        </div>
                        <div class="w-10 h-10 bg-{{ $inspecao->resultado === 'aprovado' ? 'green' : ($inspecao->resultado === 'reprovado' ? 'red' : 'yellow') }}-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-{{ $inspecao->resultado === 'aprovado' ? 'thumbs-up' : ($inspecao->resultado === 'reprovado' ? 'thumbs-down' : 'exclamation') }} text-white text-sm"></i>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Data Programada -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-indigo-600 text-xs font-semibold uppercase tracking-wide">Data Programada</p>
                            <p class="text-lg font-bold text-indigo-900 mt-1">
                                {{ $inspecao->data_programada ? $inspecao->data_programada->format('d/m/Y') : 'Não definida' }}
                            </p>
                            @if($inspecao->data_programada)
                                <p class="text-indigo-600 text-xs mt-1">
                                    {{ $inspecao->data_programada->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                        <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações da Inspeção -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Dados Básicos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-teal-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Informações Básicas</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Número da Inspeção</label>
                                <p class="text-sm text-gray-900 font-mono">{{ $inspecao->numero_inspecao }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Data de Criação</label>
                                <p class="text-sm text-gray-900">{{ $inspecao->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Local da Inspeção</label>
                            <p class="text-sm text-gray-900">{{ $inspecao->local_inspecao }}</p>
                        </div>

                        @if($inspecao->objetivo)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Objetivo</label>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $inspecao->objetivo }}</p>
                        </div>
                        @endif

                        @if($inspecao->observacoes)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Observações</label>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $inspecao->observacoes }}</p>
                        </div>
                        @endif

                        @if($inspecao->data_inicio)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Data de Início</label>
                                <p class="text-sm text-gray-900">{{ $inspecao->data_inicio->format('d/m/Y H:i') }}</p>
                            </div>
                            @if($inspecao->data_conclusao)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Data de Conclusão</label>
                                <p class="text-sm text-gray-900">{{ $inspecao->data_conclusao->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Vinculações -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-link text-blue-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Vinculações</h3>
                    </div>
                    
                    <div class="space-y-4">
                        @if($inspecao->inspetor)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Inspetor Responsável</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-gray-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $inspecao->inspetor->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $inspecao->inspetor->email }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($inspecao->embarcacao)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Embarcação</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-ship text-teal-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $inspecao->embarcacao->nome }}</p>
                                    <p class="text-xs text-gray-500">IMO: {{ $inspecao->embarcacao->imo }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($inspecao->terminal)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Terminal</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-warehouse text-purple-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $inspecao->terminal->nome }}</p>
                                    <p class="text-xs text-gray-500">{{ $inspecao->terminal->codigo }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($inspecao->user)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Criado por</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-gray-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $inspecao->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $inspecao->user->email }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Relatório Final -->
            @if($inspecao->relatorio_final)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-file-alt text-green-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Relatório Final</h3>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $inspecao->relatorio_final }}</p>
                </div>
            </div>
            @endif

            <!-- Detalhes Técnicos -->
            @if($inspecao->detalhes_inspecao && count($inspecao->detalhes_inspecao) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-cogs text-indigo-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Detalhes Técnicos</h3>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ json_encode($inspecao->detalhes_inspecao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
            @endif

            <!-- Ações Rápidas -->
            @can('inspecoes.manage')
                @if($inspecao->status !== 'concluida' && $inspecao->status !== 'cancelada')
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-bolt text-teal-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Ações Rápidas</h3>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        @if($inspecao->status === 'agendada')
                        <form action="{{ route('inspecoes.start', $inspecao) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                                <i class="fas fa-play mr-2"></i>
                                Iniciar Inspeção
                            </button>
                        </form>
                        @endif

                        @if($inspecao->status === 'em_andamento')
                        <button onclick="showCompleteModal()" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                            <i class="fas fa-check mr-2"></i>
                            Concluir Inspeção
                        </button>
                        @endif

                        <form action="{{ route('inspecoes.update-status', $inspecao) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelada">
                            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm" onclick="return confirm('Tem certeza que deseja cancelar esta inspeção?')">
                                <i class="fas fa-ban mr-2"></i>
                                Cancelar
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            @endcan
        </div>
    </div>

    <!-- Modal de Conclusão -->
    <div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center mb-4">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Concluir Inspeção</h3>
                    <form action="{{ route('inspecoes.complete', $inspecao) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="resultado" class="block text-sm font-medium text-gray-700 mb-2">Resultado</label>
                            <select name="resultado" id="resultado" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent" required>
                                <option value="">Selecione o resultado</option>
                                <option value="aprovado">Aprovado</option>
                                <option value="reprovado">Reprovado</option>
                                <option value="aprovado_com_ressalvas">Aprovado com Ressalvas</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="relatorio_final" class="block text-sm font-medium text-gray-700 mb-2">Relatório Final</label>
                            <textarea name="relatorio_final" id="relatorio_final" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent" placeholder="Descreva os resultados da inspeção..." required></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="hideCompleteModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-200">
                                Cancelar
                            </button>
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                Concluir Inspeção
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showCompleteModal() {
            document.getElementById('completeModal').classList.remove('hidden');
        }

        function hideCompleteModal() {
            document.getElementById('completeModal').classList.add('hidden');
            document.getElementById('resultado').value = '';
            document.getElementById('relatorio_final').value = '';
        }

        // Fechar modal ao clicar fora
        document.getElementById('completeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideCompleteModal();
            }
        });
    </script>
</x-app-layout>