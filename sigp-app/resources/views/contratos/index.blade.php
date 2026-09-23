<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-file-contract text-white text-xs"></i>
                    </div>
                    Contratos
                </h2>
                <p class="text-gray-600 text-xs mt-1">Gestão de contratos e tarifas</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="location.reload()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <a href="{{ route('contratos.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #0084de;">
                    <i class="fas fa-plus mr-1"></i>Novo Contrato
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 text-lg"></i>
                        <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                        <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-400 text-lg"></i>
                        <ul class="ml-3 list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Contratos</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $contratos->total() }}</p>
                            <p class="text-blue-600 text-xs mt-1">Registrados</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-file-contract text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-4">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border border-gray-200 rounded-md mb-4">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-list mr-2 text-blue-600 text-xs"></i>
                                    Lista de Contratos
                                </h3>
                                <p class="text-xs text-gray-600 mt-1">{{ $contratos->total() }} contratos encontrados</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if(Route::has('contratos.relatorioFiltrado'))
                                <a href="{{ route('contratos.relatorioFiltrado', array_merge(request()->query(), ['formato' => 'pdf'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #dc2626;">
                                    <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                                </a>
                                <a href="{{ route('contratos.relatorioFiltrado', array_merge(request()->query(), ['formato' => 'excel'])) }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #16a34a;">
                                    <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                                </a>
                                @endif
                                <a href="{{ route('contratos.create') }}" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #3395da;">
                                    <i class="fas fa-plus mr-1 text-xs"></i>Adicionar Contrato
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Filtros de Pesquisa -->
                    <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden mb-4">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-filter mr-2 text-blue-600 text-xs"></i>
                                Filtros de Pesquisa
                            </h3>
                        </div>
                        <div class="p-4">
                            <form method="GET" action="{{ route('contratos.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Buscar Contrato</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400 text-xs"></i>
                                        </div>
                                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Título do contrato" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                    </div>
                                </div>
                                <div>
                                    <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" id="status" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                        <option value="">Todos os status</option>
                                        <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                        <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                                        <option value="cancelado" {{ request('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="modo_faturacao" class="block text-xs font-medium text-gray-700 mb-1">Modo de Faturação</label>
                                    <select name="modo_faturacao" id="modo_faturacao" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                        <option value="">Todos os modos</option>
                                        <option value="por_operacao" {{ request('modo_faturacao') == 'por_operacao' ? 'selected' : '' }}>Por operação</option>
                                        <option value="consolidado_por_navio" {{ request('modo_faturacao') == 'consolidado_por_navio' ? 'selected' : '' }}>Consolidado por navio</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="concessionaria_id" class="block text-xs font-medium text-gray-700 mb-1">Concessionária</label>
                                    <select name="concessionaria_id" id="concessionaria_id" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                        <option value="">Todas</option>
                                        @isset($concessionarias)
                                            @foreach($concessionarias as $con)
                                                <option value="{{ $con->id }}" {{ request('concessionaria_id') == $con->id ? 'selected' : '' }}>{{ $con->nome }}</option>
                                            @endforeach
                                        @endisset
                                    </select>
                                </div>
                                <div class="flex items-end space-x-2">
                                    <button type="submit" class="flex-1 text-white font-medium py-2 px-3 rounded-md transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 text-xs" style="background: #3395da;">
                                        <i class="fas fa-search mr-1 text-xs"></i>Filtrar
                                    </button>
                                    <a href="{{ route('contratos.index') }}" class="text-white font-medium py-2 px-3 rounded-md transition duration-200 flex items-center justify-center text-xs" style="background: #6b7280;">
                                        <i class="fas fa-times text-xs"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Título</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Concessionária</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Modo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($contratos as $c)
                            <tr class="border-t">
                                <td class="p-2">{{ $c->titulo }}</td>
                                <td class="p-2">{{ $c->concessionaria->nome ?? '-' }}</td>
                                <td class="p-2">{{ $c->modo_faturacao }}</td>
                                <td class="p-2">{{ $c->status }}</td>
                                <td class="p-2 text-right">
                                    <a href="{{ route('contratos.show',$c) }}" 
                                       class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" 
                                       style="background: #6b7280;" title="Ver">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('contratos.edit',$c) }}" 
                                       class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 ml-2" 
                                       style="background: #3395da;" title="Editar">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form method="POST" action="{{ route('contratos.destroy',$c) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir o contrato {{ addslashes($c->titulo) }}? Esta ação não pode ser desfeita.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 ml-2" 
                                                style="background: #dc2626;" title="Excluir">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    @if($contratos->hasPages())
                        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-700">
                                    Mostrando {{ $contratos->firstItem() }} a {{ $contratos->lastItem() }} de {{ $contratos->total() }} resultados
                                </div>
                                <div>
                                    {{ $contratos->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>