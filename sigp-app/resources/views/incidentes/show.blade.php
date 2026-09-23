<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                        <i class="fas fa-exclamation-triangle text-white text-xs"></i>
                    </div>
                    Incidente #{{ $incidente->numero_incidente }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Detalhes do incidente registrado</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('incidentes.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
                <a href="{{ route('incidentes.exportPdf', $incidente) }}" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-file-pdf mr-1 text-xs"></i>Gerar PDF
                </a>
                @can('incidentes.edit')
                    @if($incidente->status === 'aberto' || $incidente->status === 'investigando')
                    <a href="{{ route('incidentes.edit', $incidente) }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                        <i class="fas fa-edit mr-1"></i>
                        Editar Incidente
                    </a>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Seção de Mensagens -->
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

            <!-- Cards de Status, Gravidade, Tipo e Data -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Status -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: #3b82f6;">Status</p>
                            <div class="mt-2">
                                @switch($incidente->status)
                                    @case('aberto')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Aberto
                                        </span>
                                        @break
                                    @case('investigando')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-search mr-1"></i>
                                            Em Investigação
                                        </span>
                                        @break
                                    @case('resolvido')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>
                                            Resolvido
                                        </span>
                                        @break
                                    @case('fechado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-lock mr-1"></i>
                                            Fechado
                                        </span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" style="background: #3b82f6;">
                            <i class="fas fa-info-circle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Gravidade -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Gravidade</p>
                            <div class="mt-2">
                                @switch($incidente->gravidade)
                                    @case('baixa')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-arrow-down mr-1"></i>
                                            Baixa
                                        </span>
                                        @break
                                    @case('media')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-minus mr-1"></i>
                                            Média
                                        </span>
                                        @break
                                    @case('alta')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            Alta
                                        </span>
                                        @break
                                    @case('critica')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-fire mr-1"></i>
                                            Crítica
                                        </span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Tipo -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-xs font-semibold uppercase tracking-wide">Tipo</p>
                            <p class="text-lg font-bold text-purple-900 mt-1 capitalize">{{ ucfirst($incidente->tipo) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-tag text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Data de Ocorrência -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: #3b82f6;">Data de Ocorrência</p>
                            <p class="text-lg font-bold mt-1" style="color: #1e40af;">
                                {{ $incidente->data_ocorrencia->format('d/m/Y') }}
                            </p>
                            <p class="text-xs mt-1" style="color: #3b82f6;">
                                {{ $incidente->data_ocorrencia->format('H:i') }} - {{ $incidente->data_ocorrencia->diffForHumans() }}
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" style="background: #3b82f6;">
                            <i class="fas fa-calendar text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do Incidente -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Dados Básicos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" style="background: #3b82f6;">
                            <i class="fas fa-info-circle text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Informações Básicas</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Número do Incidente</label>
                                <p class="text-sm text-gray-900 font-mono">{{ $incidente->numero_incidente }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Data de Registro</label>
                                <p class="text-sm text-gray-900">{{ $incidente->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Título</label>
                            <p class="text-sm text-gray-900">{{ $incidente->titulo }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Descrição</label>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $incidente->descricao }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Local da Ocorrência</label>
                            <p class="text-sm text-gray-900">{{ $incidente->local_ocorrencia }}</p>
                        </div>

                        @if($incidente->pessoas_envolvidas && count($incidente->pessoas_envolvidas) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Pessoas Envolvidas</label>
                            <div class="space-y-2">
                                @foreach($incidente->pessoas_envolvidas as $pessoa)
                                    <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                                        <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center mr-2">
                                            <i class="fas fa-user text-gray-600 text-xs"></i>
                                        </div>
                                        <span class="text-sm text-gray-900">
                                            @if(is_array($pessoa))
                                                {{ $pessoa['nome'] ?? 'Nome não informado' }}
                                                @if(isset($pessoa['funcao']))
                                                    <span class="text-gray-500">- {{ $pessoa['funcao'] }}</span>
                                                @endif
                                            @else
                                                {{ $pessoa }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Vinculações -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-link text-green-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Vinculações</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Registrado por</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-gray-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $incidente->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $incidente->user->email }}</p>
                                </div>
                            </div>
                        </div>

                        @if($incidente->responsavelInvestigacao)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Responsável pela Investigação</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-search text-orange-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $incidente->responsavelInvestigacao->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $incidente->responsavelInvestigacao->email }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($incidente->embarcacao)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Embarcação</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" style="background: #3b82f6;">
                                    <i class="fas fa-ship text-white text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $incidente->embarcacao->nome }}</p>
                                    <p class="text-xs text-gray-500">IMO: {{ $incidente->embarcacao->imo }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($incidente->terminal)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Terminal</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-warehouse text-purple-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $incidente->terminal->nome }}</p>
                                    <p class="text-xs text-gray-500">{{ $incidente->terminal->codigo }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($incidente->requer_notificacao_autoridades)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                <span class="text-sm font-medium text-yellow-800">Requer notificação às autoridades</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Análise e Investigação -->
            @if($incidente->causas_identificadas || $incidente->acoes_imediatas || $incidente->acoes_corretivas)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-clipboard-check text-yellow-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Análise e Investigação</h3>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    @if($incidente->causas_identificadas)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Causas Identificadas</label>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $incidente->causas_identificadas }}</p>
                        </div>
                    </div>
                    @endif

                    @if($incidente->acoes_imediatas)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Ações Imediatas</label>
                        <div class="bg-blue-50 rounded-lg p-3">
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $incidente->acoes_imediatas }}</p>
                        </div>
                    </div>
                    @endif

                    @if($incidente->acoes_corretivas)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Ações Corretivas</label>
                        <div class="bg-green-50 rounded-lg p-3">
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $incidente->acoes_corretivas }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                @if($incidente->data_fechamento)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-check text-green-600 mr-2"></i>
                        <span class="text-sm font-medium text-gray-900">
                            Incidente fechado em {{ $incidente->data_fechamento->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="flex items-center space-x-3">
            <!-- Ações Rápidas -->
             <a href="{{ route('incidentes.exportPdf', $incidente) }}" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-file-pdf mr-1 text-xs"></i>Gerar PDF
                </a>
            </div>
            @if($incidente->status !== 'fechado')
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3" style="background: #3b82f6;">
                        <i class="fas fa-bolt text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Ações Rápidas</h3>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    @can('incidentes.investigate')
                        @if($incidente->status === 'aberto')
                        <form method="POST" action="{{ route('incidentes.investigate', $incidente) }}" class="inline">
                            @csrf
                            <input type="hidden" name="responsavel_investigacao" value="{{ Auth::id() }}">
                            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-search mr-2"></i>
                                Iniciar Investigação
                            </button>
                        </form>
                        @endif
                    @endcan

                    @can('incidentes.close')
                        @if(in_array($incidente->status, ['aberto', 'investigando', 'resolvido']))
                        <button onclick="openCloseModal()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-lock mr-2"></i>
                            Fechar Incidente
                        </button>
                        @endif
                    @endcan

                    {{-- Removendo a funcionalidade de reabrir por enquanto, pois não há rota definida --}}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal de Fechamento Aprimorado -->
    <div id="closeModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-xl bg-white transform transition-all">
            <div class="text-center">
                <!-- Ícone -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <i class="fas fa-lock text-white text-2xl"></i>
                </div>
                
                <!-- Título -->
                <h3 class="text-xl font-bold text-gray-900 mb-2">Fechar Incidente</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Você está prestes a fechar o incidente <strong>#{{ $incidente->numero_incidente }}</strong>.<br>
                    <span class="text-red-600 font-medium">Esta ação não pode ser desfeita facilmente.</span>
                </p>
                
                <!-- Formulário -->
                <form method="POST" action="{{ route('incidentes.close', $incidente) }}" class="text-left">
                    @csrf
                    <div class="mb-6">
                        <label for="causas_identificadas" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clipboard-list mr-1" style="color: #3b82f6;"></i>
                            Causas Identificadas *
                        </label>
                        <textarea name="causas_identificadas" id="causas_identificadas" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm transition duration-200 focus:outline-none focus:ring-2 focus:border-transparent"
                                  style="focus:ring-color: #3b82f6;"
                                  placeholder="Descreva as causas identificadas do incidente..."
                                  required></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label for="acoes_corretivas" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tools mr-1" style="color: #3b82f6;"></i>
                            Ações Corretivas
                        </label>
                        <textarea name="acoes_corretivas" id="acoes_corretivas" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm transition duration-200 focus:outline-none focus:ring-2 focus:border-transparent"
                                  style="focus:ring-color: #3b82f6;"
                                  placeholder="Descreva as ações corretivas implementadas (opcional)..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Estas informações serão registradas permanentemente no histórico do incidente.
                        </p>
                    </div>
                    
                    <!-- Botões -->
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="closeCloseModal()" 
                                class="px-6 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition duration-200 flex items-center">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition duration-200 flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-lock mr-2"></i>
                            Confirmar Fechamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCloseModal() {
            document.getElementById('closeModal').classList.remove('hidden');
            // Focar no textarea quando o modal abrir
            setTimeout(() => {
                document.getElementById('observacoes_fechamento').focus();
            }, 100);
        }

        function closeCloseModal() {
            document.getElementById('closeModal').classList.add('hidden');
            // Limpar o textarea quando fechar
            document.getElementById('observacoes_fechamento').value = '';
        }

        // Fechar modal ao clicar fora
        document.getElementById('closeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCloseModal();
            }
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCloseModal();
            }
        });

        // Validação do textarea
        document.getElementById('observacoes_fechamento').addEventListener('input', function() {
            const submitBtn = this.closest('form').querySelector('button[type="submit"]');
            if (this.value.trim().length > 0) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
    </script>
</x-app-layout>