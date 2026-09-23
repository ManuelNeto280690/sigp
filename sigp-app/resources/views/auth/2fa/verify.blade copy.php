<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-4 text-sm text-gray-600">
                {{ __('Digite o código de verificação do seu aplicativo autenticador para continuar.') }}
            </div>

            <form method="POST" action="{{ route('2fa.check') }}">
                @csrf

                <!-- Código de Verificação -->
                <div class="mb-4">
                    <x-input-label for="code" :value="__('Código de Verificação')" />
                    <x-text-input id="code" class="block mt-1 w-full text-center text-2xl tracking-widest" type="text" name="code" :value="old('code')" required autofocus autocomplete="off" maxlength="6" placeholder="000000" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mt-6">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
                            {{ __('Sair') }}
                        </button>
                    </form>
                    
                    <x-primary-button>
                        {{ __('Verificar') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>