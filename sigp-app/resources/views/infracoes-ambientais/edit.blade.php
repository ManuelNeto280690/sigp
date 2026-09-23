<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-edit text-white text-sm"></i>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Editar Infração Ambiental') }}
                </h2>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('infracoes-ambientais.show', $infracao) }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3b82f6; border: 2px solid #3b82f6;">
                    <i class="fas fa-eye mr-2"></i>Visualizar
                </a>
                <a href="{{ route('infracoes-ambientais.index') }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #10b981; border: 2px solid #10b981;">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Seção de Mensagens de Erro e Sucesso -->
            @if(session('success'))
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-blue-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">{{ session('success') }}</p>
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
                <div class="px-8 py-6 border-b border-gray-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-leaf text-blue-600 mr-2"></i>
                        Editar Infração: {{ $infracao->auto_infracao }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Atualize as informações da infração ambiental</p>
                </div>

                <form action="{{ route('infracoes-ambientais.update', $infracao) }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Básicas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Auto de Infração -->
                            <div>
                                <label for="auto_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Auto de Infração <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="auto_infracao" 
                                       id="auto_infracao"
                                       value="{{ old('auto_infracao', $infracao->numero_auto) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error(numero_auto') border-red-500 @enderror"
                                       placeholder="Ex: AI-2024-001"
                                       required>
                                @error('numero_auto')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                           <!-- Tipo de Infração -->
                            <div>
                                <label for="tipo_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Infração <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo_infracao" 
                                        id="tipo_infracao"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('tipo_infracao') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="descarga_irregular" {{ old('tipo_infracao', $infracao->tipo_infracao) == 'descarga_irregular' ? 'selected' : '' }}>Descarga Irregular</option>
                                    <option value="poluicao_atmosferica" {{ old('tipo_infracao', $infracao->tipo_infracao) == 'poluicao_atmosferica' ? 'selected' : '' }}>Poluição Atmosférica</option>
                                    <option value="residuos_solidos" {{ old('tipo_infracao', $infracao->tipo_infracao) == 'residuos_solidos' ? 'selected' : '' }}>Resíduos Sólidos</option>
                                    <option value="ruido_excessivo" {{ old('tipo_infracao', $infracao->tipo_infracao) == 'ruido_excessivo' ? 'selected' : '' }}>Ruído Excessivo</option>
                                    <option value="poluicao_agua" {{ old('tipo_infracao', $infracao->tipo_infracao) == 'poluicao_agua' ? 'selected' : '' }}>Poluição da Água</option>
                                     <option value="vazamento" {{ old('tipo_infracao', $infracao->tipo_infracao) == 'vazamento' ? 'selected' : '' }}>Vazamento</option>
                                    <option value="outros" {{ old('tipo_infracao', $infracao->tipo_infracao) == 'outros' ? 'selected' : '' }}>Outros</option>
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
                                    <option value="leve" {{ old('gravidade', $infracao->gravidade) == 'leve' ? 'selected' : '' }}>Leve</option>
                                    <option value="media" {{ old('gravidade', $infracao->gravidade) == 'media' ? 'selected' : '' }}>Média</option>
                                    <option value="grave" {{ old('gravidade', $infracao->gravidade) == 'grave' ? 'selected' : '' }}>Grave</option>
                                    <option value="gravissima" {{ old('gravidade', $infracao->gravidade) == 'gravissima' ? 'selected' : '' }}>Gravíssima</option>
                                </select>
                                @error('gravidade')
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
                                    <option value="">Selecione o status</option>
                                    <option value="registrada" {{ old('status', $infracao->status) == 'registrada' ? 'selected' : '' }}>Registrada</option>
                                    <option value="notificada" {{ old('status', $infracao->status) == 'notificada' ? 'selected' : '' }}>Notificada</option>
                                    <option value="em_regularizacao" {{ old('status', $infracao->status) == 'em_regularizacao' ? 'selected' : '' }}>Em Regularização</option>
                                    <option value="regularizada" {{ old('status', $infracao->status) == 'regularizada' ? 'selected' : '' }}>Regularizada</option>
                                    <option value="cancelada" {{ old('status', $infracao->status) == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                                @error('status')
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
                                       value="{{ old('data_infracao', $infracao->data_infracao ? $infracao->data_infracao->format('Y-m-d\TH:i') : '') }}"
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
                                        <option value="{{ $embarcacao->id }}" {{ old('embarcacao_id', $infracao->embarcacao_id) == $embarcacao->id ? 'selected' : '' }}>
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
                                        <option value="{{ $terminal->id }}" {{ old('terminal_id', $infracao->terminal_id) == $terminal->id ? 'selected' : '' }}>
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
                            <!-- Descrição da Infração -->
                            <div>
                                <label for="descricao_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrição <span class="text-red-500">*</span>
                                </label>
                                <textarea name="descricao_infracao" 
                                          id="descricao_infracao"
                                          rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('descricao_infracao') border-red-500 @enderror"
                                          placeholder="Descreva detalhadamente a infração ambiental..."
                                          required>{{ old('descricao_infracao', $infracao->descricao_infracao) }}</textarea>
                                @error('descricao_infracao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Local da Infração -->
                            <div>
                                <label for="local_infracao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Local da Infração <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="local_infracao" 
                                       id="local_infracao"
                                       value="{{ old('local_infracao', $infracao->local_infracao) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('local_infracao') border-red-500 @enderror"
                                       placeholder="Ex: Berço 3, Terminal de Contêineres"
                                       required>
                                @error('local_infracao')
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
                                          placeholder="Informações adicionais sobre a infração...">{{ old('observacoes', $infracao->observacoes) }}</textarea>
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
                            <!- Valor da Multa->
                            <div>
                                <label for="valor_multa" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor da Multa (R$)
                                </label>
                                <input type="number" 
                                       name="valor_multa" 
                                       id="valor_multa"
                                       value="{{ old('valor_multa', $infracao->valor_multa) }}"
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
                                    Prazo para Regularização <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       name="prazo_regularizacao" 
                                       id="prazo_regularizacao"
                                       value="{{ old('prazo_regularizacao', $infracao->prazo_regularizacao ? $infracao->prazo_regularizacao->format('Y-m-d') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('prazo_regularizacao') border-red-500 @enderror"
                                       min="{{ date('Y-m-d') }}"
                                       required>
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
                                Informações sobre evidências coletadas (formato JSON)
                            </p>
                            <textarea name="evidencias" 
                                      id="evidencias"
                                      rows="6"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('evidencias') border-red-500 @enderror font-mono text-sm"
                                      placeholder='{"fotos": [], "documentos": [], "testemunhas": [], "amostras": []}'
                                      >{{ old('evidencias', is_array($infracao->evidencias) ? json_encode($infracao->evidencias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $infracao->evidencias) }}</textarea>
                            @error('evidencias')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div-->

                    <!-- Análise e Regularização (apenas se aplicável) ->
                    @if(in_array($infracao->status, ['regularizada', 'em_regularizacao', 'notificada']))
                    <!-div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-clipboard-check text-blue-600 mr-2"></i>
                            Análise e Regularização
                        </h4>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <!- Data de Notificação ->
                            @if($infracao->data_notificacao)
                            <div class="mb-4">
                                <label for="data_notificacao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de Notificação
                                </label>
                                <input type="datetime-local" 
                                       name="data_notificacao" 
                                       id="data_notificacao"
                                       value="{{ old('data_notificacao', $infracao->data_notificacao ? $infracao->data_notificacao->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_notificacao') border-red-500 @enderror">
                                @error('data_notificacao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif

                            <!- Data de Regularização ->
                            @if($infracao->status === 'regularizada')
                            <div class="mb-4">
                                <label for="data_regularizacao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de Regularização
                                </label>
                                <input type="datetime-local" 
                                       name="data_regularizacao" 
                                       id="data_regularizacao"
                                       value="{{ old('data_regularizacao', $infracao->data_regularizacao ? $infracao->data_regularizacao->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_regularizacao') border-red-500 @enderror">
                                @error('data_regularizacao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif

                            <div class="text-sm text-blue-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                Esta infração passou por processo de notificação/regularização. Algumas informações podem estar bloqueadas para edição.
                            </div>
                        </div>
                    </div->
                    @endif-->

                    <!-- Status Ativo -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-toggle-on text-blue-600 mr-2"></i>
                            Configurações
                        </h4>
                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $infracao->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Infração ativa
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Desmarque para desativar a infração</p>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('infracoes-ambientais.show', $infracao) }}" 
                           class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>Atualizar Infração
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

        // Auto-sugestão de gravidade e valor baseado no tipo
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

        // Auto-preenchimento do prazo de regularização baseado na gravidade
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

        // Auto-preenchimento de datas baseado no status
        document.getElementById('status').addEventListener('change', function() {
            const agora = new Date().toISOString().slice(0, 16);
            
            if (this.value === 'notificada') {
                const dataNotificacao = document.getElementById('data_notificacao');
                if (dataNotificacao && !dataNotificacao.value) {
                    dataNotificacao.value = agora;
                }
            } else if (this.value === 'regularizada') {
                const dataRegularizacao = document.getElementById('data_regularizacao');
                if (dataRegularizacao && !dataRegularizacao.value) {
                    dataRegularizacao.value = agora;
                }
            }
        });

        // Bloquear edição de campos sensíveis para infrações regularizadas
        const currentStatus = '{{ $infracao->status }}';
        if (['regularizada'].includes(currentStatus)) {
            // Bloquear alguns campos críticos
            document.getElementById('tipo').disabled = true;
            document.getElementById('gravidade').disabled = true;
            document.getElementById('descricao').readOnly = true;
            document.getElementById('valor_multa').readOnly = true;
            
            // Adicionar aviso visual
            const blockedFields = ['tipo', 'gravidade', 'descricao', 'valor_multa'];
            blockedFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.classList.add('bg-gray-100');
                    field.title = 'Campo bloqueado para edição devido ao status da infração';
                }
            });
        }

        // Formatação do valor da multa
        document.getElementById('valor_multa').addEventListener('input', function() {
            let value = this.value.replace(/[^\d.,]/g, '');
            this.value = value;
        });
    </script>
    @endpush
</x-app-layout>
                            