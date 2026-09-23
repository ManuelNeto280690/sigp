<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-exclamation-triangle text-white text-xs"></i>
                    </div>
                    {{ $alerta->titulo }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Detalhes do alerta do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('alertas.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
                @if($alerta->status === 'ativo' && auth()->user()->can('alertas.resolve'))
                <form action="{{ route('alertas.resolve', $alerta) }}" method="POST" class="inline-block">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" onclick="return confirm('Tem certeza que deseja marcar este alerta como resolvido?')">
                        <i class="fas fa-check mr-1"></i>
                        Resolver Alerta
                    </button>
                </form>
                @endif
                @can('alertas.edit')
                <a href="{{ route('alertas.edit', $alerta) }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Alerta
                </a>
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

            <!-- Status e Nível do Alerta -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Status</p>
                            <p class="text-lg font-bold text-blue-900 mt-1">
                                @if($alerta->status === 'ativo')
                                    Ativo
                                @elseif($alerta->status === 'resolvido')
                                    Resolvido
                                @else
                                    Cancelado
                                @endif
                            </p>
                        </div>
                        <div class="w-10 h-10 
                            @if($alerta->status === 'ativo') bg-yellow-500
                            @elseif($alerta->status === 'resolvido') bg-green-500
                            @else bg-gray-500
                            @endif
                            rounded-lg flex items-center justify-center shadow-lg">
                            @if($alerta->status === 'ativo')
                                <i class="fas fa-exclamation text-white text-sm"></i>
                            @elseif($alerta->status === 'resolvido')
                                <i class="fas fa-check text-white text-sm"></i>
                            @else
                                <i class="fas fa-times text-white text-sm"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Nível</p>
                            <p class="text-lg font-bold text-orange-900 mt-1">{{ ucfirst($alerta->nivel) }}</p>
                        </div>
                        <div class="w-10 h-10 
                            @if($alerta->nivel === 'critica') bg-red-500
                            @elseif($alerta->nivel === 'alta') bg-orange-500
                            @elseif($alerta->nivel === 'media') bg-yellow-500
                            @else bg-blue-500
                            @endif
                            rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-600 text-xs font-semibold uppercase tracking-wide">Tipo</p>
                            <p class="text-lg font-bold text-purple-900 mt-1">{{ ucfirst($alerta->tipo) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                            @if($alerta->tipo === 'sistema')
                                <i class="fas fa-cog text-white text-sm"></i>
                            @elseif($alerta->tipo === 'seguranca')
                                <i class="fas fa-shield-alt text-white text-sm"></i>
                            @elseif($alerta->tipo === 'operacional')
                                <i class="fas fa-tools text-white text-sm"></i>
                            @elseif($alerta->tipo === 'ambiental')
                                <i class="fas fa-leaf text-white text-sm"></i>
                            @else
                                <i class="fas fa-wrench text-white text-sm"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Criado em</p>
                            <p class="text-lg font-bold text-green-900 mt-1">{{ $alerta->created_at->format('d/m/Y') }}</p>
                            <p class="text-green-600 text-xs">{{ $alerta->created_at->format('H:i') }}</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do Alerta -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Detalhes Principais -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Detalhes do Alerta</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ $alerta->titulo }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <div class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3 whitespace-pre-wrap">{{ $alerta->descricao }}</div>
                        </div>

                        @if($alerta->data_inicio)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Início</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ $alerta->data_inicio->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif

                        @if($alerta->data_fim)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Fim</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ $alerta->data_fim->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif

                        @if($alerta->areas_afetadas)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Áreas Afetadas</label>
                            <div class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">
                                @if(is_array($alerta->areas_afetadas))
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($alerta->areas_afetadas as $area)
                                            <li>{{ $area }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="whitespace-pre-wrap">{{ $alerta->areas_afetadas }}</div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($alerta->acoes_tomadas)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ações Tomadas</label>
                            <div class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3 whitespace-pre-wrap">{{ $alerta->acoes_tomadas }}</div>
                        </div>
                        @endif

                        @if($alerta->notificar_usuarios)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notificação de Usuários</label>
                            <div class="flex items-center bg-green-50 rounded-lg p-3">
                                <i class="fas fa-bell text-green-600 mr-2"></i>
                                <span class="text-sm text-green-800">Usuários foram notificados sobre este alerta</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Informações de Responsabilidade -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-tie text-purple-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Responsabilidade</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Criado por</label>
                            <div class="flex items-center bg-gray-50 rounded-lg p-3">
                                @if($alerta->user)
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-700">
                                                {{ substr($alerta->user->name, 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $alerta->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $alerta->user->email }}</p>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">Sistema</span>
                                @endif
                            </div>
                        </div>

                        @if($alerta->resolvido_por && $alerta->resolvidoPor)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Resolvido por</label>
                            <div class="flex items-center bg-gray-50 rounded-lg p-3">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-700">
                                            {{ substr($alerta->resolvidoPor->name, 0, 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $alerta->resolvidoPor->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $alerta->resolvidoPor->email }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Ativo</label>
                            <div class="flex items-center bg-gray-50 rounded-lg p-3">
                                @if($alerta->is_active)
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                    <span class="text-sm text-green-800">Alerta ativo no sistema</span>
                                @else
                                    <i class="fas fa-times-circle text-red-600 mr-2"></i>
                                    <span class="text-sm text-red-800">Alerta inativo no sistema</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline de Resolução -->
            @if($alerta->status === 'resolvido' && $alerta->resolvido_em)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Resolução</h3>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">Alerta resolvido</div>
                            <div class="text-sm text-gray-500">
                                {{ $alerta->resolvido_em->format('d/m/Y H:i') }}
                                @if($alerta->resolvidoPor)
                                    por {{ $alerta->resolvidoPor->name }}
                                @endif
                            </div>
                            @if($alerta->resolucao_observacoes)
                            <div class="mt-2 text-sm text-gray-700 bg-gray-50 rounded-lg p-3">
                                {{ $alerta->resolucao_observacoes }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-plus text-white text-xs"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">Alerta criado</div>
                            <div class="text-sm text-gray-500">
                                {{ $alerta->created_at->format('d/m/Y H:i') }}
                                @if($alerta->criador)
                                    por {{ $alerta->criador->name }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Ações Rápidas -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-bolt text-gray-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Ações Rápidas</h3>
                </div>

                <div class="flex flex-wrap gap-3">
                    @can('alertas.edit')
                    <a href="{{ route('alertas.edit', $alerta) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Editar Alerta
                    </a>
                    @endcan

                    @if($alerta->status === 'pendente' && auth()->user()->can('alertas.resolve'))
                    <form action="{{ route('alertas.resolve', $alerta) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200" onclick="return confirm('Tem certeza que deseja marcar este alerta como resolvido?')">
                            <i class="fas fa-check mr-2"></i>
                            Resolver Alerta
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('alertas.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                        <i class="fas fa-list mr-2"></i>
                        Ver Todos os Alertas
                    </a>

                    @if($alerta->concessionaria)
                    <a href="{{ route('concessionarias.show', $alerta->concessionaria) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-200">
                        <i class="fas fa-building mr-2"></i>
                        Ver Concessionária
                    </a>
                    @endif

                    @if($alerta->embarcacao)
                    <a href="{{ route('embarcacoes.show', $alerta->embarcacao) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-ship mr-2"></i>
                        Ver Embarcação
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>