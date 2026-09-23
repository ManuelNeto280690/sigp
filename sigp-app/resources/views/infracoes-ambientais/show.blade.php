<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-leaf text-white text-xs"></i>
                    </div>
                    Auto de Infração #{{ $infracao->numero_auto }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Detalhes da infração ambiental</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('infracoes-ambientais.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
                @can('infracoes-ambientais.edit')
                    @if(in_array($infracao->status, ['registrada', 'notificada', 'contestada']))
                    <a href="{{ route('infracoes-ambientais.edit', $infracao) }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #059669;">
                        <i class="fas fa-edit mr-1"></i>
                        Editar Infração
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
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Status</p>
                            <div class="mt-2">
                                @switch($infracao->status)
                                    @case('registrada')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Registrada
                                        </span>
                                        @break
                                    @case('notificada')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-bell mr-1"></i>
                                            Notificada
                                        </span>
                                        @break
                                    @case('contestada')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-gavel mr-1"></i>
                                            Contestada
                                        </span>
                                        @break
                                    @case('paga')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>
                                            Paga
                                        </span>
                                        @break
                                    @case('cancelada')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times mr-1"></i>
                                            Cancelada
                                        </span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-info-circle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Gravidade -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Gravidade</p>
                            <div class="mt-2">
                                @switch($infracao->gravidade)
                                    @case('leve')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-leaf mr-1"></i>
                                            Leve
                                        </span>
                                        @break
                                    @case('media')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-exclamation mr-1"></i>
                                            Média
                                        </span>
                                        @break
                                    @case('grave')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Grave
                                        </span>
                                        @break
                                    @case('gravissima')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-fire mr-1"></i>
                                            Gravíssima
                                        </span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-flag text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Tipo de Infração -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-xs font-semibold uppercase tracking-wide">Tipo</p>
                            <p class="text-sm font-bold text-purple-900 mt-1">{{ $infracao->getTipoInfracaoLabel() }}</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-tag text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Data da Infração -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Data da Infração</p>
                            <p class="text-sm font-bold text-blue-900 mt-1">
                                {{ $infracao->data_infracao ? $infracao->data_infracao->format('d/m/Y H:i') : 'Não informado' }}
                            </p>
                            <p class="text-blue-600 text-xs mt-1">
                                {{ $infracao->data_infracao ? $infracao->data_infracao->diffForHumans() : '' }}
                            </p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações da Infração -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Dados Básicos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-green-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Informações Básicas</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Número do Auto</label>
                                <p class="text-sm text-gray-900 font-mono">{{ $infracao->numero_auto }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Data de Registro</label>
                                <p class="text-sm text-gray-900">{{ $infracao->created_at ? $infracao->created_at->format('d/m/Y H:i') : 'Não informado' }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Local da Infração</label>
                            <p class="text-sm text-gray-900">{{ $infracao->local_infracao }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Descrição da Infração</label>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $infracao->descricao_infracao }}</p>
                        </div>

                        @if($infracao->medidas_corretivas)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Medidas Corretivas</label>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $infracao->medidas_corretivas }}</p>
                        </div>
                        @endif

                        @if($infracao->observacoes)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Observações</label>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $infracao->observacoes }}</p>
                        </div>
                        @endif

                      <!--  @if($infracao->valor_multa)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Valor da Multa</label>
                            <p class="text-lg font-bold text-red-600">R$ {{ number_format($infracao->valor_multa, 2, ',', '.') }}</p>
                        </div>
                        @endif

                        @if($infracao->prazo_regularizacao)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Prazo para Regularização</label>
                            <p class="text-sm text-gray-900">{{ $infracao->prazo_regularizacao ? $infracao->prazo_regularizacao->format('d/m/Y H:i') : 'Não definido' }}</p>
                            <p class="text-xs text-orange-600 mt-1">
                                @if($infracao->prazo_regularizacao && $infracao->prazo_regularizacao->isPast())
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Prazo vencido há {{ $infracao->prazo_regularizacao->diffForHumans() }}
                                @elseif($infracao->prazo_regularizacao)
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $infracao->prazo_regularizacao->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                        @endif -->
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
                        @if($infracao->inspetor)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Inspetor Responsável</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-gray-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $infracao->inspetor->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $infracao->inspetor->email }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($infracao->embarcacao)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Embarcação</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-ship text-blue-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $infracao->embarcacao->nome }}</p>
                                    @if($infracao->embarcacao->imo)
                                        <p class="text-xs text-gray-500">IMO: {{ $infracao->embarcacao->imo }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($infracao->terminal)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Terminal</label>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-warehouse text-purple-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $infracao->terminal->nome }}</p>
                                    @if($infracao->terminal->codigo)
                                        <p class="text-xs text-gray-500">{{ $infracao->terminal->codigo }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Evidências -->
            @if($infracao->evidencias && count($infracao->evidencias) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-camera text-yellow-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Evidências</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($infracao->evidencias as $evidencia)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">{{ $evidencia['nome'] ?? 'Evidência' }}</span>
                                <span class="text-xs text-gray-500">{{ $evidencia['tipo'] ?? 'Arquivo' }}</span>
                            </div>
                            @if(isset($evidencia['url']))
                                <a href="{{ $evidencia['url'] }}" target="_blank" class="text-green-600 hover:text-green-800 text-sm flex items-center">
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                    Visualizar
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Aprovação e Análise -->
            @if($infracao->aprovadoPor || $infracao->aprovado_em)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-clipboard-check text-green-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Aprovação e Análise</h3>
                </div>
                
                <div class="space-y-4">
                    @if($infracao->aprovadoPor)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Aprovado por</label>
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-2">
                                    <i class="fas fa-user text-green-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $infracao->aprovadoPor ? $infracao->aprovadoPor->name : 'Não informado' }}</p>
                                    <p class="text-xs text-gray-500">{{ $infracao->aprovadoPor ? $infracao->aprovadoPor->email : '' }}</p>
                                </div>
                            </div>
                        </div>
                        @if($infracao->aprovado_em)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Data de Aprovação</label>
                            <p class="text-sm text-gray-900">{{ $infracao->aprovado_em ? $infracao->aprovado_em->format('d/m/Y H:i') : 'Não aprovado' }}</p>
                            <p class="text-xs text-gray-500">{{ $infracao->aprovado_em ? $infracao->aprovado_em->diffForHumans() : '' }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Ações Rápidas -->
            <!--div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-cogs text-gray-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Ações Rápidas</h3>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    @can('infracoes-ambientais.edit')
                        @if(in_array($infracao->status, ['registrada', 'notificada', 'contestada']))
                        <a href="{{ route('infracoes-ambientais.edit', $infracao) }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition duration-200">
                            <i class="fas fa-edit mr-2"></i>
                            Editar Infração
                        </a>
                        @endif
                    @endcan

                    @if($infracao->status === 'registrada')
                        <button onclick="notificarInfracao('{{ $infracao->id }}')" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-200">
                            <i class="fas fa-bell mr-2"></i>
                            Notificar
                        </button>
                    @endif

                    @if(in_array($infracao->status, ['registrada', 'notificada', 'contestada']))
                        <button onclick="cancelarInfracao('{{ $infracao->id }}')" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                    @endif

                    <button onclick="gerarRelatorio('{{ $infracao->id }}')" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition duration-200">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Gerar Relatório
                    </button>
                </div>
            </div-->
        </div>
    </div>

    <!-- Modal de Notificação -->
    <div id="notificarModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                    <i class="fas fa-bell text-blue-600 text-lg"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Notificar Infração</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Confirma o envio da notificação da infração ambiental?
                    </p>
                    <textarea id="observacaoNotificacao" placeholder="Observações adicionais (opcional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" rows="3"></textarea>
                </div>
                <div class="flex items-center justify-center space-x-4 mt-4">
                    <button onclick="closeNotificarModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <button onclick="confirmarNotificacao()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition duration-200">
                        Notificar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cancelamento -->
    <div id="cancelarModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-times text-red-600 text-lg"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Cancelar Infração</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Tem certeza que deseja cancelar esta infração ambiental?
                    </p>
                    <textarea id="motivoCancelamento" placeholder="Motivo do cancelamento (obrigatório)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" rows="3" required></textarea>
                </div>
                <div class="flex items-center justify-center space-x-4 mt-4">
                    <button onclick="closeCancelarModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-400 transition duration-200">
                        Voltar
                    </button>
                    <button onclick="confirmarCancelamento()" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition duration-200">
                        Cancelar Infração
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let infracaoIdAtual = null;

        // Função para notificar infração
        function notificarInfracao(infracaoId) {
            infracaoIdAtual = infracaoId;
            document.getElementById('notificarModal').classList.remove('hidden');
        }

        // Função para cancelar infração
        function cancelarInfracao(infracaoId) {
            infracaoIdAtual = infracaoId;
            document.getElementById('cancelarModal').classList.remove('hidden');
        }

        // Função para gerar relatório
        function gerarRelatorio(infracaoId) {
            window.open(`/infracoes-ambientais/${infracaoId}/relatorio`, '_blank');
        }

        // Confirmar notificação
        function confirmarNotificacao() {
            const observacao = document.getElementById('observacaoNotificacao').value;
            
            fetch(`/infracoes-ambientais/${infracaoIdAtual}/notificar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    observacao: observacao
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao notificar infração: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar solicitação');
            });
        }

        // Confirmar cancelamento
        function confirmarCancelamento() {
            const motivo = document.getElementById('motivoCancelamento').value.trim();
            
            if (!motivo) {
                alert('Por favor, informe o motivo do cancelamento.');
                return;
            }
            
            fetch(`/infracoes-ambientais/${infracaoIdAtual}/cancelar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    motivo: motivo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao cancelar infração: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar solicitação');
            });
        }

        // Fechar modais
        function closeNotificarModal() {
            document.getElementById('notificarModal').classList.add('hidden');
            document.getElementById('observacaoNotificacao').value = '';
        }

        function closeCancelarModal() {
            document.getElementById('cancelarModal').classList.add('hidden');
            document.getElementById('motivoCancelamento').value = '';
        }

        // Fechar modais ao clicar fora
        document.getElementById('notificarModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotificarModal();
            }
        });

        document.getElementById('cancelarModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCancelarModal();
            }
        });
    </script>
    @endpush
</x-app-layout>