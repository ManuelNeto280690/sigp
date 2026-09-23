<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-boxes text-white text-xs"></i>
                    </div>
                    Novo Movimento de Carga
                </h2>
                <p class="text-gray-600 text-xs mt-1">Registre uma operação de carga/descarga</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="post" action="{{ route('movimentos-carga.store') }}" class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Movimento Entrada/Saída</label>
                        <input type="text" id="entradaSaidaSearch" placeholder="Pesquisar..." class="w-full border rounded p-2 mb-2" />
                        <select id="entradaSaidaSelect" name="entrada_saida_id" class="w-full border rounded p-2" required>
                            <option value="">Selecione</option>
                            @foreach($entradaSaidas as $es)
                                <option value="{{ $es->id }}">{{ $es->numero_movimento ?? $es->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Terminal</label>
                        <select id="terminalSelect" name="terminal_id" class="w-full border rounded p-2" required>
                            <option value="">Selecione</option>
                            @foreach($terminais as $t)
                                <option value="{{ $t->id }}">{{ $t->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Berço</label>
                        <select id="bercoSelect" name="berco_id" class="w-full border rounded p-2" required>
                            <option value="">Selecione</option>
                            @foreach($bercos as $b)
                                <option value="{{ $b->id }}" data-terminal="{{ $b->terminal_id }}">{{ $b->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Guindaste</label>
                        <select id="guindasteSelect" name="guindaste_id" class="w-full border rounded p-2" required>
                            <option value="">Selecione</option>
                            @foreach($guindastes as $g)
                                <option value="{{ $g->id }}" data-terminal="{{ $g->terminal_id ?? '' }}">{{ $g->nome }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="aplicar_tarifa_operacao" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                <span class="ml-2 text-sm text-gray-600">Aplicar Tarifa de Operação (Uso de Equipamento)</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Tipo de Operação</label>
                        <select name="tipo_operacao" class="w-full border rounded p-2" required>
                            <option value="carga">Carga</option>
                            <option value="descarga">Descarga</option>
                            <option value="movimentacao_interna">Movimentação interna</option>
                            <option value="transbordo">Transbordo</option>
                            <option value="pesagem">Pesagem</option>
                            <option value="inspecao">Inspeção</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Tipo de Produto</label>
                        <select name="tipo_produto" class="w-full border rounded p-2" required>
                            <option value="Container">Container</option>
                            <option value="Granéis Sólidos">Granéis Sólidos</option>
                            <option value="Granéis Líquidos">Granéis Líquidos</option>
                            <option value="Carga Geral Solta">Carga Geral Solta</option>
                            <option value="Ro-Ro">Ro-Ro</option>
                            <option value="Animais vivos">Animais vivos</option>
                        </select>
</div>
                    <div>
                        <label class="block text-sm mb-1">Unidade</label>
                        <select name="unidade" class="w-full border rounded p-2" required>
                            <option value="Tonelada">Tonelada</option>
                            <option value="Contêiner (20)">Contêiner (20)</option>
                            <option value="Contêiner (40)">Contêiner (40)</option>
                            <option value="Unidade">Unidade</option>
                            <option value="m³">m³</option>
                            <option value="kg">kg</option>
                            <option value="Hora">Hora</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Quantidade</label>
                        <input type="number" step="0.0001" name="quantidade" class="w-full border rounded p-2" required />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Início</label>
                        <input type="datetime-local" name="inicio" class="w-full border rounded p-2" required />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Fim</label>
                        <input type="datetime-local" name="fim" class="w-full border rounded p-2" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Observações</label>
                        <textarea name="observacoes" class="w-full border rounded p-2" rows="4"></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
                    <a href="{{ route('movimentos-carga.index') }}" class="ml-2 px-4 py-2 bg-gray-100 rounded">Cancelar</a>
                </div>
            <script>
                (function(){
                    const entradaSaidaSelect = document.getElementById('entradaSaidaSelect');
                    const entradaSaidaSearch = document.getElementById('entradaSaidaSearch');
                    const terminalSelect = document.getElementById('terminalSelect');
                    const bercoSelect = document.getElementById('bercoSelect');
                    const guindasteSelect = document.getElementById('guindasteSelect');

                    entradaSaidaSearch.addEventListener('input', function(){
                        const term = this.value.toLowerCase();
                        Array.from(entradaSaidaSelect.options).forEach(opt => {
                            if (!opt.value) return; 
                            const txt = opt.text.toLowerCase();
                            opt.style.display = txt.includes(term) ? '' : 'none';
                        });
                    });

                    entradaSaidaSelect.addEventListener('change', function(){
                        const id = this.value;
                        if (!id) return;
                        fetch(`{{ route('movimentos-carga.contexto', ['entradaSaida' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER', id))
                            .then(r => r.json())
                            .then(data => {
                                terminalSelect.value = data.terminal_id || '';
                                bercoSelect.innerHTML = '<option value="">Selecione</option>';
                                (data.bercos || []).forEach(b => {
                                    const o = document.createElement('option');
                                    o.value = b.id; o.textContent = b.nome; bercoSelect.appendChild(o);
                                });
                                guindasteSelect.innerHTML = '<option value="">Selecione</option>';
                                (data.guindastes || []).forEach(g => {
                                    const o = document.createElement('option');
                                    o.value = g.id; o.textContent = g.nome; guindasteSelect.appendChild(o);
                                });
                                if (data.berco_id) { bercoSelect.value = data.berco_id; }
                                if (data.guindaste_id) { guindasteSelect.value = data.guindaste_id; }
                            })
                            .catch(() => {});
                    });
                })();
            </script>
            </form>
        </div>
    </div>
</x-app-layout>