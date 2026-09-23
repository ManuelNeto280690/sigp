
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-anchor mr-2 text-blue-600"></i>
                    {{ __('Detalhes do Berço') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $berco->nome }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('bercos.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Adicionar Berço
                </a>
                <a href="{{ route('bercos.edit', $berco) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
                <a href="{{ route('bercos.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Terminal</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                {{ $berco->terminal->nome ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                {{ $berco->nome }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Calado Máximo</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                {{ $berco->calado_maximo !== null ? number_format($berco->calado_maximo, 2, ',', '.') . ' m' : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Comprimento Máximo</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                {{ $berco->comprimento_maximo !== null ? number_format($berco->comprimento_maximo, 2, ',', '.') . ' m' : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="px-3 py-2 bg-gray-50 rounded-lg">
                                @php($st = $berco->status)
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $st === 'disponivel' ? 'bg-green-100 text-green-700' : ($st === 'ocupado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ ucfirst($st) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Criado em</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                {{ $berco->created_at ? $berco->created_at->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Atualizado em</label>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg px-3 py-2">
                                {{ $berco->updated_at ? $berco->updated_at->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('bercos.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                    Voltar à lista
                </a>
                <a href="{{ route('bercos.edit', $berco) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                    Editar
                </a>
            </div>
        </div>
    </div>
</x-app-layout>