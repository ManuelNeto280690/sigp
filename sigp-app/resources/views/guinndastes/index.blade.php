
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-industry text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Guindastes') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Controle e monitore os guindastes dos terminais</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="location.reload()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <a href="{{ route('guindastes.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Novo Guindaste
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-100">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Busca</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #3b82f6;" placeholder="Nome do guindaste">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Terminal</label>
                            <select name="terminal_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #3b82f6;">
                                <option value="">Todos</option>
                                @foreach($terminais as $terminal)
                                    <option value="{{ $terminal->id }}" {{ request('terminal_id') == $terminal->id ? 'selected' : '' }}>
                                        {{ $terminal->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #3b82f6;">
                                <option value="">Todos</option>
                                @foreach(['container','graneis_solidos','graneis_liquidos','carga_geral','passageiros','tanque','ro_ro','frigorifico'] as $t)
                                    <option value="{{ $t }}" {{ request('tipo') == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #3b82f6;">
                                <option value="">Todos</option>
                                <option value="disponivel" {{ request('status') == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                                <option value="ocupado" {{ request('status') == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                                <option value="manutencao" {{ request('status') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-xs">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                            <a href="{{ route('guindastes.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3b82f6;">
                    <i class="fas fa-plus mr-1"></i>
                    Novo Guindaste
                </a>
                        </div>
                    </form>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terminal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($guindastes as $guindaste)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $guindaste->nome }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $guindaste->terminal->nome ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ ucfirst(str_replace('_',' ',$guindaste->tipo)) }}</td>
                                        <td class="px-4 py-3">
                                            @php($st = $guindaste->status)
                                            <span class="px-2 py-1 rounded text-xs font-semibold
                                                {{ $st === 'disponivel' ? 'bg-green-100 text-green-700' : ($st === 'ocupado' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                {{ ucfirst($st) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('guindastes.show', $guindaste) }}" class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5" style="background: #3395da;" title="Visualizar">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                                <a href="{{ route('guindastes.edit', $guindaste) }}" class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 bg-yellow-500 hover:bg-yellow-600" title="Editar">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                                <form method="POST" action="{{ route('guindastes.destroy', $guindaste) }}" onsubmit="return confirm('Confirma excluir este guindaste?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center w-7 h-7 text-white rounded-md transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 bg-red-600 hover:bg-red-700" title="Excluir">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                    <i class="fas fa-industry text-gray-400 text-xl"></i>
                                                </div>
                                                <h3 class="text-sm font-semibold text-gray-900 mb-2">Nenhum guindaste encontrado</h3>
                                                <p class="text-gray-500 mb-4 text-xs">Não há guindastes que correspondam aos filtros aplicados.</p>
                                                <a href="{{ route('guindastes.create') }}" class="text-white font-semibold py-2 px-4 rounded-md transition duration-200 flex items-center text-xs" style="background: #3395da;">
                                                    <i class="fas fa-plus mr-2"></i>Adicionar Guindaste
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $guindastes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>