<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-plus text-white text-xs"></i>
                    </div>
                    {{ __('Nova Inspeção') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Crie uma nova inspeção naval no sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('inspecoes.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
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
                <form method="POST" action="{{ route('inspecoes.store') }}" class="p-6">
                    @csrf

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Básicas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tipo de Inspeção -->
                            <div>
                                <label for="tipo_inspecao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Inspeção <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo_inspecao" 
                                        id="tipo_inspecao"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('tipo_inspecao') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="seguranca" {{ old('tipo_inspecao') == 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="ambiental" {{ old('tipo_inspecao') == 'ambiental' ? 'selected' : '' }}>Ambiental</option>
                                    <option value="operacional" {{ old('tipo_inspecao') == 'operacional' ? 'selected' : '' }}>Operacional</option>
                                    <option value="sanitaria" {{ old('tipo_inspecao') == 'sanitaria' ? 'selected' : '' }}>Sanitária</option>
                                    <option value="estrutural" {{ old('tipo_inspecao') == 'estrutural' ? 'selected' : '' }}>Estrutural</option>
                                    <option value="documentacao" {{ old('tipo_inspecao') == 'documentacao' ? 'selected' : '' }}>Documentação</option>
                                    <option value="equipamentos" {{ old('tipo_inspecao') == 'equipamentos' ? 'selected' : '' }}>Equipamentos</option>
                                    <option value="emergencial" {{ old('tipo_inspecao') == 'emergencial' ? 'selected' : '' }}>Emergencial</option>
                                </select>
                                @error('tipo_inspecao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Local da Inspeção -->
                            <div>
                                <label for="local_inspecao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Local da Inspeção <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="local_inspecao" 
                                       id="local_inspecao"
                                       value="{{ old('local_inspecao') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('local_inspecao') border-red-500 @enderror"
                                       placeholder="Ex: Porto de Santos, Berço 15"
                                       required>
                                @error('local_inspecao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data Programada -->
                            <div>
                                <label for="data_programada" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data da Inspecção <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       name="data_inspecao" 
                                       id="data_inspecao"
                                       value="{{ old('data_inspecao') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_inspecao') border-red-500 @enderror"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('data_inspecao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Inspetor Responsável -->
                            <div>
                                <label for="inspetor_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Inspetor Responsável
                                </label>
                                <select name="inspetor_id" 
                                        id="inspetor_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('inspetor_id') border-red-500 @enderror">
                                    <option value="">Selecione um inspetor</option>
                                    @foreach($inspetores as $inspetor)
                                        <option value="{{ $inspetor->id }}" {{ old('inspetor_id') == $inspetor->id ? 'selected' : '' }}>
                                            {{ $inspetor->name }} - {{ $inspetor->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('inspetor_id')
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
                                    Embarcação <span class="text-red-500">*</span>
                                </label>
                                <select name="embarcacao_id" 
                                        id="embarcacao_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('embarcacao_id') border-red-500 @enderror"
                                        required>
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

                    <!-- Descrição da Inspeção -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                            Descrição da Inspeção
                        </h4>
                        <div class="space-y-6">
                            <!-- Objetivo -->
                            <div>
                                <label for="objetivo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Objetivo <span class="text-red-500">*</span>
                                </label>
                                <textarea name="objetivo" 
                                          id="objetivo"
                                          rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('objetivo') border-red-500 @enderror"
                                          placeholder="Descreva o objetivo da inspeção..."
                                          required>{{ old('objetivo') }}</textarea>
                                @error('objetivo')
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
                                          placeholder="Informações adicionais sobre a inspeção (opcional)...">{{ old('observacoes') }}</textarea>
                                @error('observacoes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes Técnicos -->
                    <!--div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cogs text-blue-600 mr-2"></i>
                            Detalhes Técnicos
                        </h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-4">
                                <i class="fas fa-info-circle mr-1"></i>
                                Adicione informações técnicas específicas da inspeção (formato JSON)
                            </p>
                            <textarea name="detalhes_inspecao" 
                                      id="detalhes_inspecao"
                                      rows="6"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('detalhes_inspecao') border-red-500 @enderror font-mono text-sm"
                                      placeholder='{"areas_inspecao": [], "equipamentos_verificar": [], "documentos_necessarios": [], "criterios_avaliacao": {}}'
                                      >{{ old('detalhes_inspecao') }}</textarea>
                            @error('detalhes_inspecao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div-->

                    <!-- Configurações -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cog text-blue-600 mr-2"></i>
                            Configurações
                        </h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="ativo" 
                                       id="ativo" 
                                       value="1" 
                                       {{ old('ativo', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="ativo" class="ml-2 block text-sm text-gray-700">
                                    Inspeção ativa
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Inspeções inativas não aparecerão nas listagens principais
                            </p>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('inspecoes.index') }}" 
                           class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>Criar Inspeção
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Validação de JSON para detalhes_inspecao
        document.getElementById('detalhes_inspecao').addEventListener('blur', function() {
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

        // Auto-preenchimento do local baseado na embarcação e terminal
        function updateLocalInspecao() {
            const embarcacaoSelect = document.getElementById('embarcacao_id');
            const terminalSelect = document.getElementById('terminal_id');
            const localInput = document.getElementById('local_inspecao');
            
            const embarcacaoText = embarcacaoSelect.options[embarcacaoSelect.selectedIndex]?.text || '';
            const terminalText = terminalSelect.options[terminalSelect.selectedIndex]?.text || '';
            
            if (embarcacaoText && embarcacaoText !== 'Selecione uma embarcação') {
                let local = embarcacaoText.split(' - ')[0];
                if (terminalText && terminalText !== 'Selecione um terminal') {
                    local += ' - ' + terminalText.split(' - ')[0];
                }
                if (localInput.value === '' || localInput.dataset.autoFilled === 'true') {
                    localInput.value = local;
                    localInput.dataset.autoFilled = 'true';
                }
            }
        }

        document.getElementById('embarcacao_id').addEventListener('change', updateLocalInspecao);
        document.getElementById('terminal_id').addEventListener('change', updateLocalInspecao);

        // Limpar flag de auto-preenchimento quando usuário edita manualmente
        document.getElementById('local_inspecao').addEventListener('input', function() {
            this.dataset.autoFilled = 'false';
        });

        // Sugestão de objetivo baseado no tipo de inspeção
        document.getElementById('tipo_inspecao').addEventListener('change', function() {
            const objetivoTextarea = document.getElementById('objetivo');
            const objetivos = {
                'seguranca': 'Verificar conformidade com normas de segurança marítima, equipamentos de segurança e procedimentos de emergência.',
                'ambiental': 'Avaliar conformidade com regulamentações ambientais, prevenção de poluição e gestão de resíduos.',
                'operacional': 'Verificar procedimentos operacionais, eficiência dos processos e conformidade com protocolos.',
                'estrutural': 'Inspecionar integridade estrutural do navio, casco, convés e compartimentos.',
                'documentacao': 'Verificar documentação obrigatória, certificados, licenças e registros.',
                'equipamentos': 'Inspecionar funcionamento e manutenção de equipamentos críticos e sistemas.',
                'emergencial': 'Inspeção urgente devido a incidente, acidente ou situação de risco.'
            };
            
            if (this.value && objetivos[this.value] && objetivoTextarea.value === '') {
                objetivoTextarea.value = objetivos[this.value];
            }
        });

        // Validação de data mínima
        document.getElementById('data_programada').addEventListener('change', function() {
            const hoje = new Date().toISOString().split('T')[0];
            if (this.value < hoje) {
                this.value = hoje;
                alert('A data programada não pode ser anterior à data atual.');
            }
        });

        // Formatação automática do JSON
        document.getElementById('detalhes_inspecao').addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && value !== '') {
                try {
                    const parsed = JSON.parse(value);
                    this.value = JSON.stringify(parsed, null, 2);
                } catch (e) {
                    // Mantém o valor original se não for JSON válido
                }
            }
        });
    </script>
    @endpush
</x-app-layout>