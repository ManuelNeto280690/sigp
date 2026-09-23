<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-file-contract text-white text-xs"></i>
                    </div>
                    Editar Contrato
                </h2>
                <p class="text-gray-600 text-xs mt-1">Atualize parâmetros do contrato</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="post" action="{{ route('contratos.update',$contrato) }}" class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden p-6">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Concessionária</label>
                        <select name="concessionaria_id" class="w-full border rounded p-2" required>
                            @foreach($concessionarias as $con)
                                <option value="{{ $con->id }}" @selected($contrato->concessionaria_id === $con->id)>{{ $con->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Tipo de Contrato</label>
                        <select name="titulo" class="w-full border rounded p-2" required>
                            <option value="Tarifas aplicadas ao NAVIO" @selected($contrato->titulo==='Tarifas aplicadas ao NAVIO')>Tarifas aplicadas ao NAVIO</option>
                            <option value="Tarifas aplicadas à CARGA" @selected($contrato->titulo==='Tarifas aplicadas à CARGA')>Tarifas aplicadas à CARGA</option>
                            <option value="Tarifas aplicadas à OPERAÇÃO" @selected($contrato->titulo==='Tarifas aplicadas à OPERAÇÃO')>Tarifas aplicadas à OPERAÇÃO</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Data Início</label>
                        <input type="date" name="data_inicio" value="{{ optional($contrato->data_inicio)->format('Y-m-d') }}" class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Data Fim</label>
                        <input type="date" name="data_fim" value="{{ optional($contrato->data_fim)->format('Y-m-d') }}" class="w-full border rounded p-2" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Modo de Faturação</label>
                        <select name="modo_faturacao" class="w-full border rounded p-2" required>
                            <option value="por_operacao" @selected($contrato->modo_faturacao==='por_operacao')>Por operação</option>
                            <option value="consolidado_por_navio" @selected($contrato->modo_faturacao==='consolidado_por_navio')>Consolidado por navio</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Status</label>
                        <select name="status" class="w-full border rounded p-2" required>
                            <option value="ativo" @selected($contrato->status==='ativo')>Ativo</option>
                            <option value="inativo" @selected($contrato->status==='inativo')>Inativo</option>
                            <option value="cancelado" @selected($contrato->status==='cancelado')>Cancelado</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Observações</label>
                        <textarea name="observacoes" class="w-full border rounded p-2" rows="4">{{ $contrato->observacoes }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
                    <a href="{{ route('contratos.index') }}" class="ml-2 px-4 py-2 bg-gray-100 rounded">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>