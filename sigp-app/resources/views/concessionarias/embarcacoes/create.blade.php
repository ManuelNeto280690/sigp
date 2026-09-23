<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0086e1 0%, #01043d 100%);">
                    <i class="fas fa-plus text-white text-sm"></i>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Nova Embarcação') }}
                </h2>
            </div>
            <a href="{{ route('concessionarias.embarcacoes.index') }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3395da; border: 2px solid #3395da;">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
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
            
            <!-- Formulário Container -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-lg">
                <!-- Header -->
                <div class="px-8 py-6 border-b border-gray-100" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Cadastro de Nova Embarcação</h3>
                        <p class="text-gray-600">Preencha todas as informações da embarcação</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('concessionarias.embarcacoes.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Seção 1: Informações Básicas -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="mb-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                    <i class="fas fa-info-circle text-white text-sm"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Informações Básicas</h3>
                            </div>
                            <p class="text-gray-600 ml-13">Dados fundamentais da embarcação</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="space-y-1">
                                <x-input-label for="nome" :value="__('Nome *')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="nome" class="block w-full py-2 px-3 rounded-lg text-sm" type="text" name="nome" :value="old('nome')" 
                                              placeholder="Ex: Kizomba" maxlength="255" required />
                                <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="imo" :value="__('IMO *')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="imo" class="block w-full py-2 px-3 rounded-lg text-sm" type="text" name="imo" :value="old('imo')" 
                                              placeholder="Ex: 1234567" maxlength="7" required />
                                <x-input-error :messages="$errors->get('imo')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="mmsi" :value="__('MMSI')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="mmsi" class="block w-full py-2 px-3 rounded-lg text-sm" type="text" name="mmsi" :value="old('mmsi')" 
                                              placeholder="Ex: 123456789" maxlength="9" />
                                <x-input-error :messages="$errors->get('mmsi')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="bandeira" :value="__('Bandeira *')" class="text-xs font-medium text-gray-700" />
                                <select id="bandeira" name="bandeira" 
                                        class="block w-full py-2 px-3 rounded-lg text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Selecione a bandeira</option>
                                    @foreach(\App\Helpers\CountryHelper::getCountriesForSelect() as $code => $country)
                                        <option value="{{ $country }}" {{ old('bandeira') == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bandeira')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="tipo_embarcacao" :value="__('Tipo de Embarcação *')" class="text-xs font-medium text-gray-700" />
                                <select id="tipo_embarcacao" name="tipo_embarcacao" class="block w-full py-2 px-3 rounded-lg text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>

                                    <option value="">Selecione o tipo</option>
                                            <option value="Cargueiro" {{ old('tipo') == 'Cargueiro' ? 'selected' : '' }}>🚢 Cargueiro</option>
                                            <option value="Tanque" {{ old('tipo') == 'Tanque' ? 'selected' : '' }}>🛢️ Tanque</option>
                                            <option value="Passageiros" {{ old('tipo') == 'Passageiros' ? 'selected' : '' }}>🛳️ Passageiros</option>
                                            <option value="Pesqueiro" {{ old('tipo') == 'Pesqueiro' ? 'selected' : '' }}>🎣 Pesqueiro</option>
                                            <option value="Rebocador" {{ old('tipo') == 'Rebocador' ? 'selected' : '' }}>🚤 Rebocador</option>
                                            <option value="Frigorifico" {{ old('tipo') == 'Outro' ? 'selected' : '' }}>❄️ Frigorifico</option>
                                            <option value="Outro" {{ old('tipo') == 'Outro' ? 'selected' : '' }}>⚓ Outro</option>
                                             
                                </select>
                                <x-input-error :messages="$errors->get('tipo_embarcacao')" class="mt-1" />
                            </div>

                

                            <!--div class="space-y-1">
                                <x-input-label for="concessionaria_id" :value="__('Concessionária *')" class="text-xs font-medium text-gray-700" />
                                <select id="concessionaria_id" name="concessionaria_id" class="block w-full py-2 px-3 rounded-lg text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Selecione a concessionária</option>
                                    @foreach($concessionarias as $concessionaria)
                                        <option value="{{ $concessionaria->id }}" {{ old('concessionaria_id') == $concessionaria->id ? 'selected' : '' }}>
                                            {{ $concessionaria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('concessionaria_id')" class="mt-1" />
                            </div-->
                        </div>
                    </div>

                    <!-- Seção 2: Dimensões -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="mb-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fas fa-ruler text-white text-sm"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Dimensões</h3>
                            </div>
                            <p class="text-gray-600 ml-13">Medidas físicas da embarcação</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="space-y-1">
                                <x-input-label for="comprimento" :value="__('Comprimento (m) *')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="comprimento" name="comprimento" type="number" step="0.01" 
                                              class="block w-full py-2 px-3 rounded-lg text-sm" :value="old('comprimento')" 
                                              placeholder="0.00" required />
                                <x-input-error :messages="$errors->get('comprimento')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="largura" :value="__('Largura (m) *')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="largura" name="largura" type="number" step="0.01" 
                                              class="block w-full py-2 px-3 rounded-lg text-sm" :value="old('largura')" 
                                              placeholder="0.00" required />
                                <x-input-error :messages="$errors->get('largura')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="calado" :value="__('Calado (m) *')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="calado" name="calado" type="number" step="0.01" 
                                              class="block w-full py-2 px-3 rounded-lg text-sm" :value="old('calado')" 
                                              placeholder="0.00" required />
                                <x-input-error :messages="$errors->get('calado')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="arqueacao_bruta" :value="__('Arqueação Bruta (GT)')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="arqueacao_bruta" name="arqueacao_bruta" type="number" step="0.01" 
                                              class="block w-full py-2 px-3 rounded-lg text-sm" :value="old('arqueacao_bruta')" 
                                              placeholder="0.00" />
                                <x-input-error :messages="$errors->get('arqueacao_bruta')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="arqueacao_liquida" :value="__('Arqueação Líquida (NT)')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="arqueacao_liquida" name="arqueacao_liquida" type="number" step="0.01" 
                                              class="block w-full py-2 px-3 rounded-lg text-sm" :value="old('arqueacao_liquida')" 
                                              placeholder="0.00" />
                                <x-input-error :messages="$errors->get('arqueacao_liquida')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Seção 3: Informações Operacionais -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="mb-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                                    <i class="fas fa-cogs text-white text-sm"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Informações Operacionais</h3>
                            </div>
                            <p class="text-gray-600 ml-13">Dados operacionais e de responsabilidade</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <x-input-label for="armador" :value="__('Armador')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="armador" class="block w-full py-2 px-3 rounded-lg text-sm" type="text" name="armador" :value="old('armador')" 
                                              placeholder="Nome do armador" maxlength="255" />
                                <x-input-error :messages="$errors->get('armador')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="agente_maritimo" :value="__('Agente Marítimo')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="agente_maritimo" class="block w-full py-2 px-3 rounded-lg text-sm" type="text" name="agente_maritimo" :value="old('agente_maritimo')" 
                                              placeholder="Nome do agente marítimo" maxlength="255" />
                                <x-input-error :messages="$errors->get('agente_maritimo')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="capitao" :value="__('Capitão')" class="text-xs font-medium text-gray-700" />
                                <x-text-input id="capitao" class="block w-full py-2 px-3 rounded-lg text-sm" type="text" name="capitao" :value="old('capitao')" 
                                              placeholder="Nome do capitão" maxlength="255" />
                                <x-input-error :messages="$errors->get('capitao')" class="mt-1" />
                            </div>

                            <div class="space-y-1">
                                <x-input-label for="status" :value="__('Status *')" class="text-xs font-medium text-gray-700" />
                                <select id="status" name="status" class="block w-full py-2 px-3 rounded-lg text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Selecione o status</option>
                                    <option value="chegando" {{ old('status') == 'chegando' ? 'selected' : '' }}>⏳ Chegando</option>
                                    <option value="atracado" {{ old('status') == 'atracado' ? 'selected' : '' }}>⚓ Atracado</option>
                                    <option value="operando" {{ old('status') == 'operando' ? 'selected' : '' }}>⚡ Operando</option>
                                    <option value="partido" {{ old('status') == 'partido' ? 'selected' : '' }}>🚢 Partido</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-1" />
                            </div>

                            <div class="md:col-span-2 space-y-1">
                                <x-input-label for="observacoes" :value="__('Observações')" class="text-xs font-medium text-gray-700" />
                                <textarea id="observacoes" name="observacoes" rows="4" 
                                          class="block w-full py-2 px-3 rounded-lg text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                          placeholder="Observações adicionais sobre a embarcação">{{ old('observacoes') }}</textarea>
                                <x-input-error :messages="$errors->get('observacoes')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-between px-8 py-6 bg-gray-50">
                        <a href="{{ route('concessionarias.embarcacoes.index') }}" class="text-gray-600 font-medium py-2 px-4 rounded-lg transition duration-200 hover:bg-gray-100 flex items-center">
                            <i class="fas fa-times mr-2 text-xs"></i>Cancelar
                        </a>
                        
                        <button type="submit" class="text-white font-medium py-2 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3395da;">
                            <i class="fas fa-save mr-2 text-xs"></i>Salvar Embarcação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Validação de IMO (algoritmo de verificação)
        document.getElementById('imo').addEventListener('blur', function() {
            const imo = this.value;
            if (imo && imo.length === 7) {
                const digits = imo.substring(0, 6);
                let sum = 0;
                for (let i = 0; i < 6; i++) {
                    sum += parseInt(digits[i]) * (7 - i);
                }
                const checkDigit = sum % 10;
                if (checkDigit != parseInt(imo[6])) {
                    this.setCustomValidity('Número IMO inválido');
                } else {
                    this.setCustomValidity('');
                }
            }
        });

        // Auto-formatação de campos numéricos
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                if (this.value < 0) {
                    this.value = 0;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>