<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    
    <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-dark mb-2">Bem-vindo de volta</h2>
                <p class="text-gray-600">Faça login em sua conta para continuar</p>
            </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPassword: false, loading: false }" @submit="loading = true">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" class="block text-xs font-medium text-gray-600 mb-1.5">
               
                E-mail
            </x-input-label>
            <div class="relative">
                <x-text-input id="email" 
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary pl-9 text-sm" 
                    type="email" 
                    name="email" 
                    :value="old('email')" 
                    required 
                    autofocus 
                    autocomplete="username" 
                    placeholder="seu@email.com" />
                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" class="block text-xs font-medium text-gray-600 mb-1.5">
                
                Senha
            </x-input-label>
            <div class="relative">
                <x-text-input id="password" 
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary pl-9 pr-9 text-sm"
                    ::type="showPassword ? 'text' : 'password'"
                    name="password"
                    required 
                    autocomplete="current-password" 
                    placeholder="••••••••" />
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                <button 
                    type="button" 
                    @click="showPassword = !showPassword" 
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-xs"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input id="remember_me" 
                    type="checkbox" 
                    class="w-3.5 h-3.5 text-primary bg-gray-50 border-gray-300 rounded focus:ring-primary focus:ring-1" 
                    name="remember">
                <span class="ml-2 text-xs text-gray-600">Lembrar de mim</span>
            </label>
            
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-primary hover:text-primary/80 font-medium transition-colors">
                    Esqueceu a senha?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <button 
            type="submit" 
            :disabled="loading" 
            class="w-full bg-gradient-to-r from-primary to-blue-600 text-white font-medium py-2.5 px-4 rounded-lg hover:from-primary/90 hover:to-blue-600/90 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
        >
            <span x-show="!loading" class="flex items-center justify-center">
                <i class="fas fa-sign-in-alt mr-2 text-sm"></i>
                Entrar
            </span>
            <span x-show="loading" class="flex items-center justify-center">
                <i class="fas fa-spinner fa-spin mr-2 text-sm"></i>
                Entrando...
            </span>
        </button>
    </form>
</x-guest-layout>
