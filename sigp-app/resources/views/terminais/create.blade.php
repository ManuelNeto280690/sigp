<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                    <i class="fas fa-plus text-white text-sm"></i>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Novo Terminal') }}
                </h2>
            </div>
            <a href="{{ route('terminais.index') }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3b82f6; border: 2px solid #3b82f6;">
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
            
            <!-- Formulário -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-lg">
                <!-- Cabeçalho -->
                <div class="px-8 py-6 border-b border-gray-100" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-warehouse text-blue-600 mr-2"></i>
                        Cadastro de Novo Terminal
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Preencha as informações do terminal portuário</p>
                </div>

                <form action="{{ route('terminais.store') }}" method="POST" class="p-8">
                    @csrf

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Básicas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Concessionária -->
                            <div>
                                <label for="concessionaria_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Concessionária <span class="text-red-500">*</span>
                                </label>
                                <select name="concessionaria_id" 
                                        id="concessionaria_id" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('concessionaria_id') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione a concessionária</option>
                                    @foreach($concessionarias ?? [] as $concessionaria)
                                        <option value="{{ $concessionaria->id }}" {{ old('concessionaria_id') == $concessionaria->id ? 'selected' : '' }}>
                                            {{ $concessionaria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('concessionaria_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nome -->
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nome do Terminal <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="nome" 
                                       id="nome"
                                       value="{{ old('nome') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('nome') border-red-500 @enderror"
                                       placeholder="Ex: Terminal de Contêineres Norte"
                                       required>
                                @error('nome')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Código -->
                            <div>
                                <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Código <span class="text-gray-500">(Gerado automaticamente)</span>
                                </label>
                                <input type="text" 
                                       name="codigo" 
                                       id="codigo"
                                       value="{{ $proximoCodigo ?? 'TM' . date('Y') . '0001' }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                       placeholder="Código será gerado automaticamente"
                                       maxlength="20"
                                       readonly>
                                <p class="mt-1 text-sm text-gray-500">O código será gerado automaticamente no formato TM{{ date('Y') }}XXXX</p>
                                @error('codigo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo" 
                                        id="tipo" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('tipo') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="carga geral" {{ old('tipo') == 'carga geral' ? 'selected' : '' }}>Carga Geral</option>
                     
                                </select>
                                @error('tipo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" 
                                        id="status" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('status') border-red-500 @enderror"
                                        required>
                                    <option value="ativo" {{ old('status', 'ativo') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                    <option value="inativo" {{ old('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                                    <option value="manutencao" {{ old('status') == 'manutencao' ? 'selected' : '' }}>Em Manutenção</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Ativo -->
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                                    Habilitado
                                </label>
                                <select name="is_active" 
                                        id="is_active" 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Especificações Técnicas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cogs text-blue-600 mr-2"></i>
                            Especificações Técnicas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Área Total -->
                            <div>
                                <label for="area_total" class="block text-sm font-medium text-gray-700 mb-2">
                                    Área Total (m²)
                                </label>
                                <input type="number" 
                                       name="area_total" 
                                       id="area_total"
                                       value="{{ old('area_total') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('area_total') border-red-500 @enderror"
                                       placeholder="Ex: 100000"
                                       step="0.01"
                                       min="0">
                                @error('area_total')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Área Operacional -->
                            <div>
                                <label for="area_operacional" class="block text-sm font-medium text-gray-700 mb-2">
                                    Área Operacional (m²)
                                </label>
                                <input type="number" 
                                       name="area_operacional" 
                                       id="area_operacional"
                                       value="{{ old('area_operacional') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('area_operacional') border-red-500 @enderror"
                                       placeholder="Ex: 80000"
                                       step="0.01"
                                       min="0">
                                @error('area_operacional')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Número de Berços -->
                            <div>
                                <label for="numero_bercos" class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de Berços <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="numero_bercos" 
                                       id="numero_bercos"
                                       value="{{ old('numero_bercos', 1) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('numero_bercos') border-red-500 @enderror"
                                       placeholder="Ex: 3"
                                       min="1"
                                       max="50"
                                       required>
                                @error('numero_bercos')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Calado Máximo -->
                            <div>
                                <label for="calado_maximo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Calado Máximo (m)
                                </label>
                                <input type="number" 
                                       name="calado_maximo" 
                                       id="calado_maximo"
                                       value="{{ old('calado_maximo') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('calado_maximo') border-red-500 @enderror"
                                       placeholder="Ex: 15.5"
                                       step="0.01"
                                       min="0">
                                @error('calado_maximo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Capacidade de Armazenagem -->
                            <div>
                                <label for="capacidade_armazenagem" class="block text-sm font-medium text-gray-700 mb-2">
                                    Capacidade de Armazenagem (t)
                                </label>
                                <input type="number" 
                                       name="capacidade_armazenagem" 
                                       id="capacidade_armazenagem"
                                       value="{{ old('capacidade_armazenagem') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('capacidade_armazenagem') border-red-500 @enderror"
                                       placeholder="Ex: 50000"
                                       min="0">
                                @error('capacidade_armazenagem')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-file-text text-blue-600 mr-2"></i>
                            Descrição
                        </h4>
                        <div>
                            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição do Terminal
                            </label>
                            <textarea name="descricao" 
                                      id="descricao"
                                      rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('descricao') border-red-500 @enderror"
                                      placeholder="Descreva as características e funcionalidades do terminal...">{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                            Observações
                        </h4>
                        <div>
                            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                Observações Adicionais
                            </label>
                            <textarea name="observacoes" 
                                      id="observacoes"
                                      rows="4"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('observacoes') border-red-500 @enderror"
                                      placeholder="Informações adicionais sobre o terminal...">{{ old('observacoes') }}</textarea>
                            @error('observacoes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('terminais.index') }}" 
                           class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-200 flex items-center">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Terminal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>