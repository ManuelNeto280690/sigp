<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-ship text-blue-600 mr-3"></i>
                    Editar Inspeção do Navio
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Atualize as informações da inspeção #{{ $inspecao->id }}
                </p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('inspecoes.show', $inspecao) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-eye mr-2"></i>Visualizar
                </a>
                <a href="{{ route('inspecoes.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Mensagens de Erro -->
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    Há alguns problemas com os dados informados:
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
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
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('inspecoes.update', $inspecao) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Básicas
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Tipo de Inspeção -->
                            <div>
                                <label for="tipo_inspecao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Inspeção <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo_inspecao" 
                                        id="tipo_inspecao"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('tipo_inspecao') border-red-500 @enderror">
                                    <option value="">Selecione o tipo</option>
                                    <option value="seguranca" {{ old('tipo_inspecao', $inspecao->tipo_inspecao) === 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="ambiental" {{ old('tipo_inspecao', $inspecao->tipo_inspecao) === 'ambiental' ? 'selected' : '' }}>Ambiental</option>
                                    <option value="estrutural" {{ old('tipo_inspecao', $inspecao->tipo_inspecao) === 'estrutural' ? 'selected' : '' }}>Estrutural</option>
                                    <option value="equipamentos" {{ old('tipo_inspecao', $inspecao->tipo_inspecao) === 'equipamentos' ? 'selected' : '' }}>Equipamentos</option>
                                    <option value="documentacao" {{ old('tipo_inspecao', $inspecao->tipo_inspecao) === 'documentacao' ? 'selected' : '' }}>Documentação</option>
                                    <option value="operacional" {{ old('tipo_inspecao', $inspecao->tipo_inspecao) === 'operacional' ? 'selected' : '' }}>Operacional</option>
                                    <option value="sanitaria" {{ old('tipo_inspecao', $inspecao->tipo_inspecao) === 'sanitaria' ? 'selected' : '' }}>Sanitária</option>
                                    <option value="emergencial" {{ old('tipo_inspecao', $inspecao->tipo_inspecao) === 'emergencial' ? 'selected' : '' }}>Emergencial</option>
                                </select>
                                @error('tipo_inspecao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data da Inspeção -->
                            <div>
                                <label for="data_inspecao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data da Inspeção <span class="text-red-500">*</span>
                                </label>
                                <input type="date" 
                                       name="data_inspecao" 
                                       id="data_inspecao"
                                       value="{{ old('data_inspecao', $inspecao->data_inspecao ? $inspecao->data_inspecao->format('Y-m-d') : '') }}"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_inspecao') border-red-500 @enderror">
                                @error('data_inspecao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Hora de Início -->
                            <div>
                                <label for="hora_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                                    Hora de Início <span class="text-red-500">*</span>
                                </label>
                                <input type="time" 
                                       name="hora_inicio" 
                                       id="hora_inicio"
                                       value="{{ old('hora_inicio', $inspecao->hora_inicio ? $inspecao->hora_inicio->format('H:i') : '') }}"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('hora_inicio') border-red-500 @enderror">
                                @error('hora_inicio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Hora de Fim -->
                            <div>
                                <label for="hora_fim" class="block text-sm font-medium text-gray-700 mb-2">
                                    Hora de Fim
                                </label>
                                <input type="time" 
                                       name="hora_fim" 
                                       id="hora_fim"
                                       value="{{ old('hora_fim', $inspecao->hora_fim ? $inspecao->hora_fim->format('H:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('hora_fim') border-red-500 @enderror">
                                @error('hora_fim')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Prioridade -->
                            <div>
                                <label for="prioridade" class="block text-sm font-medium text-gray-700 mb-2">
                                    Prioridade <span class="text-red-500">*</span>
                                </label>
                                <select name="prioridade" 
                                        id="prioridade"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('prioridade') border-red-500 @enderror">
                                    <option value="">Selecione a prioridade</option>
                                    <option value="baixa" {{ old('prioridade', $inspecao->prioridade) === 'baixa' ? 'selected' : '' }}>Baixa</option>
                                    <option value="media" {{ old('prioridade', $inspecao->prioridade) === 'media' ? 'selected' : '' }}>Média</option>
                                    <option value="alta" {{ old('prioridade', $inspecao->prioridade) === 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="critica" {{ old('prioridade', $inspecao->prioridade) === 'critica' ? 'selected' : '' }}>Crítica</option>
                                </select>
                                @error('prioridade')
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
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('status') border-red-500 @enderror">
                                    <option value="">Selecione o status</option>
                                    <option value="agendada" {{ old('status', $inspecao->status) === 'agendada' ? 'selected' : '' }}>Agendada</option>
                                    <option value="em_andamento" {{ old('status', $inspecao->status) === 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="concluida" {{ old('status', $inspecao->status) === 'concluida' ? 'selected' : '' }}>Concluída</option>
                                    <option value="cancelada" {{ old('status', $inspecao->status) === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data Programada -->
                            <div class="md:col-span-2">
                                <label for="data_programada" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data e Hora Programada
                                </label>
                                <input type="datetime-local" 
                                       name="data_programada" 
                                       id="data_programada"
                                       value="{{ old('data_programada', $inspecao->data_programada ? \Carbon\Carbon::parse($inspecao->data_programada)->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_programada') border-red-500 @enderror">
                                @error('data_programada')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Local Específico -->
                            <div class="md:col-span-2">
                                <label for="local_especifico" class="block text-sm font-medium text-gray-700 mb-2">
                                    Local Específico
                                </label>
                                <input type="text" 
                                       name="local_especifico" 
                                       id="local_especifico"
                                       value="{{ old('local_especifico', $inspecao->local_especifico) }}"
                                       placeholder="Ex: Porão de carga nº 2, Sala de máquinas, Convés principal..."
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('local_especifico') border-red-500 @enderror">
                                @error('local_especifico')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Vinculações -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-link text-blue-600 mr-2"></i>
                            Vinculações
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Embarcação -->
                            <div>
                                <label for="embarcacao_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Embarcação <span class="text-red-500">*</span>
                                </label>
                                <select name="embarcacao_id" 
                                        id="embarcacao_id"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('embarcacao_id') border-red-500 @enderror">
                                    <option value="">Selecione a embarcação</option>
                                    @foreach($embarcacoes as $embarcacao)
                                        <option value="{{ $embarcacao->id }}" 
                                                {{ old('embarcacao_id', $inspecao->embarcacao_id) == $embarcacao->id ? 'selected' : '' }}>
                                            {{ $embarcacao->nome }} - {{ $embarcacao->tipo_embarcacao }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('embarcacao_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Inspetor -->
                            <div>
                                <label for="inspetor_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Inspetor Responsável <span class="text-red-500">*</span>
                                </label>
                                <select name="inspetor_id" 
                                        id="inspetor_id"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('inspetor_id') border-red-500 @enderror">
                                    <option value="">Selecione o inspetor</option>
                                    @foreach($inspetores as $inspetor)
                                        <option value="{{ $inspetor->id }}" 
                                                {{ old('inspetor_id', $inspecao->inspetor_id) == $inspetor->id ? 'selected' : '' }}>
                                            {{ $inspetor->name }} - {{ $inspetor->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('inspetor_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Terminal -->
                            <div class="md:col-span-2">
                                <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Terminal
                                </label>
                                <select name="terminal_id" 
                                        id="terminal_id"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('terminal_id') border-red-500 @enderror">
                                    <option value="">Selecione o terminal (opcional)</option>
                                    @foreach($terminais as $terminal)
                                        <option value="{{ $terminal->id }}" 
                                                {{ old('terminal_id', $inspecao->terminal_id) == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->nome }} - {{ $terminal->localizacao }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('terminal_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes da Inspeção -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                            Detalhes da Inspeção
                        </h3>
                        <div class="space-y-6">
                            <!-- Objetivo -->
                            <div>
                                <label for="objetivo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Objetivo da Inspeção <span class="text-red-500">*</span>
                                </label>
                                <textarea name="objetivo" 
                                          id="objetivo"
                                          rows="3"
                                          required
                                          placeholder="Descreva o objetivo e escopo da inspeção..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('objetivo') border-red-500 @enderror">{{ old('objetivo', $inspecao->objetivo) }}</textarea>
                                @error('objetivo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Checklist -->
                            <div>
                                <label for="checklist" class="block text-sm font-medium text-gray-700 mb-2">
                                    Checklist de Inspeção
                                </label>
                                <textarea name="checklist" 
                                          id="checklist"
                                          rows="4"
                                          placeholder="Liste os itens a serem verificados durante a inspeção..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('checklist') border-red-500 @enderror">{{ old('checklist', $inspecao->checklist) }}</textarea>
                                @error('checklist')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Detalhes Técnicos -->
                            <div>
                                <label for="detalhes_tecnicos" class="block text-sm font-medium text-gray-700 mb-2">
                                    Detalhes Técnicos (JSON)
                                </label>
                                <textarea name="detalhes_tecnicos" 
                                          id="detalhes_tecnicos"
                                          rows="6"
                                          placeholder='{"equipamentos": [], "normas_aplicaveis": [], "criterios_avaliacao": {}}'
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 font-mono text-sm @error('detalhes_tecnicos') border-red-500 @enderror">{{ old('detalhes_tecnicos', $inspecao->detalhes_tecnicos) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Formato JSON para detalhes técnicos estruturados</p>
                                @error('detalhes_tecnicos')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Observações -->
                            <div>
                                <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Observações Gerais
                                </label>
                                <textarea name="observacoes" 
                                          id="observacoes"
                                          rows="3"
                                          placeholder="Observações adicionais sobre a inspeção..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('observacoes') border-red-500 @enderror">{{ old('observacoes', $inspecao->observacoes) }}</textarea>
                                @error('observacoes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Upload de Documentos -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-paperclip text-blue-600 mr-2"></i>
                            Documentos
                        </h3>
                        <div class="space-y-4">
                            <!-- Documentos Anexos -->
                            <div>
                                <label for="documentos_anexos" class="block text-sm font-medium text-gray-700 mb-2">
                                    Anexar Documentos
                                </label>
                                <input type="file" 
                                       name="documentos_anexos[]" 
                                       id="documentos_anexos"
                                       multiple
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('documentos_anexos') border-red-500 @enderror">
                                <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PDF, DOC, DOCX, JPG, JPEG, PNG (máx. 10MB cada)</p>
                                @error('documentos_anexos')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Documentos Existentes -->
                            @if($inspecao->documentos_anexos)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Documentos Atuais:</h4>
                                    <div class="text-sm text-gray-600">
                                        @php
                                            $documentos = is_string($inspecao->documentos_anexos) ? json_decode($inspecao->documentos_anexos, true) : $inspecao->documentos_anexos;
                                        @endphp
                                        @if(is_array($documentos) && count($documentos) > 0)
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($documentos as $documento)
                                                    <li>{{ basename($documento) }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-gray-500">Nenhum documento anexado</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Relatório Final (apenas para inspeções concluídas) -->
                    @if(in_array($inspecao->status, ['concluida']))
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                            Relatório Final
                        </h3>
                        <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 space-y-6">
                            <!-- Resultado -->
                            <div>
                                <label for="resultado" class="block text-sm font-medium text-gray-700 mb-2">
                                    Resultado da Inspeção <span class="text-red-500">*</span>
                                </label>
                                <select name="resultado" 
                                        id="resultado"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('resultado') border-red-500 @enderror">
                                    <option value="">Selecione o resultado</option>
                                    <option value="aprovado" {{ old('resultado', $inspecao->resultado) === 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                                    <option value="aprovado_com_ressalvas" {{ old('resultado', $inspecao->resultado) === 'aprovado_com_ressalvas' ? 'selected' : '' }}>Aprovado com Ressalvas</option>
                                    <option value="reprovado" {{ old('resultado', $inspecao->resultado) === 'reprovado' ? 'selected' : '' }}>Reprovado</option>
                                    <option value="pendente_correcoes" {{ old('resultado', $inspecao->resultado) === 'pendente_correcoes' ? 'selected' : '' }}>Pendente de Correções</option>
                                </select>
                                @error('resultado')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Relatório Final -->
                            <div>
                                <label for="relatorio_final" class="block text-sm font-medium text-gray-700 mb-2">
                                    Relatório Final <span class="text-red-500">*</span>
                                </label>
                                <textarea name="relatorio_final" 
                                          id="relatorio_final"
                                          rows="6"
                                          required
                                          placeholder="Descreva os resultados da inspeção, não conformidades encontradas, recomendações..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('relatorio_final') border-red-500 @enderror">{{ old('relatorio_final', $inspecao->relatorio_final) }}</textarea>
                                @error('relatorio_final')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-blue-100 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                    <p class="text-sm text-blue-800">
                                        Esta inspeção foi concluída. Você pode atualizar o relatório final e resultado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Configurações -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-cog text-blue-600 mr-2"></i>
                            Configurações
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active"
                                       value="1"
                                       {{ old('is_active', $inspecao->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-3 block text-sm font-medium text-gray-900">
                                    Inspeção ativa
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 ml-7">Desmarque para desativar a inspeção</p>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('inspecoes.show', $inspecao) }}" 
                           class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-save mr-2"></i>Atualizar Inspeção
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Exclusão -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmar Exclusão</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Tem certeza que deseja excluir esta inspeção? Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="flex justify-center space-x-3 mt-4">
                    <button onclick="closeDeleteModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                    <button onclick="document.getElementById('deleteForm').submit()" 
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário de Exclusão (oculto) -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    @push('scripts')
    <script>
        // Modal de Exclusão
        function confirmDelete(id) {
            const form = document.getElementById('deleteForm');
            form.action = `/inspecoes/${id}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Fechar modal ao clicar fora
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Validação de JSON para detalhes_tecnicos
        document.getElementById('detalhes_tecnicos').addEventListener('blur', function() {
            const value = this.value.trim();
            if (value && value !== '') {
                try {
                    JSON.parse(value);
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                } catch (e) {
                    this.classList.remove('border-green-500');
                    this.classList.add('border-red-500');
                }
            } else {
                this.classList.remove('border-red-500', 'border-green-500');
            }
        });

        // Formatação automática do JSON
        document.getElementById('detalhes_tecnicos').addEventListener('blur', function() {
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

        // Atualização automática da prioridade baseada no tipo
        document.getElementById('tipo').addEventListener('change', function() {
            const prioridadeSelect = document.getElementById('prioridade');
            if (this.value === 'emergencial') {
                prioridadeSelect.value = 'critica';
            } else if (this.value === 'seguranca') {
                prioridadeSelect.value = 'alta';
            }
        });

        // Sugestões de objetivo baseadas no tipo de inspeção
        document.getElementById('tipo').addEventListener('change', function() {
            const objetivoTextarea = document.getElementById('objetivo');
            if (this.value && !objetivoTextarea.value) {
                const sugestoes = {
                    'seguranca': 'Verificar conformidade com normas de segurança marítima e condições dos equipamentos de segurança.',
                    'ambiental': 'Avaliar conformidade com regulamentações ambientais e sistemas de prevenção à poluição.',
                    'estrutural': 'Inspecionar integridade estrutural do casco, convés e estruturas principais.',
                    'equipamentos': 'Verificar funcionamento e manutenção de equipamentos críticos da embarcação.',
                    'documentacao': 'Revisar documentação obrigatória, certificados e registros de manutenção.',
                    'rotina': 'Inspeção periódica de rotina conforme cronograma estabelecido.',
                    'emergencial': 'Inspeção emergencial devido a incidente ou não conformidade identificada.'
                };
                
                if (sugestoes[this.value]) {
                    objetivoTextarea.value = sugestoes[this.value];
                }
            }
        });

        // Auto-preenchimento de local específico baseado na embarcação
        document.getElementById('embarcacao_id').addEventListener('change', function() {
            const localInput = document.getElementById('local_especifico');
            if (this.value && !localInput.value) {
                const selectedOption = this.options[this.selectedIndex];
                const embarcacaoTipo = selectedOption.text.split(' - ')[1];
                
                // Sugestões baseadas no tipo de embarcação
                const sugestoes = {
                    'container': 'Porão de carga',
                    'graneis_solidos': 'Porões de granéis',
                    'graneis_liquidos': 'Tanques de carga',
                    'carga_geral': 'Convés de carga',
                    'passageiros': 'Convés principal',
                    'tanque': 'Tanques de carga',
                    'ro_ro': 'Convés de veículos',
                    'frigorifico': 'Câmaras frigoríficas'
                };
                
                if (sugestoes[embarcacaoTipo]) {
                    localInput.value = sugestoes[embarcacaoTipo];
                }
            }
        });

        // Validação de data mínima (apenas para inspeções agendadas)
        const statusSelect = document.getElementById('status');
        const dataInput = document.getElementById('data_programada');
        
        function validateDate() {
            if (statusSelect.value === 'agendada') {
                const agora = new Date();
                const dataInputValue = new Date(dataInput.value);
                if (dataInputValue < agora) {
                    const agoraFormatted = agora.toISOString().slice(0, 16);
                    dataInput.value = agoraFormatted;
                    alert('A data programada não pode ser anterior à data/hora atual para inspeções agendadas.');
                }
            }
        }

        dataInput.addEventListener('change', validateDate);
        statusSelect.addEventListener('change', validateDate);

        // Bloquear edição de campos sensíveis para inspeções concluídas
        const currentStatus = '{{ $inspecao->status }}';
        if (currentStatus === 'concluida') {
            // Bloquear alguns campos críticos
            document.getElementById('tipo').disabled = true;
            document.getElementById('objetivo').readOnly = true;
            document.getElementById('local_especifico').readOnly = true;
            document.getElementById('data_programada').readOnly = true;
            
            // Adicionar aviso visual
            const blockedFields = ['tipo', 'objetivo', 'local_especifico', 'data_programada'];
            blockedFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.classList.add('bg-gray-100');
                    field.title = 'Campo bloqueado para edição devido ao status da inspeção';
                }
            });
        }
    </script>
    @endpush
</x-app-layout>