<x-app-layout>
    <x-slot name="header">
        <!-- Layout ignora o header dinâmico, o conteúdo foi movido para dentro da py-6 -->
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 text-lg"></i>
                        <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Cabeçalho da Página -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                            <i class="fas fa-cogs text-white text-sm"></i>
                        </div>
                        Configurações de Faturação
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">Gerencie a série atual, moeda base e as taxas de imposto aplicáveis</p>
                </div>
            </div>

            <!-- Configurações Gerais de Faturação -->
            <x-card class="overflow-hidden shadow-sm">
                <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Definições Gerais</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('faturacao.salvar-configuracao') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Moeda Base (Ex: AOA, USD)</label>
                            <input type="text" name="moeda" value="{{ old('moeda', $moeda) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Série de Faturação (Ex: 2026)</label>
                            <input type="text" name="serie" value="{{ old('serie', $serie) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                            <p class="text-xs text-gray-500 mt-1">Este valor será usado como série para novas faturas. Altere no início de cada ano.</p>
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-save mr-2"></i>Guardar Definições
                            </button>
                        </div>
                    </form>
                </div>
            </x-card>

            <!-- Tabela de Impostos -->
            <x-card class="overflow-hidden shadow-sm">
                <div class="bg-gray-50 px-6 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Taxas de Imposto</h3>
                    <button type="button" onclick="document.getElementById('modal-create').classList.remove('hidden')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow transition-colors text-xs font-medium flex items-center">
                        <i class="fas fa-plus mr-1"></i>Novo Imposto
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código / Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxa (%)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Isenção (Código - Motivo)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($impostos as $imposto)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-md text-xs font-bold">{{ $imposto->tipo }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $imposto->sigla }}</div>
                                        <div class="text-xs text-gray-500">{{ $imposto->nome }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                        {{ number_format($imposto->taxa, 2, ',', '.') }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($imposto->taxa == 0 && $imposto->motivo_isencao_codigo)
                                            <div class="text-sm text-gray-900">{{ $imposto->motivo_isencao_codigo }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($imposto->motivo_isencao_descricao, 30) }}</div>
                                        @else
                                            <span class="text-gray-400 text-xs">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($imposto->ativo)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="editImposto({{ $imposto->toJson() }})" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                                        <form method="POST" action="{{ route('impostos.destroy', $imposto) }}" class="inline" onsubmit="return confirm('Tem certeza?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhum imposto configurado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Modal Novo Imposto -->
    <div id="modal-create" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-create').classList.add('hidden')"></div>
            <div class="relative w-full max-w-lg bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all z-10 border border-gray-100">
                <form action="{{ route('impostos.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Adicionar Novo Imposto</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Imposto</label>
                                <select name="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                                    <option value="IVA">IVA</option>
                                    <option value="IS">Imposto de Selo</option>
                                    <option value="IRT">Retenção na Fonte (IRT/II)</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Código AGT</label>
                                    <input type="text" name="sigla" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Ex: NOR, ISE" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Taxa (%)</label>
                                    <input type="number" step="0.01" name="taxa" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Ex: 14" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <input type="text" name="nome" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Ex: Taxa Normal" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cód. Isenção (se 0%)</label>
                                    <input type="text" name="motivo_isencao_codigo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Ex: M10">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Motivo Isenção</label>
                                    <input type="text" name="motivo_isencao_descricao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" placeholder="Ex: Isento nos termos...">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="ativo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Gravar</button>
                        <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Imposto (Simplificado via script) -->
    <div id="modal-edit" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-edit').classList.add('hidden')"></div>
            <div class="relative w-full max-w-lg bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all z-10 border border-gray-100">
                <form id="edit-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Editar Imposto</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Imposto</label>
                                <select name="tipo" id="edit-tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                                    <option value="IVA">IVA</option>
                                    <option value="IS">Imposto de Selo</option>
                                    <option value="IRT">Retenção na Fonte (IRT/II)</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Código AGT</label>
                                    <input type="text" name="sigla" id="edit-sigla" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Taxa (%)</label>
                                    <input type="number" step="0.01" name="taxa" id="edit-taxa" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                                <input type="text" name="nome" id="edit-nome" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cód. Isenção</label>
                                    <input type="text" name="motivo_isencao_codigo" id="edit-motivo_isencao_codigo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Motivo Isenção</label>
                                    <input type="text" name="motivo_isencao_descricao" id="edit-motivo_isencao_descricao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="ativo" id="edit-ativo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Atualizar</button>
                        <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editImposto(imposto) {
            document.getElementById('edit-form').action = '/impostos/' + imposto.id;
            document.getElementById('edit-tipo').value = imposto.tipo;
            document.getElementById('edit-sigla').value = imposto.sigla;
            document.getElementById('edit-taxa').value = imposto.taxa;
            document.getElementById('edit-nome').value = imposto.nome;
            document.getElementById('edit-motivo_isencao_codigo').value = imposto.motivo_isencao_codigo || '';
            document.getElementById('edit-motivo_isencao_descricao').value = imposto.motivo_isencao_descricao || '';
            document.getElementById('edit-ativo').value = imposto.ativo ? '1' : '0';
            
            document.getElementById('modal-edit').classList.remove('hidden');
        }
    </script>
</x-app-layout>
