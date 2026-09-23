<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Autenticação de Dois Fatores') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Gerencie suas configurações de autenticação de dois fatores para maior segurança.') }}
        </p>
    </header>

    @if(auth()->user()->two_factor_enabled)
        <div class="mt-6 space-y-6">
            <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium text-green-800">{{ __('2FA Habilitado') }}</span>
                </div>
                <span class="text-xs text-green-600">{{ __('Sua conta está protegida') }}</span>
            </div>

            <form method="POST" action="{{ route('2fa.disable') }}" class="space-y-4">
                @csrf
                
                <div>
                    <x-input-label for="password" :value="__('Senha Atual')" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                
                <div>
                    <x-input-label for="code" :value="__('Código de Verificação')" />
                    <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" maxlength="6" placeholder="000000" required />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <x-danger-button onclick="return confirm('{{ __('Tem certeza que deseja desabilitar a autenticação de dois fatores?') }}')">
                    {{ __('Desabilitar 2FA') }}
                </x-danger-button>
            </form>
        </div>
    @else
        <div class="mt-6">
            <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium text-yellow-800">{{ __('2FA Desabilitado') }}</span>
                </div>
                <a href="{{ route('2fa.setup') }}" class="text-sm text-blue-600 hover:text-blue-800 underline">
                    {{ __('Configurar Agora') }}
                </a>
            </div>
        </div>
    @endif
</section>