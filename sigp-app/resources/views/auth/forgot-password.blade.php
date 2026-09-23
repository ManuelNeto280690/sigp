<x-guest-layout>
    <div class="w-full max-w-sm mx-auto">
        <!-- Header com ícone -->
        <div class="text-center mb-6">
            
            <h2 class="text-xl font-bold text-gray-800 mb-2">Recuperar Senha</h2>
            <p class="text-xs text-gray-600 leading-relaxed">
                Esqueceu sua senha? Sem problemas. Informe seu endereço de e-mail e enviaremos um link para redefinir sua senha.
            </p>
        </div>

        <!-- Card informativo -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-2">
                    <h3 class="text-xs font-medium text-blue-800">Como funciona</h3>
                    <p class="text-xs text-blue-700 mt-1">
                        Você receberá um e-mail com instruções para criar uma nova senha. Verifique também sua caixa de spam.
                    </p>
                </div>
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Formulário -->
        <div class="bg-white rounded-xl shadow-lg p-5 border border-gray-100" x-data="{ loading: false }">
            <form method="POST" action="{{ route('password.email') }}" @submit="loading = true">
                @csrf

                <!-- Email Address -->
                <div class="mb-4">
                    <label for="email" class="flex items-center text-xs font-medium text-gray-700 mb-2">
                        
                        E-mail
                    </label>
                    <div class="relative">
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                            class="w-full px-3 py-2 pl-9 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Digite seu e-mail"
                        >
                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                            <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Botão de envio -->
                <div class="mb-4">
                    <button 
                        type="submit" 
                        class="w-full text-white py-2 px-4 rounded-lg font-medium hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-[1.02] transition-all duration-200 shadow-lg"
                        style="background-color: #339de6;"
                        :disabled="loading"
                        :class="{ 'opacity-75 cursor-not-allowed': loading }"
                    >
                        <span x-show="!loading" class="flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Enviar Link de Recuperação
                        </span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Enviando...
                        </span>
                    </button>
                </div>

                <!-- Link para voltar ao login -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar ao Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
