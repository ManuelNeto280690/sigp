<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-edit text-white text-xs"></i>
                    </div>
                    {{ __('Editar Movimento') }} #{{ $entradaSaida->numero_movimento }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Altere as informações do movimento de embarcação</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('entrada-saida-embarcacao.show', $entradaSaida) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-eye mr-1"></i>Visualizar
                </a>
                <a href="{{ route('entrada-saida-embarcacao.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-list mr-1"></i>Listar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

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

            <!-- Formulário de Edição -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-edit mr-2" style="color: #0084de;"></i>
                        Dados do Movimento
                    </h3>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('entrada-saida-embarcacao.update', $entradaSaida) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
    <h4 class="text-base font-semibold text-gray-900 flex items-center">
        <i class="fas fa-clock mr-2 text-blue-600"></i>
        Informações Iniciais (Em Espera)
    </h4>
</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="embarcacao_id" class="block text-sm font-medium text-gray-700 mb-2">Embarcação *</label>
                                <select name="embarcacao_id" id="embarcacao_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" style="focus:ring-color: #0084de;" required>
                                    <option value="">Selecione uma embarcação</option>
                                    @foreach($embarcacoes as $embarcacao)
                                        <option value="{{ $embarcacao->id }}" {{ old('embarcacao_id', $entradaSaida->embarcacao_id) == $embarcacao->id ? 'selected' : '' }}>
                                            {{ $embarcacao->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">Terminal *</label>
                                <select name="terminal_id" id="terminal_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                    @php($terminalAtual = old('terminal_id', $entradaSaida->terminal_id))
                                    @php($tSel = isset($terminais) ? $terminais->firstWhere('id', $terminalAtual) : null)
                                    @if($tSel)
                                        <option value="{{ $tSel->id }}" selected>{{ $tSel->nome }} - {{ $tSel->codigo }}</option>
                                    @elseif($terminalAtual)
                                        <option value="{{ $terminalAtual }}" selected>Terminal selecionado</option>
                                    @else
                                        <option value="">Selecione uma embarcação e preencha as datas primeiro</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="tipo_movimento" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimento *</label>
                                <select name="tipo_movimento" id="tipo_movimento" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="entrada" {{ old('tipo_movimento', $entradaSaida->tipo_movimento) == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                    <option value="saida" {{ old('tipo_movimento', $entradaSaida->tipo_movimento) == 'saida' ? 'selected' : '' }}>Saída</option>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                                    <option value="programado" {{ old('status', $entradaSaida->status) == 'programado' ? 'selected' : '' }}>Programado</option>
                                    <option value="autorizado" {{ old('status', $entradaSaida->status) == 'autorizado' ? 'selected' : '' }}>Autorizado</option>
                                    <option value="em_andamento" {{ old('status', $entradaSaida->status) == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="concluido" {{ old('status', $entradaSaida->status) == 'concluido' ? 'selected' : '' }}>Concluído</option>
                                    <option value="cancelado" {{ old('status', $entradaSaida->status) == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 mb-4">
    <h4 class="text-base font-semibold text-gray-900 flex items-center">
        <i class="fas fa-anchor mr-2 text-green-600"></i>
        Atracado
    </h4>
</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="estado_embarcacao" class="block text-sm font-medium text-gray-700 mb-2">Estado da Embarcação</label>
                                <select name="estado_embarcacao" id="estado_embarcacao" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                                    <option value="">Selecione o estado</option>
                                    <option value="esperado" {{ old('estado_embarcacao', $entradaSaida->estado_embarcacao) == 'esperado' ? 'selected' : '' }}>Esperado</option>
                                    <option value="atracado" {{ old('estado_embarcacao', $entradaSaida->estado_embarcacao) == 'atracado' ? 'selected' : '' }}>Atracado</option>
                                    <option value="operando" {{ old('estado_embarcacao', $entradaSaida->estado_embarcacao) == 'operando' ? 'selected' : '' }}>Operando</option>
                                    <option value="partido" {{ old('estado_embarcacao', $entradaSaida->estado_embarcacao) == 'partido' ? 'selected' : '' }}>Partido</option>
                                </select>
                            </div>
                        </div>

                        <!-- Detalhes do Movimento -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="berco" class="block text-sm font-medium text-gray-700 mb-2">Berço</label>
                                <select name="berco" id="berco" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @if(old('berco', $entradaSaida->berco))
                                        <option value="{{ old('berco', $entradaSaida->berco) }}" selected>{{ old('berco', $entradaSaida->berco) }}</option>
                                    @else
                                        <option value="">Selecione um terminal primeiro</option>
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label for="guindaste_id" class="block text-sm font-medium text-gray-700 mb-2">Guindaste</label>
                                <select name="guindaste_id" id="guindaste_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @php($guindasteAtual = old('guindaste_id', $entradaSaida->guindaste_id ?? null))
                                    @if($guindasteAtual)
                                        <option value="{{ $guindasteAtual }}" selected>Guindaste selecionado</option>
                                    @else
                                        <option value="">Selecione um terminal primeiro</option>
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label for="agente_maritimo" class="block text-sm font-medium text-gray-700 mb-2">Agente Marítimo</label>
                                <input type="text" name="agente_maritimo" id="agente_maritimo" value="{{ old('agente_maritimo', $entradaSaida->agente_maritimo) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Nome do agente marítimo">
                            </div>
                        </div>

                        <!-- Capitanias -->
                        <!--div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="capitania_origem" class="block text-sm font-medium text-gray-700 mb-2">Capitania de Origem</label>
                                <input type="text" name="capitania_origem" id="capitania_origem" value="{{ old('capitania_origem', $entradaSaida->capitania_origem) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Ex: Capitania dos Portos de Santos">
                            </div>

                            <div>
                                <label for="capitania_destino" class="block text-sm font-medium text-gray-700 mb-2">Capitania de Destino</label>
                                <input type="text" name="capitania_destino" id="capitania_destino" value="{{ old('capitania_destino', $entradaSaida->capitania_destino) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Ex: Capitania dos Portos do Rio de Janeiro">
                            </div>
                        </div-->

                        <!-- Data e Hora -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="data_programada" class="block text-sm font-medium text-gray-700 mb-2">Data Programada *</label>
                                <input type="datetime-local" name="data_programada" id="data_programada" value="{{ old('data_programada', $entradaSaida->data_programada ? $entradaSaida->data_programada->format('Y-m-d\TH:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" required>
                            </div>

                            <div>
                                <label for="data_efetiva" class="block text-sm font-medium text-gray-700 mb-2">Data Efetiva</label>
                                <input type="datetime-local" name="data_efetiva" id="data_efetiva" value="{{ old('data_efetiva', $entradaSaida->data_efetiva ? $entradaSaida->data_efetiva->format('Y-m-d\TH:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                            </div>
                        </div>

                        <!-- Portos -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="porto_origem" class="block text-sm font-medium text-gray-700 mb-2">Porto de Origem</label>
                                <input type="text" name="porto_origem" id="porto_origem" value="{{ old('porto_origem', $entradaSaida->porto_origem) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Ex: Rio de Janeiro">
                            </div>
                            <div>
                                <label for="porto_destino" class="block text-sm font-medium text-gray-700 mb-2">Porto de Destino</label>
                                <input type="text" name="porto_destino" id="porto_destino" value="{{ old('porto_destino', $entradaSaida->porto_destino) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Ex: Santos">
                            </div>
                        </div>

                        <!-- Tempos -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="eta" class="block text-sm font-medium text-gray-700 mb-2">ETA - Chegada Estimada</label>
                                <input type="datetime-local" name="eta" id="eta" value="{{ old('eta', $entradaSaida->eta ? $entradaSaida->eta->format('Y-m-d\\TH:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="etd" class="block text-sm font-medium text-gray-700 mb-2">ETD - Saída Estimada</label>
                                <input type="datetime-local" name="etd" id="etd" value="{{ old('etd', $entradaSaida->etd ? $entradaSaida->etd->format('Y-m-d\\TH:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="ata" class="block text-sm font-medium text-gray-700 mb-2">ATA - Chegada Real</label>
                                <input type="datetime-local" name="ata" id="ata" value="{{ old('ata', $entradaSaida->ata ? $entradaSaida->ata->format('Y-m-d\\TH:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                            </div>
                           
                        </div>

                        <!-- Motivo -->
                         <div class="mt-8 mb-4">
                                    <h4 class="text-base font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-arrow-right mr-2 text-red-600"></i>
                                        Saída
                                    </h4>
                                </div>

                         <div>
                                
                                <label for="atd" class="block text-sm font-medium text-gray-700 mb-2">ATD - Saída Real</label>
                                <input type="datetime-local" name="atd" id="atd" value="{{ old('atd', $entradaSaida->atd ? $entradaSaida->atd->format('Y-m-d\\TH:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                            </div>
                        <div class="mb-6">
                            <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">Motivo</label>
                            <textarea name="motivo" id="motivo" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Motivo do movimento...">{{ old('motivo', $entradaSaida->motivo) }}</textarea>
                        </div>

                        <!-- Observações -->
                        <div class="mb-6">
                            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea name="observacoes" id="observacoes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Informações adicionais sobre o movimento...">{{ old('observacoes', $entradaSaida->observacoes) }}</textarea>
                        </div>

                        <!-- Documentos Existentes -->
                        @if($entradaSaida->documentos_apresentados && count($entradaSaida->documentos_apresentados) > 0)
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Documentos Atuais</label>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="space-y-2">
                                        @foreach($entradaSaida->documentos_apresentados as $index => $documento)
                                            <div class="flex items-center justify-between p-2 bg-white rounded border">
                                                <div class="flex items-center">
                                                    <i class="fas fa-file text-gray-400 mr-2"></i>
                                                    <span class="text-sm text-gray-900">
                                                        @if(is_array($documento))
                                                            {{ $documento['nome_original'] ?? $documento['nome_arquivo'] ?? 'Documento' }}
                                                        @else
                                                            {{ $documento }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    @if(is_array($documento) && isset($documento['caminho']))
                                                        <a href="{{ Storage::url($documento['caminho']) }}" target="_blank" class="text-blue-500 hover:text-blue-700 text-sm" title="Visualizar">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" onclick="removerDocumentoExistente({{ $index }})" class="text-red-500 hover:text-red-700 text-sm" title="Remover">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Novos Documentos -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Adicionar Novos Documentos</label>
                            <div id="documentos-container" class="space-y-3">
                                <div class="documento-item flex items-center space-x-3">
                                    <input type="file" name="novos_documentos[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <button type="button" onclick="adicionarDocumento()" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-md text-sm">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Formatos aceitos: PDF, DOC, DOCX, JPG, JPEG, PNG</p>
                        </div>

                        <!-- Campos ocultos para documentos removidos -->
                        <input type="hidden" name="documentos_removidos" id="documentos_removidos" value="">

                        <!-- Botões de Ação -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('entrada-saida-embarcacao.show', $entradaSaida) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                <i class="fas fa-times mr-1"></i>Cancelar
                            </a>
                            <button type="submit" class="text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow-lg hover:shadow-xl" style="background: #0084de;">
                                <i class="fas fa-save mr-1"></i>Atualizar Movimento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adicionarDocumento() {
            const container = document.getElementById('documentos-container');
            const novoItem = document.createElement('div');
            novoItem.className = 'documento-item flex items-center space-x-3';
            novoItem.innerHTML = `
                <input type="file" name="novos_documentos[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <button type="button" onclick="removerDocumento(this)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(novoItem);
        }

        function removerDocumento(button) {
            button.parentElement.remove();
        }

        function removerDocumentoExistente(index) {
            if (confirm('Tem certeza que deseja remover este documento?')) {
                // Adicionar o índice à lista de documentos removidos
                const documentosRemovidos = document.getElementById('documentos_removidos');
                const removidos = documentosRemovidos.value ? documentosRemovidos.value.split(',') : [];
                removidos.push(index);
                documentosRemovidos.value = removidos.join(',');
                
                // Remover o elemento da interface
                event.target.closest('.flex').remove();
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const embarcacaoSelect = document.getElementById('embarcacao_id');
            const dataProgramadaInput = document.getElementById('data_programada');
            const etaInput = document.getElementById('eta');
            const etdInput = document.getElementById('etd');
            const terminalSelect = document.getElementById('terminal_id');
            const bercoSelect = document.getElementById('berco');
            const guindasteSelect = document.getElementById('guindaste_id');

            const terminalAtual = `{{ old('terminal_id', $entradaSaida->terminal_id) }}`;
            const bercoAtual = `{{ old('berco', $entradaSaida->berco) }}`;
            const guindasteAtual = `{{ old('guindaste_id', $entradaSaida->guindaste_id) }}`;

            let tipoEmbarcacao = null;
            let caladoNavio = null;



            async function carregarEmbarcacaoInfo(id) {
                const res = await fetch(`/planejamento/embarcacao/${id}`);
                if (!res.ok) return;
                const data = await res.json();
                tipoEmbarcacao = data.tipo_embarcacao;
                caladoNavio = data.calado;
                etaInput.disabled = false;
                etdInput.disabled = false;
            }

            async function carregarTerminaisDisponiveis() {
                // Use ETA/ETD if available, otherwise fallback to Data Programada
                let dataEntrada = etaInput.value;
                let dataSaida = etdInput.value;

                if (!dataEntrada && dataProgramadaInput && dataProgramadaInput.value) {
                    dataEntrada = dataProgramadaInput.value;
                    // If no ETD, assume 24h duration or same time for check
                    if (!dataSaida) {
                        // Create a date object, add 24 hours, format back to datetime-local string
                        const date = new Date(dataEntrada);
                        date.setHours(date.getHours() + 24);
                        dataSaida = date.toISOString().slice(0, 16);
                    }
                }

                if (!tipoEmbarcacao || !caladoNavio || !dataEntrada || !dataSaida) {
                    return;
                }

                // Capture current terminal name/info from the DOM before we potentially wipe it
                // We access the global terminalAtual/bercoAtual variables defined later in this scope
                // (They are available because this function is called after they are initialized)
                let currentTerminalName = 'Terminal Atual';
                if (terminalSelect.value && terminalSelect.options[terminalSelect.selectedIndex]) {
                    currentTerminalName = terminalSelect.options[terminalSelect.selectedIndex].text;
                } else if (terminalAtual) {
                     // Try to find it in the existing options even if not selected
                     const opt = terminalSelect.querySelector(`option[value="${terminalAtual}"]`);
                     if (opt) currentTerminalName = opt.text;
                }

                const query = new URLSearchParams({
                    tipo_embarcacao: tipoEmbarcacao,
                    calado: caladoNavio,
                    data_entrada: dataEntrada,
                    data_saida: dataSaida,
                    ignorar_movimento_id: '{{ $entradaSaida->id }}'
                });
                const res = await fetch(`/planejamento/terminais-disponiveis?${query.toString()}`);
                if (!res.ok) { return; }
                const terminais = await res.json();
                
                // FORCE INSERT CURRENT TERMINAL IF MISSING
                // This fixes the issue where strict API filters (e.g. draft, conflict) hide the currently selected terminal
                if (terminalAtual && Array.isArray(terminais)) {
                    const exists = terminais.some(t => t.id == terminalAtual);
                    if (!exists) {
                        terminais.push({
                            id: terminalAtual,
                            nome: currentTerminalName, 
                            codigo: '', // Name likely contains code already
                            bercos_livres: bercoAtual ? [bercoAtual] : [], // Ensure current berth is available
                            guindastes_disponiveis: [] // Will be fetched by guindaste logic
                        });
                    }
                }

                if (!Array.isArray(terminais) || terminais.length === 0) {
                    terminalSelect.innerHTML = '<option value="">Nenhum terminal disponível</option>';
                    terminalSelect.disabled = true;
                    bercoSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                    bercoSelect.disabled = true;
                    return;
                }
                terminalSelect.innerHTML = '<option value="">Selecione um terminal</option>';
                terminais.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = `${t.nome} - ${t.codigo}`;
                    opt.setAttribute('data-bercos', JSON.stringify(t.bercos_livres || []));
                    opt.setAttribute('data-guindastes', JSON.stringify(t.guindastes_disponiveis || []));
                    terminalSelect.appendChild(opt);
                });
                terminalSelect.disabled = false;
                bercoSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                bercoSelect.disabled = true;
                guindasteSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                guindasteSelect.disabled = true;
            }

            async function carregarGuindastesPorTerminal(terminalId) {
                const res = await fetch(`/planejamento/guindastes?terminal_id=${terminalId}`);
                if (!res.ok) return;
                const guindastes = await res.json();
                if (!Array.isArray(guindastes)) return;
                guindasteSelect.innerHTML = '<option value="">Selecione um guindaste</option>';
                guindastes.forEach(g => {
                    const og = document.createElement('option');
                    og.value = g.id;
                    og.textContent = g.nome;
                    guindasteSelect.appendChild(og);
                });
                guindasteSelect.disabled = guindasteSelect.options.length <= 1;
            }

            async function populateDependentFields(opt) {
                const bercos = JSON.parse(opt.getAttribute('data-bercos') || '[]');
                const guindastes = JSON.parse(opt.getAttribute('data-guindastes') || '[]');
                
                bercoSelect.innerHTML = '<option value="">Selecione um berço</option>';
                bercos.forEach(b => {
                    const o = document.createElement('option');
                    o.value = b;
                    o.textContent = b;
                    bercoSelect.appendChild(o);
                });
                bercoSelect.disabled = bercos.length === 0;

                if (guindastes.length === 0) {
                    await carregarGuindastesPorTerminal(opt.value);
                } else {
                    guindasteSelect.innerHTML = '<option value="">Selecione um guindaste</option>';
                    guindastes.forEach(g => {
                        const og = document.createElement('option');
                        og.value = g.id;
                        og.textContent = g.nome;
                        guindasteSelect.appendChild(og);
                    });
                    guindasteSelect.disabled = false;
                }
            }

            embarcacaoSelect.addEventListener('change', async () => {
                const id = embarcacaoSelect.value;
                if (!id) { return; }
                await carregarEmbarcacaoInfo(id);
            });

            etaInput.addEventListener('change', carregarTerminaisDisponiveis);
            etdInput.addEventListener('change', carregarTerminaisDisponiveis);
            if (dataProgramadaInput) {
                dataProgramadaInput.addEventListener('change', carregarTerminaisDisponiveis);
            }

            terminalSelect.addEventListener('change', async () => {
                const opt = terminalSelect.selectedOptions[0];
                if (!opt) {
                    bercoSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>'; bercoSelect.disabled = true;
                    guindasteSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>'; guindasteSelect.disabled = true;
                    return;
                }
                await populateDependentFields(opt);
            });
            
            if (embarcacaoSelect.value) {
                await carregarEmbarcacaoInfo(embarcacaoSelect.value);
                
                let terminaisCarregados = false;
                if ((etaInput.value && etdInput.value) || (dataProgramadaInput && dataProgramadaInput.value)) {
                    await carregarTerminaisDisponiveis();
                    terminaisCarregados = true;
                }
                
                if (terminalAtual) {
                    if (terminaisCarregados) {
                        terminalSelect.value = terminalAtual;
                        const opt = terminalSelect.selectedOptions[0];
                        if (opt) {
                            await populateDependentFields(opt);
                            if (bercoAtual) bercoSelect.value = bercoAtual;
                            if (guindasteAtual) guindasteSelect.value = guindasteAtual;
                        }
                    } else {
                        // Fallback: load cranes if terminals were not refreshed
                        await carregarGuindastesPorTerminal(terminalAtual);
                        if (guindasteAtual) guindasteSelect.value = guindasteAtual;
                    }
                }
            }

        });
    </script>
</x-app-layout>