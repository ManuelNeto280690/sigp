<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-boxes text-white text-xs"></i>
                    </div>
                    Editar Movimento de Carga
                </h2>
                <p class="text-gray-600 text-xs mt-1">Atualize dados do movimento</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="post" action="{{ route('movimentos-carga.update',$movimento) }}" class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden p-6">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Movimento Entrada/Saída</label>
                        <select name="entrada_saida_id" class="w-full border rounded p-2" required>
                            @foreach($entradaSaidas as $es)
                                <option value="{{ $es->id }}" @selected($movimento->entrada_saida_id===$es->id)>{{ $es->numero_movimento ?? $es->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Terminal</label>
                        <select name="terminal_id" class="w-full border rounded p-2" required>
                            @foreach($terminais as $t)
                                <option value="{{ $t->id }}" @selected($movimento->terminal_id===$t->id)>{{ $t->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Berço</label>
                        <select name="berco_id" class="w-full border rounded p-2" required>
                            @foreach($bercos as $b)
                                <option value="{{ $b->id }}" @selected($movimento->berco_id===$b->id)>{{ $b->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Guindaste</label>
                        <select name="guindaste_id" class="w-full border rounded p-2" required>
                            @foreach($guindastes as $g)
                                <option value="{{ $g->id }}" @selected($movimento->guindaste_id===$g->id)>{{ $g->nome }}</option>
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
                            <option value="carga" @selected($movimento->tipo_operacao==='carga')>Carga</option>
                            <option value="descarga" @selected($movimento->tipo_operacao==='descarga')>Descarga</option>
                            <option value="movimentacao_interna" @selected($movimento->tipo_operacao==='movimentacao_interna')>Movimentação interna</option>
                            <option value="transbordo" @selected($movimento->tipo_operacao==='transbordo')>Transbordo</option>
                            <option value="pesagem" @selected($movimento->tipo_operacao==='pesagem')>Pesagem</option>
                            <option value="inspecao" @selected($movimento->tipo_operacao==='inspecao')>Inspeção</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Tipo de Produto</label>
                        <select name="tipo_produto" class="w-full border rounded p-2" required>
                            <option value="Container" @selected($movimento->tipo_produto==='Container')>Container</option>
                            <option value="Granéis Sólidos" @selected($movimento->tipo_produto==='Granéis Sólidos')>Granéis Sólidos</option>
                            <option value="Granéis Líquidos" @selected($movimento->tipo_produto==='Granéis Líquidos')>Granéis Líquidos</option>
                            <option value="Carga Geral Solta" @selected($movimento->tipo_produto==='Carga Geral Solta')>Carga Geral Solta</option>
                            <option value="Ro-Ro" @selected($movimento->tipo_produto==='Ro-Ro')>Ro-Ro</option>
                            <option value="Animais vivos" @selected($movimento->tipo_produto==='Animais vivos')>Animais vivos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Unidade</label>
                        <select name="unidade" class="w-full border rounded p-2" required>
                            <option value="Tonelada" @selected($movimento->unidade==='Tonelada')>Tonelada</option>
                            <option value="Contêiner (20)" @selected($movimento->unidade==='Contêiner (20)')>Contêiner (20)</option>
                            <option value="Contêiner (40)" @selected($movimento->unidade==='Contêiner (40)')>Contêiner (40)</option>
                            @if($movimento->unidade === 'Contêiner')
                                <option value="Contêiner" selected>Contêiner (Legado)</option>
                            @endif
                            <option value="Unidade" @selected($movimento->unidade==='Unidade')>Unidade</option>
                            <option value="m³" @selected($movimento->unidade==='m³')>m³</option>
                            <option value="kg" @selected($movimento->unidade==='kg')>kg</option>
                            <option value="Hora" @selected($movimento->unidade==='Hora')>Hora</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Quantidade</label>
                        <input type="number" step="0.0001" name="quantidade" value="{{ $movimento->quantidade }}" class="w-full border rounded p-2" required />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Início</label>
                        <input type="datetime-local" name="inicio" value="{{ \Carbon\Carbon::parse($movimento->inicio)->format('Y-m-d\\TH:i') }}" class="w-full border rounded p-2" required />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Fim</label>
                        <input type="datetime-local" name="fim" value="{{ $movimento->fim ? \Carbon\Carbon::parse($movimento->fim)->format('Y-m-d\\TH:i') : '' }}" class="w-full border rounded p-2" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Observações</label>
                        <textarea name="observacoes" class="w-full border rounded p-2" rows="4">{{ $movimento->observacoes }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
                    <a href="{{ route('movimentos-carga.index') }}" class="ml-2 px-4 py-2 bg-gray-100 rounded">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>