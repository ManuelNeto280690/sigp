<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-boxes text-white text-xs"></i>
                    </div>
                    Movimento de Carga
                </h2>
                <p class="text-gray-600 text-xs mt-1">Detalhes da operação</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('movimentos-carga.edit',$movimento) }}" class="px-4 py-2 bg-gray-100 rounded">Editar</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4" style="font-size: 14px;">
                <x-stat-card title="Operação" :value="ucfirst($movimento->tipo_operacao)" color="blue" icon="fas fa-dolly" class="text-xs" />
                <x-stat-card title="Quantidade" :value="number_format($movimento->quantidade,2,',','.')" color="green" icon="fas fa-weight-hanging" class="text-xs" />
                <x-stat-card title="Início" :value="\Carbon\Carbon::parse($movimento->inicio)->format('d/m/Y H:i')" color="purple" icon="fas fa-play" class="text-xs" />
                <x-stat-card title="Fim" :value="$movimento->fim ? \Carbon\Carbon::parse($movimento->fim)->format('d/m/Y H:i') : '—'" color="gray" icon="fas fa-stop" class="text-xs" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="space-y-4 lg:col-span-1">
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3"><h3 class="text-sm font-semibold text-gray-900 flex items-center"><i class="fas fa-info-circle mr-2 text-blue-600"></i>Informações Básicas</h3></div>
                        <div class="grid grid-cols-1 gap-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Produto</span><span class="font-medium">{{ $movimento->tipo_produto }}</span></div>
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Terminal</span><span class="font-medium">{{ $movimento->terminal->nome ?? 'N/A' }}</span></div>
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Berço</span><span class="font-medium">{{ $movimento->berco->nome ?? 'N/A' }}</span></div>
                            <div class="flex justify-between py-1"><span class="text-gray-500">Guindaste</span><span class="font-medium">{{ $movimento->guindaste->nome ?? 'N/A' }}</span></div>
                        </div>
                    </x-card>
                </div>

                <div class="space-y-4 lg:col-span-1">
                    <x-card class="p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="border-b border-gray-200 pb-2 mb-3"><h3 class="text-sm font-semibold text-gray-900 flex items-center"><i class="fas fa-route mr-2 text-indigo-600"></i>Movimentação</h3></div>
                        <div class="grid grid-cols-1 gap-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Início</span><span class="font-medium">{{ \Carbon\Carbon::parse($movimento->inicio)->format('d/m/Y H:i') }}</span></div>
                            <div class="flex justify-between py-1 border-b border-gray-100"><span class="text-gray-500">Fim</span><span class="font-medium">{{ $movimento->fim ? \Carbon\Carbon::parse($movimento->fim)->format('d/m/Y H:i') : '—' }}</span></div>
                            <div class="flex justify-between py-1"><span class="text-gray-500">Observações</span><span class="font-medium">{{ $movimento->observacoes ?: '—' }}</span></div>
                        </div>
                    </x-card>
                </div>

                <div class="space-y-4 lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200"><h3 class="text-lg font-semibold text-gray-900">Ações Rápidas</h3></div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <a href="{{ route('movimentos-carga.edit',$movimento) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200"><i class="fas fa-edit mr-2"></i>Editar</a>
                                <a href="{{ route('movimentos-carga.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200"><i class="fas fa-arrow-left mr-2"></i>Voltar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>