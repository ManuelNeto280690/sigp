<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-user text-white text-xs"></i>
                    </div>
                    {{ __('Detalhes do Usuário') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Visualize as informações completas do usuário</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
                @can('edit users')
                    <a href="{{ route('users.edit', $user) }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #0084de;">
                        <i class="fas fa-edit mr-1"></i>
                        Editar
                    </a>
                @endcan
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

            <!-- Conteúdo Principal -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Informações Básicas -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-user mr-2 text-blue-600"></i>
                                Informações Básicas
                            </h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">ID:</span>
                                    <code class="bg-gray-200 px-2 py-1 rounded text-sm">{{ $user->id }}</code>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Nome:</span>
                                    <span class="text-gray-900">{{ $user->name }}</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Email:</span>
                                    <span class="text-gray-900">{{ $user->email }}</span>
                                </div>
                                
                                @if($user->telefone)
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Telefone:</span>
                                    <span class="text-gray-900">{{ $user->telefone }}</span>
                                </div>
                                @endif
                                
                                @if($user->cargo)
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Cargo:</span>
                                    <span class="text-gray-900">{{ $user->cargo }}</span>
                                </div>
                                @endif
                                
                                @if($user->departamento)
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Departamento:</span>
                                    <span class="text-gray-900">{{ $user->departamento }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Configurações de Segurança -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-shield-alt mr-2 text-green-600"></i>
                                Configurações de Segurança
                            </h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-600">Email Verificado:</span>
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Verificado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Não Verificado
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-600">2FA:</span>
                                    @if($user->two_factor_enabled)
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-shield-alt mr-1"></i> Ativado
                                            </span>
                                            @if($user->two_factor_method)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ App\Models\User::getTwoFactorMethods()[$user->two_factor_method] ?? $user->two_factor_method }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-shield mr-1"></i> Desativado
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-600">Status:</span>
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Ativo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Inativo
                                        </span>
                                    @endif
                                </div>
                                
                                @if($user->last_login_at)
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Último Login:</span>
                                    <span class="text-gray-900 text-sm">{{ $user->last_login_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Roles e Permissões -->
                    @if($user->roles->count() > 0)
                    <div class="mt-6 bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user-tag mr-2 text-purple-600"></i>
                            Funções e Permissões
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Funções:</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" style="background-color: #0084de; color: white;">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            
                            @if($user->getAllPermissions()->count() > 0)
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Permissões:</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                    @foreach($user->getAllPermissions() as $permission)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-key mr-1"></i>
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>