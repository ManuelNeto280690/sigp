<x-guest-layout>
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Verificação de Segurança</h2>
        <p class="text-gray-600 text-sm">
            @if($user->two_factor_method === 'email')
                Um código foi enviado para <strong>{{ $user->two_factor_email }}</strong>
            @else
                Digite o código do seu aplicativo autenticador
            @endif
        </p>
    </div>

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

    <form method="POST" action="{{ route('2fa.check') }}" x-data="{ loading: false, countdown: 0 }" @submit="loading = true">
        @csrf

        <div class="mb-6">
            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Código de Verificação</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-key text-gray-400 text-sm"></i>
                </div>
                <input 
                    type="text" 
                    name="code" 
                    id="code" 
                    required
                    autofocus
                    maxlength="6" 
                    placeholder="000000"
                    pattern="[0-9]{6}"
                    autocomplete="off"
                    class="block w-full pl-10 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-center tracking-widest font-mono"
                >
            </div>
            <p class="text-xs text-gray-500 mt-1">
                @if($user->two_factor_method === 'email')
                    Digite o código de 6 dígitos enviado por email
                @else
                    Digite o código de 6 dígitos do seu aplicativo autenticador
                @endif
            </p>
        </div>

        <div class="flex items-center justify-between mb-6">
            @if($user->two_factor_method === 'email')
                <button 
                    type="button" 
                    id="resend-code"
                    @click="
                        fetch('{{ route('2fa.resend') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.message) {
                                alert(data.message);
                                countdown = 60;
                                const timer = setInterval(() => {
                                    countdown--;
                                    if (countdown <= 0) clearInterval(timer);
                                }, 1000);
                            } else {
                                alert(data.error || 'Erro ao reenviar código');
                            }
                        })
                        .catch(error => alert('Erro ao reenviar código'));
                    "
                    :disabled="countdown > 0"
                    class="text-sm text-blue-600 hover:text-blue-800 underline disabled:text-gray-400 disabled:no-underline"
                >
                    <span x-show="countdown <= 0">Reenviar código</span>
                    <span x-show="countdown > 0" x-text="`Aguarde ${countdown}s`"></span>
                </button>
            @else
                <div></div>
            @endif
            
            <div class="text-xs text-gray-500">
                Tentativas restantes: <span class="font-medium">{{ 5 - ($user->two_factor_failed_attempts ?? 0) }}</span>
            </div>
        </div>

        <button 
            type="submit" 
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
            :disabled="loading"
        >
            <span x-show="!loading">Verificar Código</span>
            <span x-show="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Verificando...
            </span>
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ route('logout') }}" 
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="text-sm text-gray-600 hover:text-gray-900 underline">
            Sair da conta
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</x-guest-layout>