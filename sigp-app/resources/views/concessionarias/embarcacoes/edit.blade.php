<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-ship mr-2 text-blue-600"></i>
                    {{ __('Editar Embarcação') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $embarcacao->nome }} - IMO: {{ $embarcacao->imo }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('concessionarias.embarcacoes.show', $embarcacao) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-eye mr-2"></i>Visualizar
                </a>
                <a href="{{ route('concessionarias.embarcacoes.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Progress Indicator -->
            <div class="mb-6">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center text-blue-600">
                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold mr-3">
                            <i class="fas fa-edit"></i>
                        </div>
                        <span class="font-medium">Editando Embarcação</span>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-6 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-400 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-green-500"></i>
                        <div>
                            <strong class="font-semibold">Sucesso!</strong>
                            <p class="mt-1">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-400 text-red-700 p-4 rounded-lg shadow-sm" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
                        <div>
                            <strong class="font-semibold">Erro!</strong>
                            <p class="mt-1">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-lg shadow-sm" role="alert">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle mr-3 text-yellow-500 mt-1"></i>
                        <div>
                            <strong class="font-semibold">Atenção!</strong>
                            <p class="mt-1">Corrija os erros abaixo:</p>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Form Card -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <form method="POST" action="{{ route('concessionarias.embarcacoes.update', $embarcacao) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information Section -->
                    <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-info-circle text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Informações Básicas</h3>
                                <p class="text-sm text-gray-600">Dados fundamentais da embarcação</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="nome" :value="__('Nome da Embarcação')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="nome" name="nome" type="text" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150" 
                                                  :value="old('nome', $embarcacao->nome)" required autofocus 
                                                  placeholder="Ex: MSC Splendida" />
                                    <i class="fas fa-ship absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="imo" :value="__('Número IMO')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="imo" name="imo" type="text" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150" 
                                                  :value="old('imo', $embarcacao->imo)" required
                                                  placeholder="Ex: 9123456" 
                                                  pattern="[0-9]{7}" 
                                                  title="O IMO deve conter exatamente 7 dígitos" />
                                    <i class="fas fa-hashtag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    7 dígitos numéricos
                                </p>
                                <x-input-error :messages="$errors->get('imo')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="mmsi" :value="__('Número MMSI')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="mmsi" name="mmsi" type="text" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150" 
                                                  :value="old('mmsi', $embarcacao->mmsi)" 
                                                  placeholder="Ex: 123456789" 
                                                  pattern="[0-9]{9}" 
                                                  title="O MMSI deve conter 9 dígitos" />
                                    <i class="fas fa-satellite-dish absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    9 dígitos numéricos
                                </p>
                                <x-input-error :messages="$errors->get('mmsi')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="bandeira" :value="__('Bandeira')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <select id="bandeira" name="bandeira" 
                                            class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150" required>
                                        <option value="">Selecione a bandeira</option>
                                        @foreach(\App\Helpers\CountryHelper::getCountriesForSelect() as $code => $country)
                                            <option value="{{ $country }}" {{ old('bandeira', $embarcacao->bandeira) == $country ? 'selected' : '' }}>
                                                {{ $country }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-flag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('bandeira')" class="mt-1" />
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <x-input-label for="tipo_embarcacao" :value="__('Tipo de Embarcação')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <select id="tipo_embarcacao" name="tipo_embarcacao" 
                                            class="mt-1 block w-full pl-10 pr-10 py-3 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150 appearance-none bg-white" 
                                            required>
                                        <option value="">Selecione o tipo</option>
                                        <option value="Cargueiro" {{ old('tipo_embarcacao', $embarcacao->tipo_embarcacao) == 'Cargueiro' ? 'selected' : '' }}>🚢 Cargueiro</option>
                                        <option value="Tanque" {{ old('tipo_embarcacao', $embarcacao->tipo_embarcacao) == 'Tanque' ? 'selected' : '' }}>⛽ Tanque</option>
                                        <option value="Passageiros" {{ old('tipo_embarcacao', $embarcacao->tipo_embarcacao) == 'Passageiros' ? 'selected' : '' }}>🛳️ Passageiros</option>
                                        <option value="Pesqueiro" {{ old('tipo_embarcacao', $embarcacao->tipo_embarcacao) == 'Pesqueiro' ? 'selected' : '' }}>🚢 Pesqueiro</option>
                                        <option value="passageiros" {{ old('tipo_embarcacao', $embarcacao->tipo_embarcacao) == 'passageiros' ? 'selected' : '' }}>🛳️ Passageiros</option>
                                        <option value="Rebocador" {{ old('tipo_embarcacao', $embarcacao->tipo_embarcacao) == 'Rebocador' ? 'selected' : '' }}>⛽ Rebocador</option>
                                        <option value="Frigorifico" {{ old('tipo_embarcacao', $embarcacao->tipo_embarcacao) == 'Frigorifico' ? 'selected' : '' }}>❄️ Frigorifico</option>
                                        <option value="Outro" {{ old('tipo_embarcacao', $embarcacao->tipo_embarcacao) == 'Outro' ? 'selected' : '' }}>🚛 Outro</option>
                                        
                                    </select>
                                    <i class="fas fa-ship absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('tipo_embarcacao')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Dimensions Section -->
                    <div class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-ruler text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Dimensões</h3>
                                <p class="text-sm text-gray-600">Medidas físicas da embarcação</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="comprimento" :value="__('Comprimento (m)')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="comprimento" name="comprimento" type="number" step="0.01" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition duration-150" 
                                                  :value="old('comprimento', $embarcacao->comprimento)" 
                                                  placeholder="Ex: 300.50" />
                                    <i class="fas fa-arrows-alt-h absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('comprimento')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="largura" :value="__('Largura (m)')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="largura" name="largura" type="number" step="0.01" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition duration-150" 
                                                  :value="old('largura', $embarcacao->largura)" 
                                                  placeholder="Ex: 45.20" />
                                    <i class="fas fa-arrows-alt-v absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('largura')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="calado" :value="__('Calado (m)')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="calado" name="calado" type="number" step="0.01" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition duration-150" 
                                                  :value="old('calado', $embarcacao->calado)" 
                                                  placeholder="Ex: 15.80" />
                                    <i class="fas fa-water absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('calado')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="arqueacao_bruta" :value="__('Arqueação Bruta (GT)')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="arqueacao_bruta" name="arqueacao_bruta" type="number" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition duration-150" 
                                                  :value="old('arqueacao_bruta', $embarcacao->arqueacao_bruta)" 
                                                  required placeholder="Ex: 95000" />
                                    <i class="fas fa-weight-hanging absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('arqueacao_bruta')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="arqueacao_liquida" :value="__('Arqueação Líquida (NT)')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="arqueacao_liquida" name="arqueacao_liquida" type="number" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition duration-150" 
                                                  :value="old('arqueacao_liquida', $embarcacao->arqueacao_liquida)" 
                                                  required placeholder="Ex: 28500" />
                                    <i class="fas fa-balance-scale absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('arqueacao_liquida')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Operational Information Section -->
                    <div class="p-6 bg-gradient-to-r from-purple-50 to-pink-50">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-cogs text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Informações Operacionais</h3>
                                <p class="text-sm text-gray-600">Dados operacionais e de gestão</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="armador" :value="__('Armador')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="armador" name="armador" type="text" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition duration-150" 
                                                  :value="old('armador', $embarcacao->armador)" 
                                                  placeholder="Nome da empresa armadora" />
                                    <i class="fas fa-building absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('armador')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="agente_maritimo" :value="__('Agente Marítimo')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="agente_maritimo" name="agente_maritimo" type="text" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition duration-150" 
                                                  :value="old('agente_maritimo', $embarcacao->agente_maritimo)" 
                                                  placeholder="Nome do agente marítimo" />
                                    <i class="fas fa-user-tie absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('agente_maritimo')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="capitao" :value="__('Capitão')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <x-text-input id="capitao" name="capitao" type="text" 
                                                  class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition duration-150" 
                                                  :value="old('capitao', $embarcacao->capitao)" 
                                                  placeholder="Nome do capitão" />
                                    <i class="fas fa-user-crown absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('capitao')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="status" :value="__('Status')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <select id="status" name="status" 
                                            class="mt-1 block w-full pl-10 pr-10 py-3 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition duration-150 appearance-none bg-white" 
                                            required>
                                        <option value="">Selecione o status</option>
                                        <option value="chegando" {{ old('status', $embarcacao->status) == 'chegando' ? 'selected' : '' }}>⏳ Chegando</option>
                                        <option value="atracado" {{ old('status', $embarcacao->status) == 'atracado' ? 'selected' : '' }}>⚓ Atracado</option>
                                        <option value="operando" {{ old('status', $embarcacao->status) == 'operando' ? 'selected' : '' }}>⚡ Operando</option>
                                        <option value="partido" {{ old('status', $embarcacao->status) == 'partido' ? 'selected' : '' }}>🚢 Partido</option>
                                    </select>
                                    <i class="fas fa-ship absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('status')" class="mt-1" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="is_active" :value="__('Môdo')" class="text-sm font-medium text-gray-700" />
                                <div class="relative">
                                    <select id="is_active" name="is_active" 
                                            class="mt-1 block w-full pl-10 pr-10 py-3 border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition duration-150 appearance-none bg-white">
                                        <option value="1" {{ old('is_active', $embarcacao->is_active) == '1' ? 'selected' : '' }}>✅ Ativa</option>
                                        <option value="0" {{ old('is_active', $embarcacao->is_active) == '0' ? 'selected' : '' }}>❌ Inativa</option>
                                    </select>
                                    <i class="fas fa-toggle-on absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Observations Section -->
                    <div class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-yellow-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-sticky-note text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Observações</h3>
                                <p class="text-sm text-gray-600">Informações adicionais e notas importantes</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <x-input-label for="observacoes" :value="__('Observações Gerais')" class="text-sm font-medium text-gray-700" />
                            <div class="relative">
                                <textarea id="observacoes" name="observacoes" rows="4" 
                                          class="mt-1 block w-full pl-10 pr-4 py-3 border-gray-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition duration-150 resize-none" 
                                          placeholder="Informações adicionais sobre a embarcação, histórico de operações, características especiais...">{{ old('observacoes', $embarcacao->observacoes) }}</textarea>
                                <i class="fas fa-comment-alt absolute left-3 top-4 text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Campo opcional para informações complementares
                            </p>
                            <x-input-error :messages="$errors->get('observacoes')" class="mt-1" />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-2"></i>
                                Campos marcados com * são obrigatórios
                            </div>
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('concessionarias.embarcacoes.show', $embarcacao) }}" 
                                   class="inline-flex items-center px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-lg shadow-sm transition duration-200">
                                    <i class="fas fa-times mr-2"></i>Cancelar
                                </a>
                                <button type="submit" 
                                        class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg shadow-lg transition duration-200 focus:outline-none focus:ring-4 focus:ring-blue-300">
                                    <i class="fas fa-save mr-2"></i>Atualizar Embarcação
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Enhanced form interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to submit button
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
            });

            // IMO validation
            const imoInput = document.getElementById('imo');
            imoInput.addEventListener('input', function() {
                // Apenas permite números
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Limita a 7 dígitos
                if (this.value.length > 7) {
                    this.value = this.value.substring(0, 7);
                }
                
                // Feedback visual
                if (this.value.length === 7) {
                    this.classList.remove('border-red-300');
                    this.classList.add('border-green-300');
                } else if (this.value.length > 0) {
                    this.classList.remove('border-green-300');
                    this.classList.add('border-yellow-300');
                } else {
                    this.classList.remove('border-green-300', 'border-yellow-300', 'border-red-300');
                }
            });

            // Auto-format numeric inputs
            document.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value < 0) {
                        this.value = 0;
                    }
                });

                // Add visual feedback for valid inputs
                input.addEventListener('blur', function() {
                    if (this.value && this.checkValidity()) {
                        this.classList.add('border-green-300');
                        this.classList.remove('border-red-300');
                    }
                });
            });

            // Enhanced focus effects
            document.querySelectorAll('input, select, textarea').forEach(element => {
                element.addEventListener('focus', function() {
                    this.parentElement.classList.add('transform', 'scale-105');
                });
                
                element.addEventListener('blur', function() {
                    this.parentElement.classList.remove('transform', 'scale-105');
                });
            });

            // Smooth scroll to errors
            const firstError = document.querySelector('.text-red-600');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
    @endpush
</x-app-layout>