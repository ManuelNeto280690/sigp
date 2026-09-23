<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-leaf text-white text-xs"></i>
                    </div>
                    {{ __('Nova Infração Ambiental') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Registre uma nova infração ambiental no sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('infracoes-ambientais.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensagens de Erro -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Há {{ $errors->count() }} erro(s) com os dados informados:
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Mensagens de Sucesso -->
            @if (session('success'))
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Formulário -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <form method="POST" action="{{ route('infracoes-ambientais.store') }}" enctype="multipart/form-data" class="p-6">
                    @csrf

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Básicas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Auto de Infração -->
                            <!--div>
                                <label for="auto_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Auto de Infração <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="auto_infracao" 
                                       id="auto_infracao"
                                       value="{{ old('auto_infracao') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('auto_infracao') border-red-500 @enderror"
                                       placeholder="Ex: AI-2024-001"
                                       required>
                                @error('auto_infracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div-->

                            <!-- Tipo -->
                            <div>
                                <label for="tipo_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Infração <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo_infracao" 
                                        id="tipo_infracao"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('tipo_infracao') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="descarga_irregular" {{ old('tipo_infracao') == 'descarga_irregular' ? 'selected' : '' }}>Descarga Irregular</option>
                                    <option value="poluicao_atmosferica" {{ old('tipo_infracao') == 'poluicao_atmosferica' ? 'selected' : '' }}>Poluição Atmosférica</option>
                                    <option value="residuos_solidos" {{ old('tipo_infracao') == 'residuos_solidos' ? 'selected' : '' }}>Resíduos Sólidos</option>
                                    <option value="ruido_excessivo" {{ old('tipo_infracao') == 'ruido_excessivo' ? 'selected' : '' }}>Ruído Excessivo</option>
                                    <option value="poluicao_agua" {{ old('tipo') == 'poluicao_agua' ? 'selected' : '' }}>Poluição da Água</option>
                                    <option value="vazamento" {{ old('tipo') == 'vazamento' ? 'selected' : '' }}>Vazamento</option>
                                    <option value="outros" {{ old('tipo') == 'outros' ? 'selected' : '' }}>Outros</option>
                                </select>
                                @error('tipo_infracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Gravidade -->
                            <div>
                                <label for="gravidade" class="block text-sm font-medium text-gray-700 mb-2">
                                    Gravidade <span class="text-red-500">*</span>
                                </label>
                                <select name="gravidade" 
                                        id="gravidade"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('gravidade') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione a gravidade</option>
                                    <option value="leve" {{ old('gravidade') == 'leve' ? 'selected' : '' }}>Leve</option>
                                    <option value="media" {{ old('gravidade', 'media') == 'media' ? 'selected' : '' }}>Média</option>
                                    <option value="grave" {{ old('gravidade') == 'grave' ? 'selected' : '' }}>Grave</option>
                                    <option value="gravissima" {{ old('gravidade') == 'gravissima' ? 'selected' : '' }}>Gravíssima</option>
                                </select>
                                @error('gravidade')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data da Infração -->
                            <div>
                                <label for="data_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data da Infração <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" 
                                       name="data_infracao" 
                                       id="data_infracao"
                                       value="{{ old('data_infracao') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_infracao') border-red-500 @enderror"
                                       max="{{ date('Y-m-d\TH:i') }}"
                                       required>
                                @error('data_infracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Vinculações -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-link text-blue-600 mr-2"></i>
                            Vinculações
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Embarcação -->
                            <div>
                                <label for="embarcacao_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Embarcação
                                </label>
                                <select name="embarcacao_id" 
                                        id="embarcacao_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('embarcacao_id') border-red-500 @enderror">
                                    <option value="">Selecione uma embarcação</option>
                                    @foreach($embarcacoes as $embarcacao)
                                        <option value="{{ $embarcacao->id }}" {{ old('embarcacao_id') == $embarcacao->id ? 'selected' : '' }}>
                                            {{ $embarcacao->nome }} - {{ $embarcacao->imo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('embarcacao_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Terminal -->
                            <div>
                                <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Terminal
                                </label>
                                <select name="terminal_id" 
                                        id="terminal_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('terminal_id') border-red-500 @enderror">
                                    <option value="">Selecione um terminal</option>
                                    @foreach($terminais as $terminal)
                                        <option value="{{ $terminal->id }}" {{ old('terminal_id') == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->nome }} - {{ $terminal->codigo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('terminal_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Descrição da Infração -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                            Descrição da Infração
                        </h4>
                        <div class="space-y-6">
                            <!-- Descrição -->
                            <div>
                                <label for="descricao_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrição <span class="text-red-500">*</span>
                                </label>
                                <textarea name="descricao_infracao" 
                                          id="descricao_infracao"
                                          rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('descricao_infracao') border-red-500 @enderror"
                                          placeholder="Descreva detalhadamente a infração ambiental..."
                                          required>{{ old('descricao_infracao') }}</textarea>
                                @error('descricao_infracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Local -->
                            <div>
                                <label for="local_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Local da Infração <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="local_infracao" 
                                       id="local_infracao"
                                       value="{{ old('local_infracao') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('local_infracao') border-red-500 @enderror"
                                       placeholder="Ex: Berço 3, Terminal de Contêineres"
                                       required>
                                @error('ocal_infracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Observações -->
                            <div>
                                <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Observações
                                </label>
                                <textarea name="observacoes" 
                                          id="observacoes"
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('observacoes') border-red-500 @enderror"
                                          placeholder="Informações adicionais sobre a infração...">{{ old('observacoes') }}</textarea>
                                @error('observacoes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Multa e Penalidades -->
                    <!--div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-gavel text-blue-600 mr-2"></i>
                            Multa e Penalidades
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!- Valor da Multa ->
                            <div>
                                <label for="valor_multa" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor da Multa (R$)
                                </label>
                                <input type="number" 
                                       name="valor_multa" 
                                       id="valor_multa"
                                       value="{{ old('valor_multa') }}"
                                       step="0.01"
                                       min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('valor_multa') border-red-500 @enderror"
                                       placeholder="0,00">
                                @error('valor_multa')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!- Prazo para Regularização ->
                            <div>
                                <label for="prazo_regularizacao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Prazo para Regularização
                                </label>
                                <input type="date" 
                                       name="prazo_regularizacao" 
                                       id="prazo_regularizacao"
                                       value="{{ old('prazo_regularizacao') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('prazo_regularizacao') border-red-500 @enderror"
                                       min="{{ date('Y-m-d') }}">
                                @error('prazo_regularizacao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div-->

                    <!-- Evidências -->
                    <!--div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-camera text-blue-600 mr-2"></i>
                            Evidências
                        </h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-4">
                                <i class="fas fa-info-circle mr-1"></i>
                                Adicione informações sobre evidências coletadas (formato JSON)
                            </p>
                            <input name="evidencias" 
                                      id="evidencias"
                                      type="file"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('evidencias') border-red-500 @enderror font-mono text-sm"
                                      >
                                      
                            @error('evidencias')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div-->

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('infracoes-ambientais.index') }}" 
                           class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>Registrar Infração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Validação de JSON para evidências
        document.getElementById('evidencias').addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && value !== '') {
                try {
                    JSON.parse(value);
                    this.classList.remove('border-red-500');
                    this.classList.add('border-blue-500');
                } catch (e) {
                    this.classList.remove('border-blue-500');
                    this.classList.add('border-red-500');
                }
            } else {
                this.classList.remove('border-red-500', 'border-blue-500');
            }
        });

        // Auto-sugestão de gravidade baseada no tipo
        document.getElementById('tipo').addEventListener('change', function() {
            const gravidadeSelect = document.getElementById('gravidade');
            const valorMultaInput = document.getElementById('valor_multa');
            
            switch(this.value) {
                case 'poluicao_agua':
                case 'vazamento':
                    gravidadeSelect.value = 'grave';
                    valorMultaInput.value = '50000.00';
                    break;
                case 'poluicao_ar':
                    gravidadeSelect.value = 'grave';
                    valorMultaInput.value = '30000.00';
                    break;
                case 'descarte_irregular':
                    gravidadeSelect.value = 'media';
                    valorMultaInput.value = '15000.00';
                    break;
                case 'ruido_excessivo':
                    gravidadeSelect.value = 'leve';
                    valorMultaInput.value = '5000.00';
                    break;
                case 'fauna_flora':
                    gravidadeSelect.value = 'gravissima';
                    valorMultaInput.value = '100000.00';
                    break;
                case 'licenciamento':
                    gravidadeSelect.value = 'media';
                    valorMultaInput.value = '20000.00';
                    break;
            }
        });

        // Validação de data da infração
        document.getElementById('data_infracao').addEventListener('change', function() {
            const agora = new Date();
            const dataInfracao = new Date(this.value);
            
            if (dataInfracao > agora) {
                alert('A data da infração não pode ser futura.');
                this.value = '';
            }
        });

        // Auto-preenchimento do prazo de regularização
        document.getElementById('gravidade').addEventListener('change', function() {
            const prazoInput = document.getElementById('prazo_regularizacao');
            const hoje = new Date();
            let diasPrazo = 30; // padrão
            
            switch(this.value) {
                case 'leve':
                    diasPrazo = 60;
                    break;
                case 'media':
                    diasPrazo = 30;
                    break;
                case 'grave':
                    diasPrazo = 15;
                    break;
                case 'gravissima':
                    diasPrazo = 7;
                    break;
            }
            
            const prazoData = new Date(hoje.getTime() + (diasPrazo * 24 * 60 * 60 * 1000));
            prazoInput.value = prazoData.toISOString().split('T')[0];
        });

        // Formatação do valor da multa
        document.getElementById('valor_multa').addEventListener('input', function() {
            let value = this.value.replace(/[^\d.,]/g, '');
            this.value = value;
        });

        // Geração automática do auto de infração
        document.addEventListener('DOMContentLoaded', function() {
            const autoInput = document.getElementById('auto_infracao');
            if (!autoInput.value) {
                const ano = new Date().getFullYear();
                const numero = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                autoInput.value = `AI-${ano}-${numero}`;
            }
        });
    </script>
    @endpush
</x-app-layout>