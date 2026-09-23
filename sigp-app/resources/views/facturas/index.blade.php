<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Header section that was previously in x-slot -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                            <i class="fas fa-file-invoice-dollar text-white text-sm"></i>
                        </div>
                        Faturas
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">Listagem de faturas emitidas</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('facturas.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-plus mr-2 text-sm"></i>Nova Fatura Manual
                    </a>
                    <button onclick="location.reload()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                        <i class="fas fa-sync-alt mr-2 text-sm"></i>Atualizar
                    </button>
                </div>
            </div>
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
                            <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Total de Faturas</p>
                            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $facturas->total() }}</p>
                            <p class="text-blue-600 text-xs mt-1">Emitidas</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center shadow-lg">
                            <i class="fas fa-file-invoice-dollar text-white text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-4">
                    <!-- Filtros de Pesquisa -->
                    <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden mb-4">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-filter mr-2 text-blue-600 text-xs"></i>
                                Filtros de Pesquisa
                            </h3>
                        </div>
                        <div class="p-4">
                            <form id="filter-form" method="GET" action="{{ route('facturas.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                                <div>
                                    <label for="factura-search" class="block text-xs font-medium text-gray-700 mb-1">Buscar Fatura</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400 text-xs"></i>
                                        </div>
                                        <input type="text" name="search" id="factura-search" value="{{ request('search') }}" placeholder="Número da fatura" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                    </div>
                                </div>
                                <div>
                                    <label for="factura-status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" id="factura-status" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                        <option value="">Todos os status</option>
                                        <option value="emitida" {{ request('status') == 'emitida' ? 'selected' : '' }}>Emitida</option>
                                        <option value="paga" {{ request('status') == 'paga' ? 'selected' : '' }}>Paga</option>
                                        <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="factura-tipo" class="block text-xs font-medium text-gray-700 mb-1">Tipo de Documento</label>
                                    <select name="tipo_documento" id="factura-tipo" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                        <option value="">Todos os tipos</option>
                                        <option value="ft" {{ request('tipo_documento') == 'ft' ? 'selected' : '' }}>Fatura (FT)</option>
                                        <option value="fr" {{ request('tipo_documento') == 'fr' ? 'selected' : '' }}>Fatura-Recibo (FR)</option>
                                        <option value="fp" {{ request('tipo_documento') == 'fp' ? 'selected' : '' }}>Pró-forma (FP)</option>
                                        <option value="nc" {{ request('tipo_documento') == 'nc' ? 'selected' : '' }}>Nota de Crédito (NC)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="data-inicio" class="block text-xs font-medium text-gray-700 mb-1">Data Início</label>
                                    <input type="date" name="data_inicio" id="data-inicio" value="{{ request('data_inicio') }}" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                </div>
                                <div>
                                    <label for="data-fim" class="block text-xs font-medium text-gray-700 mb-1">Data Fim</label>
                                    <input type="date" name="data_fim" id="data-fim" value="{{ request('data_fim') }}" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                </div>
                                <div class="flex items-end space-x-2">
                                    <button type="submit" class="flex-1 text-white font-medium py-2 px-3 rounded-md transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 text-xs" style="background: #3395da;">
                                        <i class="fas fa-search mr-1 text-xs"></i>Filtrar
                                    </button>
                                    <a href="{{ route('facturas.index') }}" class="text-white font-medium py-2 px-3 rounded-md transition duration-200 flex items-center justify-center text-xs hover:bg-gray-600" style="background: #6b7280;">
                                        <i class="fas fa-times text-xs"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200 rounded-md mb-4">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-list mr-2 text-blue-600 text-xs"></i>
                                    Lista de Faturas
                                </h3>
                                <p class="text-xs text-gray-600 mt-1">{{ $facturas->total() }} faturas encontradas</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if(Route::has('facturas.relatorioFiltrado'))
                                <button type="submit" form="filter-form" formaction="{{ route('facturas.relatorioFiltrado') }}" name="formato" value="pdf" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #dc2626;">
                                    <i class="fas fa-file-pdf mr-1 text-xs"></i>Relatório PDF
                                </button>
                                <button type="submit" form="filter-form" formaction="{{ route('facturas.relatorioFiltrado') }}" name="formato" value="excel" class="text-white px-3 py-1.5 rounded-md text-xs font-medium transition duration-200 flex items-center hover:shadow-lg transform hover:-translate-y-0.5" style="background: #16a34a;">
                                    <i class="fas fa-file-excel mr-1 text-xs"></i>Relatório Excel
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Número</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Contrato</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($facturas as $f)
                            <tr class="border-t">
                                <td class="p-2">{{ $f->numero }}</td>
                                <td class="p-2">
                                    @switch(strtolower($f->status))
                                        @case('paga')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Paga
                                            </span>
                                            @break
                                        @case('cancelada')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Cancelada
                                            </span>
                                            @break
                                        @default
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ ucfirst($f->status) }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="p-2 text-right">{{ number_format($f->valor_total, 2, ',', '.') }}</td>
                                <td class="p-2">{{ $f->contrato->titulo ?? '-' }}</td>

                                <td class="p-2 text-right">
                                    <a href="{{ route('facturas.show',$f) }}" class="text-blue-600">Ver</a>
                                     <a href="{{ route('facturas.exportPdf', $f->id) }}" class="text-red-600 hover:text-red-800 transition-colors" title="Baixar PDF">
                                            <i class="fas fa-file-pdf text-base"></i>
                                        </a>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    @if($facturas->hasPages())
                        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-700">
                                    Mostrando {{ $facturas->firstItem() }} a {{ $facturas->lastItem() }} de {{ $facturas->total() }} resultados
                                </div>
                                <div>
                                    {{ $facturas->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>