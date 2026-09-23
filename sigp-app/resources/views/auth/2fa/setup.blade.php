<x-guest-layout>
    <div class="text-center mb-4">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
            <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-800 mb-1">Configuração de Segurança</h2>
        <p class="text-gray-600 text-xs">
            @if($user->two_factor_required)
                Configure a autenticação de dois fatores para acessar o sistema
            @else
                Configure a autenticação de dois fatores para proteger sua conta (opcional)
            @endif
        </p>
    </div>

    <!-- Mensagens de Erro e Sucesso -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 text-sm mr-2"></i>
                <p class="text-xs font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 text-sm mr-2"></i>
                <p class="text-xs font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-red-400 text-sm mr-2 mt-0.5"></i>
                <div>
                    <h3 class="text-xs font-medium text-red-800 mb-1">Foram encontrados os seguintes erros:</h3>
                    <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                <div>
                    <p class="text-xs text-blue-800 font-medium mb-0.5">Por que usar 2FA?</p>
                    <p class="text-xs text-blue-700">A autenticação de dois fatores adiciona uma camada extra de segurança à sua conta.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('2fa.setup') }}" x-data="{ method: 'email', loading: false }" @submit="loading = true">
            @csrf

            <!-- Seleção do Método -->
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-700 mb-2">Escolha o método de autenticação:</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center p-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" :class="{ 'border-blue-500 bg-blue-50': method === 'email' }">
                        <input type="radio" name="method" value="email" x-model="method" class="text-blue-600 focus:ring-blue-500 mr-2">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-blue-600 mr-1 text-sm"></i>
                            <div>
                                <p class="text-xs font-medium text-gray-800">Email</p>
                                <p class="text-xs text-gray-600">Códigos por email</p>
                            </div>
                        </div>
                    </label>
                    
                    <label class="flex items-center p-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" :class="{ 'border-blue-500 bg-blue-50': method === 'authenticator' }">
                        <input type="radio" name="method" value="authenticator" x-model="method" class="text-blue-600 focus:ring-blue-500 mr-2">
                        <div class="flex items-center">
                            <i class="fas fa-mobile-alt text-blue-600 mr-1 text-sm"></i>
                            <div>
                                <p class="text-xs font-medium text-gray-800">Aplicativo</p>
                                <p class="text-xs text-gray-600">Google Auth, etc.</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Configuração por Email -->
            <div x-show="method === 'email'" x-transition class="mb-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2 mb-3">
                    <div class="flex items-start">
                        <i class="fas fa-envelope text-yellow-600 mt-0.5 mr-2 text-sm"></i>
                        <div>
                            <p class="text-xs text-yellow-800 font-medium mb-0.5">Configuração por Email</p>
                            <p class="text-xs text-yellow-700">Você receberá códigos de verificação no email especificado.</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email para 2FA</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 text-xs"></i>
                        </div>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email', $user->email) }}"
                            class="block w-full pl-7 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="seu@email.com"
                        >
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Pode ser diferente do email de login</p>
                </div>
            </div>

            <!-- Configuração por Authenticator -->
            <div x-show="method === 'authenticator'" x-transition class="mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 mb-3">
                    <div class="flex items-start">
                        <i class="fas fa-mobile-alt text-blue-600 mt-0.5 mr-2 text-sm"></i>
                        <div>
                            <p class="text-xs text-blue-800 font-medium mb-0.5">Configuração por Aplicativo</p>
                            <p class="text-xs text-blue-700">Use Google Authenticator, Authy ou similar.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- QR Code e Instruções -->
                    <div>
                        <div class="text-center mb-3">
                            <p class="text-xs font-medium text-gray-800 mb-2">1. Escaneie o código QR</p>
                            <div class="flex justify-center mb-2">
                                <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                                    @if(isset($qrCodeSvg) && !empty($qrCodeSvg))
                                        <div class="w-24 h-24 flex items-center justify-center">
                                            {!! $qrCodeSvg !!}
                                        </div>
                                    @else
                                        <div class="w-24 h-24 bg-red-50 border border-red-200 flex items-center justify-center text-red-600 text-xs text-center p-1">
                                            <div>
                                                <div class="mb-1">⚠️</div>
                                                <div class="text-xs">Erro ao gerar QR</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 mb-1">Ou digite manualmente:</p>
                                <div class="bg-gray-50 border border-gray-200 rounded p-1 inline-block">
                                    <code class="text-xs font-mono text-gray-700 break-all">{{ $secret ?? 'Carregando...' }}</code>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Código de Verificação -->
                    <div>
                        <p class="text-xs font-medium text-gray-800 mb-2">2. Digite o código de verificação</p>
                        <div class="mb-2">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-gray-400 text-xs"></i>
                                </div>
                                <input 
                                    type="text" 
                                    name="authenticator_code" 
                                    class="block w-full pl-7 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-center tracking-widest"
                                    maxlength="6" 
                                    placeholder="000000"
                                    pattern="[0-9]{6}"
                                    autocomplete="off"
                                >
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">Código de 6 dígitos do aplicativo</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmação de Senha -->
            <div class="mb-4">
                <label for="password" class="block text-xs font-medium text-gray-700 mb-1">Confirme sua senha</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400 text-xs"></i>
                    </div>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required
                        class="block w-full pl-7 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Sua senha atual"
                    >
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Digite sua senha atual para confirmar</p>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-between">
                @if(!$user->two_factor_required)
                    <a href="{{ route('dashboard') }}" class="text-xs text-gray-600 hover:text-gray-900 underline">
                        Pular por agora
                    </a>
                @else
                    <div></div>
                @endif
                
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center text-xs"
                    :disabled="loading"
                >
                    <span x-show="!loading">Configurar 2FA</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Configurando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>