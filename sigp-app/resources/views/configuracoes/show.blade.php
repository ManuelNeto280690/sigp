<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-eye text-white text-xs"></i>
                    </div>
                    {{ __('Visualizar Configuração') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Detalhes da configuração: {{ $configuracao->nome }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @can('update', $configuracao)
                    <a href="{{ route('configuracoes.edit', $configuracao) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                        <i class="fas fa-edit mr-1 text-xs"></i>Editar
                    </a>
                @endcan
                <a href="{{ route('configuracoes.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informações Básicas -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Chave</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <code class="text-sm text-gray-900">{{ $configuracao->chave }}</code>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <span class="text-sm text-gray-900">{{ $configuracao->nome }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $configuracao->tipo }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <span class="text-sm text-gray-900">{{ $configuracao->categoria ?? 'Não definida' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status e Metadados -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $configuracao->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas fa-{{ $configuracao->ativo ? 'check' : 'times' }} mr-1"></i>
                                        {{ $configuracao->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Visibilidade</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $configuracao->publico ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        <i class="fas fa-{{ $configuracao->publico ? 'globe' : 'lock' }} mr-1"></i>
                                        {{ $configuracao->publico ? 'Público' : 'Privado' }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Criado em</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <span class="text-sm text-gray-900">{{ $configuracao->created_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Última atualização</label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <span class="text-sm text-gray-900">{{ $configuracao->updated_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descrição -->
                    @if($configuracao->descricao)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-900">{{ $configuracao->descricao }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Valor -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Valor</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                            @switch($configuracao->tipo)
                                @case('boolean')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $configuracao->getValorTipado() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas fa-{{ $configuracao->getValorTipado() ? 'check' : 'times' }} mr-1"></i>
                                        {{ $configuracao->getValorTipado() ? 'Verdadeiro' : 'Falso' }}
                                    </span>
                                    @break
                                @case('json')
                                    <pre class="text-sm text-gray-900 whitespace-pre-wrap font-mono">{{ json_encode($configuracao->getValorTipado(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @break
                                @case('file')
                                    @if($configuracao->valor)
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-file-image text-blue-500"></i>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ basename($configuracao->valor) }}</p>
                                                <a href="{{ asset('storage/' . $configuracao->valor) }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-external-link-alt mr-1"></i>Visualizar arquivo
                                                </a>
                                            </div>
                                            @if(in_array(strtolower(pathinfo($configuracao->valor, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                                <img src="{{ asset('storage/' . $configuracao->valor) }}" alt="Preview" class="h-16 w-16 object-cover rounded">
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 italic">Nenhum arquivo</span>
                                    @endif
                                    @break
                                @default
                                    <span class="text-sm text-gray-900">{{ $configuracao->valor }}</span>
                            @endswitch
                        </div>
                    </div>

                    <!-- Regras de Validação -->
                    @if($configuracao->validacao)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Regras de Validação</label>
                            <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                <code class="text-sm text-gray-900">{{ $configuracao->validacao }}</code>
                            </div>
                        </div>
                    @endif

                    <!-- Opções -->
                    @if($configuracao->opcoes)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Opções</label>
                            <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                <pre class="text-sm text-gray-900 whitespace-pre-wrap font-mono">{{ json_encode($configuracao->opcoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- Ações -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 mt-6">
                        @can('delete', $configuracao)
                            <form method="POST" action="{{ route('configuracoes.destroy', $configuracao) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta configuração?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-trash mr-2"></i>Excluir
                                </button>
                            </form>
                        @endcan
                        @can('update', $configuracao)
                            <a href="{{ route('configuracoes.edit', $configuracao) }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                                <i class="fas fa-edit mr-2"></i>Editar
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>