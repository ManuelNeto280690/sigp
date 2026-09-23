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
            <a href="{{ route('embarcacoes.index') }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3395da; border: 2px solid #3395da;">
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
            
            <!-- Formulário Único -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-lg">
                <!-- Cabeçalho -->
                <div class="px-8 py-6 border-b border-gray-100" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Cadastro de Nova Embarcação</h3>
                        <div class="text-xs text-gray-600">
                            Preencha todas as informações da embarcação
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('embarcacoes.store') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    
                    <!-- Seção 1: Identificação da Embarcação -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                <i class="fas fa-ship text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Identificação da Embarcação</h3>
                                <p class="text-gray-600 text-sm">Dados básicos de identificação</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="nome" :value="__('Nome da Embarcação *')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="nome" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="text" name="nome" :value="old('nome')" 
                                              placeholder="Ex: MSC Splendida" maxlength="255" required />
                                <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="imo" :value="__('Número IMO *')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="imo" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="text" name="imo" :value="old('imo')" 
                                              placeholder="Ex: 9359787" maxlength="7" required />
                                <x-input-error :messages="$errors->get('imo')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="mmsi" :value="__('Número MMSI *')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="mmsi" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="text" name="mmsi" :value="old('mmsi')" 
                                              placeholder="Ex: 247234300" maxlength="9" required />
                                <x-input-error :messages="$errors->get('mmsi')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="bandeira" :value="__('Bandeira *')" class="text-sm font-semibold text-gray-700" />
                                <select id="bandeira" name="bandeira" 
                                        class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Selecione a bandeira</option>
                                    @foreach(\App\Helpers\CountryHelper::getCountriesForSelect() as $code => $country)
                                        <option value="{{ $country }}" {{ old('bandeira') == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bandeira')" class="mt-1" />
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <x-input-label for="tipo_embarcacao" :value="__('Tipo de Embarcação *')" class="text-sm font-semibold text-gray-700" />
                                <select id="tipo_embarcacao" name="tipo_embarcacao" 
                                        class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Selecione o tipo de embarcação</option>
                                    <option value="container" {{ old('tipo_embarcacao') == 'container' ? 'selected' : '' }}>📦 Porta-contêineres</option>
                                    <option value="graneis_solidos" {{ old('tipo_embarcacao') == 'graneis_solidos' ? 'selected' : '' }}>⚫ Granéis Sólidos</option>
                                    <option value="graneis_liquidos" {{ old('tipo_embarcacao') == 'graneis_liquidos' ? 'selected' : '' }}>🛢️ Granéis Líquidos</option>
                                    <option value="carga_geral" {{ old('tipo_embarcacao') == 'carga_geral' ? 'selected' : '' }}>📋 Carga Geral</option>
                                    <option value="passageiros" {{ old('tipo_embarcacao') == 'passageiros' ? 'selected' : '' }}>🛳️ Passageiros</option>
                                    <option value="tanque" {{ old('tipo_embarcacao') == 'tanque' ? 'selected' : '' }}>⛽ Navio Tanque</option>
                                    <option value="ro_ro" {{ old('tipo_embarcacao') == 'ro_ro' ? 'selected' : '' }}>🚛 Ro-Ro</option>
                                    <option value="frigorifico" {{ old('tipo_embarcacao') == 'frigorifico' ? 'selected' : '' }}>❄️ Frigorífico</option>
                                </select>
                                <x-input-error :messages="$errors->get('tipo_embarcacao')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Seção 2: Dimensões e Características Técnicas -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="fas fa-ruler text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Dimensões e Características Técnicas</h3>
                                <p class="text-gray-600 text-sm">Medidas e especificações técnicas da embarcação</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="comprimento" :value="__('Comprimento (metros)')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="comprimento" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="number" name="comprimento" :value="old('comprimento')" 
                                              step="0.01" min="0" placeholder="Ex: 333.30" />
                                <x-input-error :messages="$errors->get('comprimento')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="largura" :value="__('Largura (metros)')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="largura" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="number" name="largura" :value="old('largura')" 
                                              step="0.01" min="0" placeholder="Ex: 38.00" />
                                <x-input-error :messages="$errors->get('largura')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="calado" :value="__('Calado (metros)')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="calado" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="number" name="calado" :value="old('calado')" 
                                              step="0.01" min="0" placeholder="Ex: 8.55" />
                                <x-input-error :messages="$errors->get('calado')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="arqueacao_bruta" :value="__('Arqueação Bruta (GT)')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="arqueacao_bruta" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="number" name="arqueacao_bruta" :value="old('arqueacao_bruta')" 
                                              min="0" placeholder="Ex: 137936" />
                                <x-input-error :messages="$errors->get('arqueacao_bruta')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="arqueacao_liquida" :value="__('Arqueação Líquida (NT)')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="arqueacao_liquida" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="number" name="arqueacao_liquida" :value="old('arqueacao_liquida')" 
                                              min="0" placeholder="Ex: 41338" />
                                <x-input-error :messages="$errors->get('arqueacao_liquida')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Seção 3: Informações Operacionais -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <i class="fas fa-users text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Informações Operacionais</h3>
                                <p class="text-gray-600 text-sm">Dados sobre a operação e responsáveis</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="armador" :value="__('Armador')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="armador" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="text" name="armador" :value="old('armador')" 
                                              placeholder="Ex: MSC Cruises" />
                                <x-input-error :messages="$errors->get('armador')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="agente_maritimo" :value="__('Agente Marítimo')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="agente_maritimo" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="text" name="agente_maritimo" :value="old('agente_maritimo')" 
                                              placeholder="Ex: Wilson Sons" />
                                <x-input-error :messages="$errors->get('agente_maritimo')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="capitao" :value="__('Capitão')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="capitao" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="text" name="capitao" :value="old('capitao')" 
                                              placeholder="Ex: Giuseppe Marotta" />
                                <x-input-error :messages="$errors->get('capitao')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="status" :value="__('Status da Embarcação')" class="text-sm font-semibold text-gray-700" />
                                <select id="status" name="status" 
                                        class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="chegando" {{ old('status') == 'chegando' ? 'selected' : '' }}>⏳ Chegando</option>
                                    <option value="atracado" {{ old('status') == 'atracado' ? 'selected' : '' }}>⚓ Atracado</option>
                                    <option value="operando" {{ old('status') == 'operando' ? 'selected' : '' }}>⚡ Operando</option>
                                    <option value="partido" {{ old('status') == 'partido' ? 'selected' : '' }}>🚢 Partido</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Seção 4: Informações de Movimentação -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                                <i class="fas fa-route text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Informações de Movimentação</h3>
                                <p class="text-gray-600 text-sm">Dados sobre origem, destino e horários</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="porto_origem" :value="__('Porto de Origem')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="porto_origem" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="text" name="porto_origem" :value="old('porto_origem')" 
                                              placeholder="Ex: Buenos Aires" />
                                <x-input-error :messages="$errors->get('porto_origem')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="porto_destino" :value="__('Porto de Destino')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="porto_destino" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="text" name="porto_destino" :value="old('porto_destino')" 
                                              placeholder="Ex: Santos" />
                                <x-input-error :messages="$errors->get('porto_destino')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="eta" :value="__('ETA - Chegada Estimada')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="eta" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="datetime-local" name="eta" :value="old('eta')" />
                                <x-input-error :messages="$errors->get('eta')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="etd" :value="__('ETD - Partida Estimada')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="etd" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="datetime-local" name="etd" :value="old('etd')" />
                                <x-input-error :messages="$errors->get('etd')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="ata" :value="__('ATA - Chegada Real')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="ata" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="datetime-local" name="ata" :value="old('ata')" />
                                <x-input-error :messages="$errors->get('ata')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="atd" :value="__('ATD - Partida Real')" class="text-sm font-semibold text-gray-700" />
                                <x-text-input id="atd" class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                              type="datetime-local" name="atd" :value="old('atd')" />
                                <x-input-error :messages="$errors->get('atd')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Seção 5: Observações -->
                    <div class="p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                                <i class="fas fa-sticky-note text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Observações Adicionais</h3>
                                <p class="text-gray-600 text-sm">Informações complementares sobre a embarcação</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <x-input-label for="observacoes" :value="__('Observações')" class="text-sm font-semibold text-gray-700" />
                            <textarea id="observacoes" name="observacoes" rows="4" 
                                      class="block w-full py-3 px-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                      placeholder="Observações adicionais sobre a embarcação...">{{ old('observacoes') }}</textarea>
                            <x-input-error :messages="$errors->get('observacoes')" class="mt-1" />
                        </div>

                        <div class="mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Embarcação ativa</span>
                            </label>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-between px-8 py-6 border-t border-gray-100 bg-gray-50">
                        <a href="{{ route('embarcacoes.index') }}" 
                           class="text-gray-700 bg-white border border-gray-300 font-medium py-3 px-6 rounded-lg transition duration-200 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 flex items-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        
                        <button type="submit" 
                                class="text-white font-medium py-3 px-8 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" 
                                style="background: linear-gradient(135deg, #3395da 0%, #0086e1 100%);">
                            <i class="fas fa-save mr-2"></i>Salvar Embarcação
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
                    this.classList.add('border-red-500');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('border-red-500');
                }
            }
        });

        // Validação de MMSI (9 dígitos)
        document.getElementById('mmsi').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 9);
        });

        // Validação de campos numéricos
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function() {
                if (this.value < 0) {
                    this.value = 0;
                }
            });
        });

        // Validação de datas
        document.getElementById('etd').addEventListener('change', function() {
            const eta = document.getElementById('eta').value;
            if (eta && this.value && new Date(this.value) < new Date(eta)) {
                alert('A data de partida estimada deve ser posterior à chegada estimada.');
                this.value = '';
            }
        });

        document.getElementById('atd').addEventListener('change', function() {
            const ata = document.getElementById('ata').value;
            if (ata && this.value && new Date(this.value) < new Date(ata)) {
                alert('A data de partida real deve ser posterior à chegada real.');
                this.value = '';
            }
        });

        // Smooth scroll para seções com erro
        @if($errors->any())
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        @endif
    </script>
    @endpush
</x-app-layout>