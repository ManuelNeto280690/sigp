<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Novo Movimento de Embarcação') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Registre um novo movimento de entrada ou saída</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('entrada-saida-embarcacao.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensagens de sucesso -->
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

            <!-- Mensagens de erro -->
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
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-plus-circle mr-2" style="color: #0084de;"></i>
                        Dados do Movimento
                    </h3>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('entrada-saida-embarcacao.store') }}" enctype="multipart/form-data" id="formMovimento">
                        @csrf

                        <div class="mb-6">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-clock mr-2 text-blue-600"></i>
                                Informações Iniciais (Em Espera)
                            </h4>
                        </div>

                        <!-- Embarcação -->
                        <div class="mb-6">
                            <label for="embarcacao_id" class="block text-sm font-medium text-gray-700 mb-2">Embarcação *</label>
                            <select name="embarcacao_id" id="embarcacao_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Selecione uma embarcação</option>
                                @foreach($embarcacoes as $embarcacao)
                                    <option value="{{ $embarcacao->id }}" {{ old('embarcacao_id') == $embarcacao->id ? 'selected' : '' }}>
                                        {{ $embarcacao->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ETA e ETD -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="eta" class="block text-sm font-medium text-gray-700 mb-2">ETA - Chegada Estimada *</label>
                                <input type="datetime-local" name="eta" id="eta" value="{{ old('eta') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled required>
                            </div>
                            <div>
                                <label for="etd" class="block text-sm font-medium text-gray-700 mb-2">ETD - Saída Estimada *</label>
                                <input type="datetime-local" name="etd" id="etd" value="{{ old('etd') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled required>
                            </div>
                        </div>

                        <!-- Terminal -->
                        <div class="mb-6">
                            <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">Terminal *</label>
                            <select name="terminal_id" id="terminal_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Selecione um terminal</option>
                                @foreach($terminais as $terminal)
                                    <option value="{{ $terminal->id }}" 
                                        data-bercos='{{ json_encode($terminal->bercos->pluck("nome")) }}' 
                                        data-guindastes='{{ json_encode($terminal->guindastes->map(fn($g) => ["id" => $g->id, "nome" => $g->nome])) }}'
                                        {{ old('terminal_id') == $terminal->id ? 'selected' : '' }}>
                                        {{ $terminal->nome }} - {{ $terminal->codigo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Berço -->
                        <div class="mb-6">
                            <label for="berco" class="block text-sm font-medium text-gray-700 mb-2">Berço *</label>
                            <select name="berco" id="berco" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled required>
                                <option value="">Selecione um terminal primeiro</option>
                            </select>
                        </div>

                        <!-- Guindaste -->
                        <div class="mb-6">
                            <label for="guindaste_id" class="block text-sm font-medium text-gray-700 mb-2">Guindaste</label>
                            <select name="guindaste_id" id="guindaste_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                                <option value="">Selecione um terminal primeiro</option>
                            </select>
                        </div>

                        <!-- Tipo Movimento -->
                        <div class="mb-6">
                            <label for="tipo_movimento" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimento *</label>
                            <select name="tipo_movimento" id="tipo_movimento" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Selecione o tipo</option>
                                <option value="entrada" {{ old('tipo_movimento') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                <option value="saida" {{ old('tipo_movimento') == 'saida' ? 'selected' : '' }}>Saída</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="programado" {{ old('status', 'programado') == 'programado' ? 'selected' : '' }}>Programado</option>
                                <option value="autorizado" {{ old('status') == 'autorizado' ? 'selected' : '' }}>Autorizado</option>
                                <option value="em_andamento" {{ old('status') == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                <option value="concluido" {{ old('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                                <option value="cancelado" {{ old('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>

                        <div class="mt-8 mb-4">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-anchor mr-2 text-green-600"></i>
                                Atracado
                            </h4>
                        </div>
                        <div class="mb-6">
                            <label for="estado_embarcacao" class="block text-sm font-medium text-gray-700 mb-2">Estado da Embarcação</label>
                            <select name="estado_embarcacao" id="estado_embarcacao" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione o estado</option>
                                <option value="esperado" {{ old('estado_embarcacao') == 'esperado' ? 'selected' : '' }}>Esperado</option>
                                <option value="atracado" {{ old('estado_embarcacao') == 'atracado' ? 'selected' : '' }}>Atracado</option>
                                <option value="operando" {{ old('estado_embarcacao') == 'operando' ? 'selected' : '' }}>Operando</option>
                                <option value="partido" {{ old('estado_embarcacao') == 'partido' ? 'selected' : '' }}>Partido</option>
                            </select>
                        </div>

                        <!-- Agente Marítimo -->
                        <div class="mb-6">
                            <label for="agente_maritimo" class="block text-sm font-medium text-gray-700 mb-2">Agente Marítimo</label>
                            <input type="text" name="agente_maritimo" id="agente_maritimo" value="{{ old('agente_maritimo') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nome do agente marítimo">
                        </div>

                        <!-- Portos Origem e Destino -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="porto_origem" class="block text-sm font-medium text-gray-700 mb-2">Porto de Origem</label>
                                <input type="text" name="porto_origem" id="porto_origem" value="{{ old('porto_origem') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Rio de Janeiro">
                            </div>
                            <div>
                                <label for="porto_destino" class="block text-sm font-medium text-gray-700 mb-2">Porto de Destino</label>
                                <input type="text" name="porto_destino" id="porto_destino" value="{{ old('porto_destino') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Santos">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-6">
                            <div>
                                <label for="ata" class="block text-sm font-medium text-gray-700 mb-2">ATA - Chegada Real</label>
                                <input type="datetime-local" name="ata" id="ata" value="{{ old('ata') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div class="mb-6">
                            <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">Motivo</label>
                            <textarea name="motivo" id="motivo" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Motivo do movimento...">{{ old('motivo') }}</textarea>
                          </div>

                        <div class="mt-8 mb-4">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-arrow-right mr-2 text-red-600"></i>
                                Saída
                            </h4>
                        </div>
                        <div class="mb-6">
                            <label for="atd" class="block text-sm font-medium text-gray-700 mb-2">ATD - Saída Real</label>
                            <input type="datetime-local" name="atd" id="atd" value="{{ old('atd') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Observações -->
                        <div class="mb-6">
                            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea name="observacoes" id="observacoes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Informações adicionais sobre o movimento...">{{ old('observacoes') }}</textarea>
                        </div>

                        <!-- Documentos -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Documentos</label>
                            <div id="documentos-container" class="space-y-3">
                                <div class="documento-item flex items-center space-x-3">
                                    <input type="file" name="documentos[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <button type="button" onclick="adicionarDocumento()" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-md text-sm">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Formatos aceitos: PDF, DOC, DOCX, JPG, JPEG, PNG</p>
                        </div>

                        <!-- Botões -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('entrada-saida-embarcacao.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                <i class="fas fa-times mr-1"></i>Cancelar
                            </a>
                            <button type="submit" class="text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow-lg hover:shadow-xl" style="background: #0084de;">
                                <i class="fas fa-save mr-1"></i>Salvar Movimento
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
                <input type="file" name="documentos[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <button type="button" onclick="removerDocumento(this)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(novoItem);
        }

        function removerDocumento(button) {
            button.parentElement.remove();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const embarcacaoSelect = document.getElementById('embarcacao_id');
            const etaInput = document.getElementById('eta');
            const etdInput = document.getElementById('etd');
            const terminalSelect = document.getElementById('terminal_id');
            const bercoSelect = document.getElementById('berco');
            const guindasteSelect = document.getElementById('guindaste_id');

            let tipoEmbarcacao = null;
            let caladoNavio = null;

            // Bloqueia campos no início
            resetCampos();

            embarcacaoSelect.addEventListener('change', async () => {
                resetCampos(false); // mantém navio selecionado e libera ETA/ETD
                const embarcacaoId = embarcacaoSelect.value;
                if (!embarcacaoId) return;

                try {
                    const res = await fetch(`/planejamento/embarcacao/${embarcacaoId}`);
                    if (!res.ok) throw new Error('Erro ao buscar dados do navio');
                    const data = await res.json();

                    tipoEmbarcacao = data.tipo_embarcacao;
                    caladoNavio = data.calado;

                    etaInput.disabled = false;
                    etdInput.disabled = false;
                } catch (error) {
                    alert(error.message);
                }
            });

            // etaInput.addEventListener('change', carregarTerminaisDisponiveis);
            // etdInput.addEventListener('change', carregarTerminaisDisponiveis);

            terminalSelect.addEventListener('change', function () {
                const option = this.options[this.selectedIndex];
                if (!option || !option.value) {
                    bercoSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                    bercoSelect.disabled = true;
                    guindasteSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                    guindasteSelect.disabled = true;
                    return;
                }

                const bercos = JSON.parse(option.getAttribute('data-bercos') || '[]');
                const guindastes = JSON.parse(option.getAttribute('data-guindastes') || '[]');

                if (bercos.length === 0) {
                    bercoSelect.innerHTML = '<option value="">Sem berços disponíveis</option>';
                    bercoSelect.disabled = true;
                } else {
                    bercoSelect.innerHTML = bercos.map(b => `<option value="${b}">${b}</option>`).join('');
                    bercoSelect.disabled = false;
                }

                if (guindastes.length === 0) {
                    guindasteSelect.innerHTML = '<option value="">Sem guindastes disponíveis</option>';
                    guindasteSelect.disabled = true;
                } else {
                    guindasteSelect.innerHTML = '<option value="">Selecione um guindaste</option>' + guindastes.map(g => `<option value="${g.id}">${g.nome}</option>`).join('');
                    guindasteSelect.disabled = false;
                }
            });

            async function carregarTerminaisDisponiveis() {
                const eta = etaInput.value;
                const etd = etdInput.value;

                if (!tipoEmbarcacao || !caladoNavio || !eta || !etd) {
                    terminalSelect.innerHTML = '<option value="">Selecione uma embarcação e preencha as datas primeiro</option>';
                    terminalSelect.disabled = true;
                    bercoSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                    bercoSelect.disabled = true;
                    return;
                }

                try {
                    const query = new URLSearchParams({
                        tipo_embarcacao: tipoEmbarcacao,
                        calado: caladoNavio,
                        data_entrada: eta,
                        data_saida: etd,
                    });

                    const res = await fetch(`/planejamento/terminais-disponiveis?${query.toString()}`);
                    if (!res.ok) throw new Error('Erro ao buscar terminais disponíveis');

                    const terminais = await res.json();

                    if (terminais.length === 0) {
                        terminalSelect.innerHTML = '<option value="">Nenhum terminal disponível</option>';
                        terminalSelect.disabled = true;
                        bercoSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                        bercoSelect.disabled = true;
                        return;
                    }

                    terminalSelect.innerHTML = '<option value="">Selecione um terminal</option>';
                    terminais.forEach(t => {
                        terminalSelect.innerHTML += `<option value="${t.id}" data-bercos='${JSON.stringify(t.bercos_livres)}' data-guindastes='${JSON.stringify(t.guindastes_disponiveis)}'>${t.nome} - ${t.codigo}</option>`;
                    });
                    terminalSelect.disabled = false;
                    bercoSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                    bercoSelect.disabled = true;
                    guindasteSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                    guindasteSelect.disabled = true;
                } catch (error) {
                    alert(error.message);
                }
            }

            function resetCampos(limparEmbarcacao = true) {
                if (limparEmbarcacao) embarcacaoSelect.value = '';
                etaInput.value = '';
                etdInput.value = '';
                etaInput.disabled = true;
                etdInput.disabled = true;
                // terminalSelect.innerHTML = '<option value="">Selecione uma embarcação e preencha as datas primeiro</option>';
                // terminalSelect.disabled = true;
                bercoSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                bercoSelect.disabled = true;
                guindasteSelect.innerHTML = '<option value="">Selecione um terminal primeiro</option>';
                guindasteSelect.disabled = true;
            }
        });
    </script>

</x-app-layout>
