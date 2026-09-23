<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Detalhes do Movimento') }} #{{ $movimento->numero_movimento }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Visualize todas as informações do movimento</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
                
                @if($movimento->podeEditar())
                    <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.edit', $movimento) }}" class="text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs" style="background: #0084de;">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </a>
                @endif
                
                // Linha 18 - Link editar
                <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.edit', $movimento) }}" class="text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs" style="background: #0084de;">
                
                // Linha 70 - Form autorizar
                <form action="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.autorizar', $movimento) }}" method="POST" class="inline">
                
                // Linha 78 - Form iniciar
                <form action="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.iniciar', $movimento) }}" method="POST" class="inline">
                
                // Linha 86 - Form concluir
                <form action="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.concluir', $movimento) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja autorizar este movimento?')">
                        <i class="fas fa-check mr-1"></i>Autorizar
                    </button>
                </form>
            @if($movimento->status == 'autorizado' && auth()->user()->can('iniciar-movimentos'))
                <form action="{{ route('entrada-saida-embarcacao.iniciar', $movimento) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja iniciar este movimento?')">
                        <i class="fas fa-play mr-1"></i>Iniciar
                    </button>
                </form>
            @endif
            @if($movimento->status == 'em_andamento' && auth()->user()->can('concluir-movimentos'))
                <form action="{{ route('entrada-saida-embarcacao.concluir', $movimento) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja concluir este movimento?')">
                        <i class="fas fa-flag-checkered mr-1"></i>Concluir
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

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

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Status e Ações Rápidas -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle mr-2" style="color: #0084de;"></i>
                                Status do Movimento
                            </h3>
                        </div>
                        <div class="flex items-center space-x-3">
                            @php
                                $statusColors = [
                                    'programado' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'autorizado' => 'bg-green-100 text-green-800 border-green-200',
                                    'em_andamento' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'concluido' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'cancelado' => 'bg-red-100 text-red-800 border-red-200'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold border {{ $statusColors[$movimento->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                {{ $movimento->getStatusLabel() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-md {{ $movimento->tipo_movimento == 'entrada' ? 'bg-green-500' : 'bg-red-500' }}">
                                <i class="fas {{ $movimento->tipo_movimento == 'entrada' ? 'fa-arrow-down' : 'fa-arrow-up' }} text-white text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900">{{ ucfirst($movimento->tipo_movimento) }} de Embarcação</h4>
                                <p class="text-gray-600">Movimento #{{ $movimento->numero_movimento }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if($movimento->status == 'programado' && auth()->user()->can('autorizar-movimentos'))
                                <form action="{{ route('entrada-saida-embarcacao.autorizar', $movimento) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja autorizar este movimento?')">
                                        <i class="fas fa-check mr-1"></i>Autorizar
                                    </button>
                                </form>
                            @endif
                            @if($movimento->status == 'autorizado' && auth()->user()->can('iniciar-movimentos'))
                                <form action="{{ route('entrada-saida-embarcacao.iniciar', $movimento) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja iniciar este movimento?')">
                                        <i class="fas fa-play mr-1"></i>Iniciar
                                    </button>
                                </form>
                            @endif
                            @if($movimento->status == 'em_andamento' && auth()->user()->can('concluir-movimentos'))
                                <form action="{{ route('entrada-saida-embarcacao.concluir', $movimento) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja concluir este movimento?')">
                                        <i class="fas fa-flag-checkered mr-1"></i>Concluir
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Principais -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Dados da Embarcação -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-ship mr-2" style="color: #0084de;"></i>
                            Embarcação
                        </h3>
                    </div>
                    <div class="p-6">
                      
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $movimento->embarcacaoConcessionaria->nome }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                        <p class="text-gray-900">{{ $movimento->embarcacaoConcessionaria->tipo ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bandeira</label>
                                        <p class="text-gray-900">{{ $movimento->embarcacaoConcessionaria->bandeira ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Comprimento</label>
                                        <p class="text-gray-900">{{ $movimento->embarcacaoConcessionaria->comprimento ? $movimento->embarcacaoConcessionaria->comprimento . 'm' : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Calado</label>
                                        <p class="text-gray-900">{{ $movimento->embarcacaoConcessionaria->calado ? $movimento->embarcacaoConcessionaria->calado . 'm' : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                      
                    </div>
                </div>

                <!-- Dados do Terminal -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-anchor mr-2" style="color: #0084de;"></i>
                            Terminal
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($movimento->terminal)
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $movimento->terminal->nome }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Código</label>
                                        <p class="text-gray-900">{{ $movimento->terminal->codigo ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                        <p class="text-gray-900">{{ $movimento->terminal->tipo ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                @if($movimento->terminal->localizacao)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Localização</label>
                                        <p class="text-gray-900">{{ $movimento->terminal->localizacao }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-500">Informações do terminal não disponíveis</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cronograma -->
            <!--div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock mr-2" style="color: #0084de;"></i>
                        Cronograma
                    </h3>
                </div>
                <div class="p-6">
                    <!--div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900">Programado</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data</label>
                                    <p class="text-gray-900">{{ $movimento->data_programada ? $movimento->data_programada->format('d/m/Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hora</label>
                                    <p class="text-gray-900">{{ $movimento->hora_programada ? $movimento->hora_programada->format('H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900">Real</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data</label>
                                    <p class="text-gray-900">{{ $movimento->data_real ? $movimento->data_real->format('d/m/Y') : 'Não informado' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hora</label>
                                    <p class="text-gray-900">{{ $movimento->hora_real ? $movimento->hora_real->format('H:i') : 'Não informado' }}</p>
                                </div>
                            </div>
                        </div>
                    </div->

                    @if($movimento->getDuracaoEstimada())
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Duração Estimada</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $movimento->getDuracaoEstimada() }}</p>
                                </div>
                                @if($movimento->isAtrasado())
                                    <div class="bg-red-100 text-red-800 px-3 py-2 rounded-lg">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Atrasado
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div-->

            <!-- Observações e Documentos -->
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
                <!-- Observações -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-comment-alt mr-2" style="color: #0084de;"></i>
                            Observações
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($movimento->observacoes)
                            <p class="text-gray-900 whitespace-pre-line">{{ $movimento->observacoes }}</p>
                        @else
                            <p class="text-gray-500 italic">Nenhuma observação registrada</p>
                        @endif
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
              

                <!-- Documentos -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-file-alt mr-2" style="color: #0084de;"></i>
                            Documentos
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($movimento->documentos_apresentados && count($movimento->documentos_apresentados) > 0)
                            <div class="space-y-3">
                                @foreach($movimento->documentos_apresentados as $documento)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <i class="fas fa-file text-gray-400 mr-3"></i>
                                            <span class="text-sm text-gray-900">{{ $documento['nome_original'] ?? basename($documento['caminho'] ?? $documento) }}</span>
                                        </div>
                                        <a href="{{ Storage::url($documento['caminho'] ?? $documento) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">Nenhum documento anexado</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informações de Auditoria -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history mr-2" style="color: #0084de;"></i>
                        Informações de Auditoria
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Criado por</label>
                            <p class="text-gray-900">{{ $movimento->usuario->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">{{ $movimento->created_at ? $movimento->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>

                        @if($movimento->autorizado_por)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Autorizado por</label>
                                <p class="text-gray-900">{{ $movimento->autorizadoPor->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">{{ $movimento->data_autorizacao ? $movimento->data_autorizacao->format('d/m/Y H:i') : 'N/A' }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Última atualização</label>
                            <p class="text-gray-900">{{ $movimento->updated_at ? $movimento->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>