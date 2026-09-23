<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-file-contract text-white text-xs"></i>
                    </div>
                    Contrato
                </h2>
                <p class="text-gray-600 text-xs mt-1">Detalhes do contrato</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('contratos.edit',$contrato) }}" class="px-4 py-2 bg-gray-100 rounded">Editar</a>
                <form method="post" action="{{ route('contratos.destroy',$contrato) }}" class="inline">
                    @csrf @method('DELETE')
                    <button class="px-4 py-2 bg-red-600 text-white rounded">Excluir</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4" style="font-size: 14px;">
                <x-stat-card title="Status" :value="ucfirst($contrato->status)" color="green" icon="fas fa-file-contract" class="text-xs" />
                <x-stat-card title="Modo" :value="$contrato->modo_faturacao" color="blue" icon="fas fa-cogs" class="text-xs" />
                <x-stat-card title="Vigência" :value="($contrato->data_inicio ? $contrato->data_inicio->format('d/m/Y') : 'N/A') . ' - ' . ($contrato->data_fim ? $contrato->data_fim->format('d/m/Y') : 'N/A')" color="purple" icon="fas fa-calendar" class="text-xs" />
                <x-stat-card title="Atualizada" :value="$contrato->updated_at->locale('pt_BR')->diffForHumans()" color="gray" icon="fas fa-clock" class="text-xs" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="space-y-4 lg:col-span-1">
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>Informações do Contrato
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Concessionária</span><span class="font-medium">{{ $contrato->concessionaria->nome ?? 'N/A' }}</span></div>
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Título</span><span class="font-medium">{{ $contrato->titulo }}</span></div>
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Modo</span><span class="font-medium">{{ $contrato->modo_faturacao }}</span></div>
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Status</span><span class="font-medium">{{ $contrato->status }}</span></div>
                            <div class="flex justify-between py-1"><span class="text-gray-500">Observações</span><span class="font-medium">{{ $contrato->observacoes ?: '—' }}</span></div>
                        </div>
                    </x-card>
                </div>

                <div class="space-y-4 lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Ações Rápidas</h3></div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <a href="{{ route('contratos.edit',$contrato) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200"><i class="fas fa-edit mr-2"></i>Editar</a>
                                <a href="{{ route('contratos.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200"><i class="fas fa-arrow-left mr-2"></i>Voltar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-4">
             <div class="space-y-4 lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center"><i class="fas fa-tags mr-2 text-green-600"></i>Tarifas</h3>
                        </div>
                        <div class="p-6">
                            <form method="post" action="{{ route('contratos.tarifas.store',$contrato) }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                    <select name="tipo" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required>
                                        <option value="fixa">Fixa</option>
                                        <option value="variavel">Variável</option>
                                        <option value="penalidade">Penalidade</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Descrição</label>
                                    <input name="descricao" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Evento</label>
                                    @php
                                        $eventoOptions = $contrato->getEventoOptions();
                                        $tituloLower = mb_strtolower($contrato->titulo);
                                        // Fix: Garante Guindaste para contratos de operação/equipamento/guindaste (incluindo typos comuns)
                                        if ((str_contains($tituloLower, 'opera') || str_contains($tituloLower, 'opra') || str_contains($tituloLower, 'equipamento') || str_contains($tituloLower, 'guindaste')) && !in_array('Guindaste', $eventoOptions)) {
                                            $eventoOptions[] = 'Guindaste';
                                        }
                                    @endphp
                                    @if(!empty($eventoOptions))
                                        <select name="evento_disparo" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required>
                                            @foreach($eventoOptions as $opt)
                                                <option value="{{ $opt }}" @selected(old('evento_disparo')===$opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input name="evento_disparo" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" placeholder="entrada, saída, carga..." required />
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Valor Fixo</label>
                                    <input type="number" step="0.01" name="valor_fixo" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Valor Unitário</label>
                                    <input type="number" step="0.01" name="valor_unitario" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Unidade</label>
                                    <select name="unidade" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required>
                                        <option value="Naoaplicavel" @selected(old('unidade')==='Naoaplicavel')>Não aplicavel</option>
                                        <option value="Navio" @selected(old('unidade')==='Navio')>Navio</option>
                                        <option value="Hora" @selected(old('unidade')==='Hora')>Hora</option>
                                        <option value="Tonelada" @selected(old('unidade')==='Tonelada')>Tonelada</option>
                                        <option value="Contêiner (20)" @selected(old('unidade')==='Contêiner (20)')>Contêiner (20)</option>
                                        <option value="Contêiner (40)" @selected(old('unidade')==='Contêiner (40)')>Contêiner (40)</option>
                                        <option value="Unidade" @selected(old('unidade')==='Unidade')>Unidade</option>
                                        <option value="Dia" @selected(old('unidade')==='Dia')>Dia</option>
                                        <option value="m³" @selected(old('unidade')==='m³')>m³</option>
                                        <option value="kg" @selected(old('unidade')==='kg')>kg</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button class="px-3 py-2 bg-green-600 text-white rounded text-xs">Adicionar</button>
                                </div>
                            </form>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-xs">
                                    <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th></tr></thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($contrato->tarifas as $t)
                                        <tr>
                                            <td class="px-3 py-2">{{ $t->descricao }}</td>
                                            <td class="px-3 py-2">{{ $t->evento_disparo }}</td>
                                            <td class="px-3 py-2">{{ ucfirst($t->tipo) }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format($t->valor_unitario ?? $t->valor_fixo, 2, ',', '.') }} {{ $t->unidade ? '/ '.$t->unidade : '' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                <button type="button" class="text-blue-600 mr-2" title="Editar"
                                                        onclick="document.getElementById('edit-tarifa-{{ $t->id }}').classList.toggle('hidden')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="post" action="{{ route('contratos.tarifas.destroy', [$contrato, $t]) }}" class="inline" onsubmit="return confirm('Remover esta tarifa?');">
                                                    @csrf @method('DELETE')
                                                    <button class="text-red-600" title="Excluir"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        <tr id="edit-tarifa-{{ $t->id }}" class="hidden">
                                            <td colspan="5" class="bg-gray-50 px-3 py-3">
                                                <form method="post" action="{{ route('contratos.tarifas.update', [$contrato, $t]) }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                                                    @csrf @method('PUT')
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                                        <select name="tipo" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required>
                                                            <option value="fixa" @selected($t->tipo==='fixa')>Fixa</option>
                                                            <option value="variavel" @selected($t->tipo==='variavel')>Variável</option>
                                                            <option value="penalidade" @selected($t->tipo==='penalidade')>Penalidade</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Descrição</label>
                                                        <input name="descricao" value="{{ $t->descricao }}" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Evento</label>
                                                        @php
                                                            $eventoOptions = $contrato->getEventoOptions();
                                                            $tituloLower = mb_strtolower($contrato->titulo);
                                                            if ((str_contains($tituloLower, 'opera') || str_contains($tituloLower, 'opra') || str_contains($tituloLower, 'equipamento') || str_contains($tituloLower, 'guindaste')) && !in_array('Guindaste', $eventoOptions)) {
                                                                $eventoOptions[] = 'Guindaste';
                                                            }
                                                        @endphp
                                                        @if(!empty($eventoOptions))
                                                            <select name="evento_disparo" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required>
                                                                @foreach($eventoOptions as $opt)
                                                                    <option value="{{ $opt }}" @selected($t->evento_disparo===$opt)>{{ $opt }}</option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <input name="evento_disparo" value="{{ $t->evento_disparo }}" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required />
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Valor Fixo</label>
                                                        <input type="number" step="0.01" name="valor_fixo" value="{{ $t->valor_fixo }}" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Valor Unitário</label>
                                                        <input type="number" step="0.01" name="valor_unitario" value="{{ $t->valor_unitario }}" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unidade</label>
                                                        <select name="unidade" class="w-full py-2 px-3 border border-gray-300 rounded-md text-xs" required>
                                                            <option value="Navio" @selected($t->unidade==='Navio')>Navio</option>
                                                            <option value="Hora" @selected($t->unidade==='Hora')>Hora</option>
                                                            <option value="Tonelada" @selected($t->unidade==='Tonelada')>Tonelada</option>
                                                            <option value="Contêiner (20)" @selected($t->unidade==='Contêiner (20)')>Contêiner (20)</option>
                                                            <option value="Contêiner (40)" @selected($t->unidade==='Contêiner (40)')>Contêiner (40)</option>
                                                            @if($t->unidade === 'Contêiner')
                                                                <option value="Contêiner" selected>Contêiner (Legado)</option>
                                                            @endif
                                                            <option value="Unidade" @selected($t->unidade==='Unidade')>Unidade</option>
                                                            <option value="Dia" @selected($t->unidade==='Dia')>Dia</option>
                                                            <option value="m³" @selected($t->unidade==='m³')>m³</option>
                                                            <option value="kg" @selected($t->unidade==='kg')>kg</option>
                                                        </select>
                                                    </div>
                                                    <div class="flex items-end">
                                                        <button class="px-3 py-2 bg-blue-600 text-white rounded text-xs">Salvar</button>
                                                        <button type="button" class="ml-2 px-3 py-2 bg-gray-200 text-gray-800 rounded text-xs"
                                                                onclick="document.getElementById('edit-tarifa-{{ $t->id }}').classList.add('hidden')">Cancelar</button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="px-3 py-2 text-xs text-gray-500">Nenhuma tarifa cadastrada.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
      ~   </div>

        </div>     
    </div>

   
</x-app-layout>