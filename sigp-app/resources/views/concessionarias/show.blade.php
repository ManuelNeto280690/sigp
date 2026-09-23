<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-building text-white text-xs"></i>
                    </div>
                    {{ $concessionaria->nome }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Detalhes da concessionária portuária</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('concessionarias.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
                @can('concessionarias.edit')
                <a href="{{ route('concessionarias.edit', $concessionaria) }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #8b5cf6;">
                    <i class="fas fa-edit mr-1"></i>
                    Editar Concessionária
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

            <!-- Estatísticas da Concessionária -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Embarcações</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['total_embarcacoes'] }}</p>
                            <p class="text-blue-600 text-xs mt-1">Vinculadas à concessionária</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-ship text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Embarcações Ativas</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['embarcacoes_ativas'] }}</p>
                            <p class="text-green-600 text-xs mt-1">Em operação</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-check-circle text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Movimentos Este Mês</p>
                            <p class="text-2xl font-bold text-orange-900 mt-1">{{ $stats['movimentos_mes'] }}</p>
                            <p class="text-orange-600 text-xs mt-1">Operações realizadas</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-exchange-alt text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações da Concessionária -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Dados Básicos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-purple-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Informações Básicas</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nome</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->nome }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">{{ $concessionaria->cnpj ? 'CNPJ' : 'NIF' }}</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->cnpj ?? $concessionaria->nif }}</p>
                            </div>
                        </div>

                        @if($concessionaria->inscricao_estadual || $concessionaria->inscricao_municipal)
                        <div class="grid grid-cols-2 gap-4">
                            @if($concessionaria->inscricao_estadual)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Inscrição Estadual</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->inscricao_estadual }}</p>
                            </div>
                            @endif
                            @if($concessionaria->inscricao_municipal)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Inscrição Municipal</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->inscricao_municipal }}</p>
                            </div>
                            @endif
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                @if($concessionaria->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Ativa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Inativa
                                    </span>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Data de Cadastro</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if($concessionaria->data_inicio_concessao || $concessionaria->data_fim_concessao)
                        <div class="grid grid-cols-2 gap-4">
                            @if($concessionaria->data_inicio_concessao)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Início da Concessão</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->data_inicio_concessao->format('d/m/Y') }}</p>
                            </div>
                            @endif
                            @if($concessionaria->data_fim_concessao)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Fim da Concessão</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->data_fim_concessao->format('d/m/Y') }}</p>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Informações de Contato -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-address-book text-blue-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Informações de Contato</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            @if($concessionaria->telefone)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Telefone</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->telefone }}</p>
                            </div>
                            @endif
                            @if($concessionaria->celular)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Celular</label>
                                <p class="text-sm text-gray-900">{{ $concessionaria->celular }}</p>
                            </div>
                            @endif
                        </div>

                        @if($concessionaria->endereco)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Endereço Completo</label>
                            <p class="text-sm text-gray-900">
                                {{ $concessionaria->endereco }}
                                @if($concessionaria->numero), {{ $concessionaria->numero }}@endif
                                @if($concessionaria->complemento) - {{ $concessionaria->complemento }}@endif
                                <br>
                                @if($concessionaria->bairro){{ $concessionaria->bairro }} - @endif
                                {{ $concessionaria->cidade }}/{{ $concessionaria->estado }}
                                @if($concessionaria->cep) - {{ $concessionaria->cep }}@endif
                            </p>
                        </div>
                        @endif

                        @if($concessionaria->responsavel_nome)
                        <div class="border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Responsável</h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Nome</label>
                                    <p class="text-sm text-gray-900">{{ $concessionaria->responsavel_nome }}</p>
                                </div>
                                @if($concessionaria->responsavel_cpf)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">CPF</label>
                                    <p class="text-sm text-gray-900">{{ $concessionaria->responsavel_cpf }}</p>
                                </div>
                                @endif
                                @if($concessionaria->responsavel_email)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                                    <p class="text-sm text-gray-900">{{ $concessionaria->responsavel_email }}</p>
                                </div>
                                @endif
                                @if($concessionaria->responsavel_telefone)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Telefone</label>
                                    <p class="text-sm text-gray-900">{{ $concessionaria->responsavel_telefone }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Observações -->
            @if($concessionaria->observacoes)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-sticky-note text-yellow-600 text-sm"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Observações</h3>
                </div>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $concessionaria->observacoes }}</p>
            </div>
            @endif

            <!-- Embarcações Vinculadas -->
            @if($concessionaria->embarcacoes->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-ship text-blue-600 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Embarcações Vinculadas</h3>
                    </div>
                    <span class="text-sm text-gray-500">{{ $concessionaria->embarcacoes->count() }} embarcações</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Embarcação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Movimento</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($concessionaria->embarcacoes->take(10) as $embarcacao)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $embarcacao->nome }}</div>
                                        <div class="text-sm text-gray-500">{{ $embarcacao->imo ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $embarcacao->tipo ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($embarcacao->is_active ?? true)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Ativa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Inativa
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($embarcacao->entradaSaidas && $embarcacao->entradaSaidas->first())
                                            {{ $embarcacao->entradaSaidas->first()->created_at->format('d/m/Y H:i') }}
                                        @else
                                            Nenhum movimento
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($concessionaria->embarcacoes->count() > 10)
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">Mostrando 10 de {{ $concessionaria->embarcacoes->count() }} embarcações</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Seção de Usuários Associados -->
        <div class="flex justify-center">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mt-5 max-w-2xl w-full">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3">
                    <h3 class="text-base font-semibold text-white flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M17 20H7m10 0v-2c0-5.523-3.477-10-10-10s-10 4.477-10 10v2m10 0H7m0 0H2v-2a3 3 0 015.196-2.121M7 20v-2m5-10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Usuários ({{ $concessionaria->users->count() }})
                    </h3>
                </div>
                <div class="p-4">
                    @if($concessionaria->users->count() > 0)
                        <div class="space-y-2 mb-4">
                            @foreach($concessionaria->users as $user)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-2">
                                            <p class="text-xs font-medium text-gray-900">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    @can('concessionarias.edit')
                                        <form method="POST" action="{{ route('concessionarias.remove-user', $concessionaria) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-800 p-1"
                                                    onclick="return confirm('Remover usuário?')">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 mb-4">
                            <svg class="mx-auto h-6 w-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M17 20H7m10 0v-2c0-5.523-3.477-10-10-10s-10 4.477-10 10v2m10 0H7m0 0H2v-2a3 3 0 015.196-2.121M7 20v-2m5-10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-xs text-gray-500">Nenhum usuário</p>
                        </div>
                    @endif

                    @can('concessionarias.edit')
                        @if($usuariosDisponiveis->count() > 0)
                            <div class="pt-3 border-t border-gray-200">
                                <form method="POST" action="{{ route('concessionarias.add-user', $concessionaria) }}" class="flex gap-2">
                                    @csrf
                                    <select name="user_id" 
                                            class="flex-1 px-2 py-1 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-blue-500"
                                            required>
                                        <option value="">Adicionar usuário...</option>
                                        @foreach($usuariosDisponiveis as $usuario)
                                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" 
                                            class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                        +
                                    </button>
                                </form>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 text-center pt-3 border-t border-gray-200">Sem usuários disponíveis</p>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

</x-app-layout>