
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-users text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Usuários') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Controle e monitore todos os usuários do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <a href="{{ route('users.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #0084de;">
                    <i class="fas fa-plus mr-1"></i>
                    Novo Usuário
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
            
            <!-- Estatísticas Compactas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1" style="background: linear-gradient(135deg, rgba(0, 132, 222, 0.1), rgba(0, 132, 222, 0.2)); border-color: #0084de;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: #0084de;">Total de Usuários</p>
                            <p class="text-2xl font-bold mt-1" style="color: #0084de;">{{ $users->total() }}</p>
                            <p class="text-xs mt-1" style="color: #0084de;">Registrados no sistema</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" style="background: #0084de;">
                            <i class="fas fa-users text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-600 text-xs font-semibold uppercase tracking-wide">Usuários Ativos</p>
                            <p class="text-2xl font-bold text-green-900 mt-1">{{ $users->where('is_active', true)->count() }}</p>
                            <p class="text-green-600 text-xs mt-1">Com acesso ativo</p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-check text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1" style="background: linear-gradient(135deg, rgba(0, 132, 222, 0.1), rgba(0, 132, 222, 0.2)); border-color: #0084de;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: #0084de;">Com 2FA Ativo</p>
                            <p class="text-2xl font-bold mt-1" style="color: #0084de;">{{ $users->where('two_factor_enabled', true)->count() }}</p>
                            <p class="text-xs mt-1" style="color: #0084de;">Segurança reforçada</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" style="background: #0084de;">
                            <i class="fas fa-shield-alt text-white text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-600 text-xs font-semibold uppercase tracking-wide">Administradores</p>
                            <p class="text-2xl font-bold text-orange-900 mt-1">{{ $users->filter(function($user) { return $user->hasRole('admin'); })->count() }}</p>
                            <p class="text-orange-600 text-xs mt-1">Acesso total</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-shield text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Avançados -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter mr-2 text-xs" style="color: #0084de;"></i>
                        Filtros de Pesquisa
                    </h3>
                </div>
                <div class="p-4">
                    <form method="GET" action="{{ route('users.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Pesquisar</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, email, telefone..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                                    <option value="">Todos os status</option>
                                    <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                    <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                                    <option value="bloqueado" {{ request('status') == 'bloqueado' ? 'selected' : '' }}>Bloqueado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Cargo</label>
                                <input type="text" name="cargo" value="{{ request('cargo') }}" placeholder="Filtrar por cargo" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Departamento</label>
                                <input type="text" name="departamento" value="{{ request('departamento') }}" placeholder="Filtrar por departamento" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <button type="submit" class="text-white px-4 py-2 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg" style="background: #0084de;">
                                <i class="fas fa-search mr-1 text-xs"></i>Filtrar
                            </button>
                            <a href="{{ route('users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-xs font-medium transition duration-200 flex items-center">
                                <i class="fas fa-times mr-1 text-xs"></i>Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Usuários Aprimorada -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-list mr-2 text-xs" style="color: #0084de;"></i>
                                Lista de Usuários
                            </h3>
                            <p class="text-xs text-gray-600 mt-1">{{ $users->total() }} usuários encontrados</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('users.create') }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #0084de;">
                                <i class="fas fa-plus mr-1 text-xs"></i>Adicionar Usuário
                            </a>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Função</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atividade</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center shadow-md" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                                                    @if($user->avatar)
                                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                                                    @else
                                                        <span class="text-white text-xs font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-xs font-bold text-gray-900">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-500 flex items-center">
                                                    <i class="fas fa-envelope mr-1 text-xs"></i>
                                                    {{ $user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="space-y-1">
                                            @if($user->phone)
                                                <div class="text-xs font-medium text-gray-900 flex items-center">
                                                    <span class="text-white text-xs font-semibold px-2 py-0.5 rounded mr-2" style="background: #0084de;">Tel</span>
                                                    {{ $user->phone }}
                                                </div>
                                            @endif
                                            @if($user->department)
                                                <div class="text-xs text-gray-500 flex items-center">
                                                    <i class="fas fa-building mr-1 text-xs"></i>
                                                    {{ $user->department }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="space-y-1">
                                            @if($user->position)
                                                <div class="text-xs font-medium text-gray-900">{{ $user->position }}</div>
                                            @endif
                                            @if($user->roles->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($user->roles as $role)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white" style="background: #0084de;">
                                                            {{ $role->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="space-y-1">
                                            @if($user->is_active)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                                                    Ativo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1"></span>
                                                    Inativo
                                                </span>
                                            @endif
                                            @if($user->two_factor_enabled)
                                                <div class="text-xs text-green-600 flex items-center">
                                                    <i class="fas fa-shield-alt mr-1 text-xs"></i>
                                                    2FA Ativo
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs text-gray-900 font-medium">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y') : 'Nunca' }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->last_login_at ? $user->last_login_at->format('H:i') : '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="flex justify-end space-x-1">
                                            <a href="{{ route('users.show', $user) }}" 
                                               class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                               style="background: #0084de;"
                                               title="Visualizar">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user) }}" 
                                               class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                               style="background: #0084de;"
                                               title="Editar">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                            <!--
                                            @if($user->is_active)
                                                <button onclick="confirmBlock('{{ $user->id }}', '{{ $user->name }}')" 
                                                        class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                                        style="background: #f59e0b;"
                                                        title="Bloquear">
                                                    <i class="fas fa-ban text-xs"></i>
                                                </button>
                                            @else
                                                <button onclick="confirmUnblock('{{ $user->id }}', '{{ $user->name }}')" 
                                                        class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                                        style="background: #10b981;"
                                                        title="Desbloquear">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            @endif -->
                                            <button onclick="confirmDelete('{{ $user->id }}', '{{ $user->name }}')" 
                                                    class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                                    style="background: #3b82f6;"
                                                    title="Excluir">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                <i class="fas fa-users text-gray-400 text-xl"></i>
                                            </div>
                                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Nenhum usuário encontrado</h3>
                                            <p class="text-gray-500 mb-4 text-xs">Não há usuários que correspondam aos filtros aplicados.</p>
                                            <a href="{{ route('users.create') }}" class="text-white font-semibold py-2 px-4 rounded-md transition duration-200 flex items-center text-xs" style="background: #0084de;">
                                                <i class="fas fa-plus mr-2"></i>Adicionar Primeiro Usuário
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginação Compacta -->
                @if($users->hasPages())
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-700">
                                Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} resultados
                            </div>
                            <div>
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm transform transition-all">
            <div class="p-4">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-3">
                    <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Confirmar Exclusão</h3>
                <p class="text-gray-600 text-center mb-4 text-sm">
                    Tem certeza que deseja excluir o usuário <strong id="userNome" class="text-gray-900"></strong>?
                    <br><span class="text-xs text-red-600">Esta ação não pode ser desfeita.</span>
                </p>
                <div class="flex space-x-2">
                    <button onclick="closeDeleteModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-3 rounded-md transition duration-200 text-xs">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-white font-semibold py-2 px-3 rounded-md transition duration-200 text-xs" style="background: #3b82f6;">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Bloqueio -->
    <div id="blockModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm transform transition-all">
            <div class="p-4">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full mb-3">
                    <i class="fas fa-ban text-yellow-600 text-lg"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Confirmar Bloqueio</h3>
                <p class="text-gray-600 text-center mb-4 text-sm">
                    Tem certeza que deseja bloquear o usuário <strong id="blockUserNome" class="text-gray-900"></strong>?
                </p>
                <div class="flex space-x-2">
                    <button onclick="closeBlockModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-3 rounded-md transition duration-200 text-xs">
                        Cancelar
                    </button>
                    <form id="blockForm" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full text-white font-semibold py-2 px-3 rounded-md transition duration-200 text-xs" style="background: #f59e0b;">
                            Bloquear
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Desbloqueio -->
    <div id="unblockModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-sm transform transition-all">
            <div class="p-4">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full mb-3">
                    <i class="fas fa-check text-green-600 text-lg"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Confirmar Desbloqueio</h3>
                <p class="text-gray-600 text-center mb-4 text-sm">
                    Tem certeza que deseja desbloquear o usuário <strong id="unblockUserNome" class="text-gray-900"></strong>?
                </p>
                <div class="flex space-x-2">
                    <button onclick="closeUnblockModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-3 rounded-md transition duration-200 text-xs">
                        Cancelar
                    </button>
                    <form id="unblockForm" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full text-white font-semibold py-2 px-3 rounded-md transition duration-200 text-xs" style="background: #10b981;">
                            Desbloquear
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id, nome) {
            document.getElementById('userNome').textContent = nome;
            document.getElementById('deleteForm').action = `/users/${id}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function confirmBlock(id, nome) {
            document.getElementById('blockUserNome').textContent = nome;
            document.getElementById('blockForm').action = `/users/${id}/block`;
            document.getElementById('blockModal').classList.remove('hidden');
        }

        function closeBlockModal() {
            document.getElementById('blockModal').classList.add('hidden');
        }

        function confirmUnblock(id, nome) {
            document.getElementById('unblockUserNome').textContent = nome;
            document.getElementById('unblockForm').action = `/users/${id}/unblock`;
            document.getElementById('unblockModal').classList.remove('hidden');
        }

        function closeUnblockModal() {
            document.getElementById('unblockModal').classList.add('hidden');
        }

        function refreshData() {
            window.location.reload();
        }

        // Fechar modais ao clicar fora
        ['deleteModal', 'blockModal', 'unblockModal'].forEach(modalId => {
            document.getElementById(modalId).addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        });

        // Animação de entrada para as linhas da tabela
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
    @endpush
</x-app-layout>

