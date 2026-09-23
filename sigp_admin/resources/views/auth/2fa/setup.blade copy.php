<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-4 text-sm text-gray-600">
                {{ __('Para sua segurança, você deve configurar a autenticação de dois fatores antes de continuar.') }}
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Configurar Autenticação de Dois Fatores') }}</h3>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">
                        {{ __('1. Instale um aplicativo autenticador como Google Authenticator ou Authy em seu dispositivo móvel.') }}
                    </p>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('2. Escaneie o código QR abaixo com seu aplicativo autenticador:') }}
                    </p>
                    
                    <div class="flex justify-center mb-4">
                        <div class="p-4 bg-white border border-gray-300 rounded">
                            {!! $qrCodeSvg !!}
                        </div>
                    </div>
                    
                    <div class="text-center mb-4">
                        <p class="text-xs text-gray-500 mb-2">{{ __('Ou digite manualmente este código:') }}</p>
                        <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">{{ $secret }}</code>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('2fa.enable') }}">
                @csrf

                <div class="mb-4">
                    <x-input-label for="code" :value="__('Código de Verificação')" />
                    <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required autofocus autocomplete="off" maxlength="6" placeholder="000000" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    <p class="text-xs text-gray-500 mt-1">{{ __('Digite o código de 6 dígitos do seu aplicativo autenticador') }}</p>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <x-primary-button class="w-full justify-center">
                        {{ __('Habilitar Autenticação de Dois Fatores') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>