<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #ef4444 0%, #3b82f6 100%);">
                    <i class="fas fa-edit text-white text-sm"></i>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Editar Incidente') }}
                </h2>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('incidentes.show', $incidente) }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3b82f6; border: 2px solid #3b82f6;">
                    <i class="fas fa-eye mr-2"></i>Visualizar
                </a>
                <a href="{{ route('incidentes.index') }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #ef4444; border: 2px solid #ef4444;">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
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
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-blue-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-blue-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 mb-2">Foram encontrados os seguintes erros:</h3>
                            <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
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
                <div class="px-8 py-6 border-b border-gray-100" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-exclamation-triangle text-blue-600 mr-2"></i>
                        Editar Incidente: {{ $incidente->numero_incidente ?? 'INC-' . str_pad($incidente->id, 6, '0', STR_PAD_LEFT) }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Atualize as informações do incidente</p>
                </div>

                <form action="{{ route('incidentes.update', $incidente) }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Básicas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Incidente <span class="text-blue-500">*</span>
                                </label>
                                <select name="tipo" 
                                        id="tipo"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('tipo') border-blue-500 @enderror"
                                        requiblue>
                                    <option value="">Selecione o tipo</option>
                                    <option value="operacional" {{ old('tipo', $incidente->tipo) == 'operacional' ? 'selected' : '' }}>Operacional</option>
                                    <option value="seguranca" {{ old('tipo', $incidente->tipo) == 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="ambiental" {{ old('tipo', $incidente->tipo) == 'ambiental' ? 'selected' : '' }}>Ambiental</option>
                                    <option value="manutencao" {{ old('tipo', $incidente->tipo) == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                                    <option value="emergencia" {{ old('tipo', $incidente->tipo) == 'emergencia' ? 'selected' : '' }}>Emergência</option>
                                    <option value="acidente" {{ old('tipo', $incidente->tipo) == 'acidente' ? 'selected' : '' }}>Acidente</option>
                                    <option value="incendio" {{ old('tipo', $incidente->tipo) == 'incendio' ? 'selected' : '' }}>Incêndio</option>
                                    <option value="vazamento" {{ old('tipo', $incidente->tipo) == 'vazamento' ? 'selected' : '' }}>Vazamento</option>
                                </select>
                                @error('tipo')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Gravidade -->
                            <div>
                                <label for="gravidade" class="block text-sm font-medium text-gray-700 mb-2">
                                    Gravidade <span class="text-blue-500">*</span>
                                </label>
                                <select name="gravidade" 
                                        id="gravidade"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('gravidade') border-blue-500 @enderror"
                                        requiblue>
                                    <option value="">Selecione a gravidade</option>
                                    <option value="baixa" {{ old('gravidade', $incidente->gravidade) == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                    <option value="media" {{ old('gravidade', $incidente->gravidade) == 'media' ? 'selected' : '' }}>Média</option>
                                    <option value="alta" {{ old('gravidade', $incidente->gravidade) == 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="critica" {{ old('gravidade', $incidente->gravidade) == 'critica' ? 'selected' : '' }}>Crítica</option>
                                </select>
                                @error('gravidade')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status <span class="text-blue-500">*</span>
                                </label>
                                <select name="status" 
                                        id="status"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('status') border-blue-500 @enderror"
                                        requiblue>
                                    <option value="">Selecione o status</option>
                                    <option value="aberto" {{ old('status', $incidente->status) == 'aberto' ? 'selected' : '' }}>Aberto</option>
                                    <option value="em_investigacao" {{ old('status', $incidente->status) == 'em_investigacao' ? 'selected' : '' }}>Em Investigação</option>
                                    <option value="resolvido" {{ old('status', $incidente->status) == 'resolvido' ? 'selected' : '' }}>Resolvido</option>
                                    <option value="fechado" {{ old('status', $incidente->status) == 'fechado' ? 'selected' : '' }}>Fechado</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data de Ocorrência -->
                            <div>
                                <label for="data_ocorrencia" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de Ocorrência <span class="text-blue-500">*</span>
                                </label>
                                <input type="datetime-local" 
                                       name="data_ocorrencia" 
                                       id="data_ocorrencia"
                                       value="{{ old('data_ocorrencia', $incidente->data_ocorrencia ? $incidente->data_ocorrencia->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_ocorrencia') border-blue-500 @enderror"
                                      
                                       requiblue>
                                @error('data_ocorrencia')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data de Resolução -->
                            <div>
                                <label for="data_resolucao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de Resolução
                                </label>
                                <input type="datetime-local" 
                                       name="data_resolucao" 
                                       id="data_resolucao"
                                       value="{{ old('data_resolucao', $incidente->data_resolucao ? $incidente->data_resolucao->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_resolucao') border-blue-500 @enderror">
                                @error('data_resolucao')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
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
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('embarcacao_id') border-blue-500 @enderror">
                                    <option value="">Selecione uma embarcação</option>
                                    @foreach($embarcacoes as $embarcacao)
                                        <option value="{{ $embarcacao->id }}" {{ old('embarcacao_id', $incidente->embarcacao_id) == $embarcacao->id ? 'selected' : '' }}>
                                            {{ $embarcacao->nome }} - {{ $embarcacao->imo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('embarcacao_id')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Terminal -->
                            <div>
                                <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Terminal
                                </label>
                                <select name="terminal_id" 
                                        id="terminal_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('terminal_id') border-blue-500 @enderror">
                                    <option value="">Selecione um terminal</option>
                                    @foreach($terminais as $terminal)
                                        <option value="{{ $terminal->id }}" {{ old('terminal_id', $incidente->terminal_id) == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->nome }} - {{ $terminal->codigo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('terminal_id')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Descrição do Incidente -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                            Descrição do Incidente
                        </h4>
                        <div class="space-y-6">
                            <!-- Título -->
                            <div>
                                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Título <span class="text-blue-500">*</span>
                                </label>
                                <input type="text" 
                                       name="titulo" 
                                       id="titulo"
                                       value="{{ old('titulo', $incidente->titulo) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('titulo') border-blue-500 @enderror"
                                       placeholder="Título resumido do incidente..."
                                       requiblue>
                                @error('titulo')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                             <div>
                                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Local da Ocorrência <span class="text-blue-500">*</span>
                                </label>
                                <input type="text" 
                                       name="local_ocorrencia" 
                                       id="local_ocorrencia"
                                       value="{{ old('titulo', $incidente->local_ocorrencia) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('local_ocorrencia') border-blue-500 @enderror"
                                       placeholder="local da ocorrência..."
                                       requiblue>
                                @error('titulo')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descrição -->
                            <div>
                                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrição <span class="text-blue-500">*</span>
                                </label>
                                <textarea name="descricao" 
                                          id="descricao"
                                          rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('descricao') border-blue-500 @enderror"
                                          placeholder="Descreva detalhadamente o incidente ocorrido..."
                                          requiblue>{{ old('descricao', $incidente->descricao) }}</textarea>
                                @error('descricao')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Causa Provável -->
                            <div>
                                <label for="causa_provavel" class="block text-sm font-medium text-gray-700 mb-2">
                                    Causa Provável
                                </label>
                                <textarea name="causa_provavel" 
                                          id="causa_provavel"
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('causa_provavel') border-blue-500 @enderror"
                                          placeholder="Descreva a possível causa do incidente...">{{ old('causa_provavel', $incidente->causa_provavel) }}</textarea>
                                @error('causa_provavel')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Ações Imediatas -->
                            <div>
                                <label for="acoes_imediatas" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ações Imediatas Tomadas
                                </label>
                                <textarea name="acoes_imediatas" 
                                          id="acoes_imediatas"
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('acoes_imediatas') border-blue-500 @enderror"
                                          placeholder="Descreva as ações tomadas imediatamente após o incidente...">{{ old('acoes_imediatas', $incidente->acoes_imediatas) }}</textarea>
                                @error('acoes_imediatas')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Análise e Investigação -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-search text-blue-600 mr-2"></i>
                            Análise e Investigação
                        </h4>
                        <div class="space-y-6">
                            <!-- Análise Detalhada -->
                            <div>
                                <label for="analise_detalhada" class="block text-sm font-medium text-gray-700 mb-2">
                                    Análise Detalhada
                                </label>
                                <textarea name="analise_detalhada" 
                                          id="analise_detalhada"
                                          rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('analise_detalhada') border-blue-500 @enderror"
                                          placeholder="Análise técnica detalhada do incidente...">{{ old('analise_detalhada', $incidente->analise_detalhada) }}</textarea>
                                @error('analise_detalhada')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Ações Corretivas -->
                            <div>
                                <label for="acoes_corretivas" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ações Corretivas
                                </label>
                                <textarea name="acoes_corretivas" 
                                          id="acoes_corretivas"
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('acoes_corretivas') border-blue-500 @enderror"
                                          placeholder="Ações corretivas implementadas ou planejadas...">{{ old('acoes_corretivas', $incidente->acoes_corretivas) }}</textarea>
                                @error('acoes_corretivas')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
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
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('observacoes') border-blue-500 @enderror"
                                          placeholder="Observações adicionais...">{{ old('observacoes', $incidente->observacoes) }}</textarea>
                                @error('observacoes')
                                    <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes Técnicos -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cogs text-blue-600 mr-2"></i>
                            Detalhes Técnicos
                        </h4>
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-4">
                                <i class="fas fa-info-circle mr-1"></i>
                                Informações técnicas específicas relacionadas ao incidente (formato JSON)
                            </p>
                            <textarea name="detalhes_incidente" 
                                      id="detalhes_incidente"
                                      rows="6"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('detalhes_incidente') border-blue-500 @enderror font-mono text-sm"
                                      placeholder='{"equipamentos_envolvidos": [], "danos_materiais": [], "pessoas_envolvidas": [], "condicoes_climaticas": ""}'
                                      >{{ old('detalhes_incidente', is_array($incidente->detalhes_incidente) ? json_encode($incidente->detalhes_incidente, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $incidente->detalhes_incidente) }}</textarea>
                            @error('detalhes_incidente')
                                <p class="mt-1 text-sm text-blue-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('incidentes.show', $incidente) }}" 
                           class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>Atualizar Incidente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Validação de JSON para detalhes_incidente
        document.getElementById('detalhes_incidente').addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && value !== '') {
                try {
                    JSON.parse(value);
                    this.classList.remove('border-blue-500');
                    this.classList.add('border-green-500');
                } catch (e) {
                    this.classList.remove('border-green-500');
                    this.classList.add('border-blue-500');
                }
            } else {
                this.classList.remove('border-blue-500', 'border-green-500');
            }
        });

        // Atualização automática da gravidade baseada no tipo
        document.getElementById('tipo').addEventListener('change', function() {
            const gravidadeSelect = document.getElementById('gravidade');
            if (this.value === 'emergencia' || this.value === 'acidente' || this.value === 'incendio') {
                gravidadeSelect.value = 'critica';
            } else if (this.value === 'seguranca' || this.value === 'vazamento') {
                gravidadeSelect.value = 'alta';
            } else if (this.value === 'ambiental') {
                gravidadeSelect.value = 'media';
            }
        });

        // Validação de data máxima (não pode ser futura)
        document.getElementById('data_ocorrencia').addEventListener('change', function() {
            const agora = new Date().toISOString().slice(0, 16);
            if (this.value > agora) {
                this.value = agora;
                alert('A data de ocorrência não pode ser futura.');
            }
        });

        // Validação de data de resolução
        document.getElementById('data_resolucao').addEventListener('change', function() {
            const dataOcorrencia = document.getElementById('data_ocorrencia').value;
            if (dataOcorrencia && this.value && this.value < dataOcorrencia) {
                this.value = '';
                alert('A data de resolução não pode ser anterior à data de ocorrência.');
            }
        });

        // Auto-preenchimento da data de resolução quando status for resolvido ou fechado
        document.getElementById('status').addEventListener('change', function() {
            const dataResolucaoInput = document.getElementById('data_resolucao');
            if ((this.value === 'resolvido' || this.value === 'fechado') && !dataResolucaoInput.value) {
                dataResolucaoInput.value = new Date().toISOString().slice(0, 16);
            } else if (this.value === 'aberto' || this.value === 'em_investigacao') {
                dataResolucaoInput.value = '';
            }
        });

        // Bloqueio de campos para incidentes fechados
        @if($incidente->status === 'fechado')
        const campos = ['tipo', 'data_ocorrencia', 'embarcacao_id', 'terminal_id', 'titulo', 'descricao'];
        campos.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento) {
                elemento.disabled = true;
                elemento.classList.add('bg-gray-100');
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>