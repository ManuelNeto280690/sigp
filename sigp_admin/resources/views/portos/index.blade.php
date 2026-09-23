<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-anchor text-white text-xs"></i>
                    </div>
                    {{ __('Gestão de Portos') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Cadastro e controle dos portos do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('portos.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3395da;">
                    <i class="fas fa-plus mr-1"></i>
                    Novo Porto
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

            <!-- Estatísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <p class="text-blue-600 text-xs font-semibold uppercase">Total de Portos</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <p class="text-green-600 text-xs font-semibold uppercase">Workers Ativos</p>
                    <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['running'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
                    <p class="text-yellow-600 text-xs font-semibold uppercase">Idle</p>
                    <p class="text-2xl font-bold text-yellow-900 mt-1">{{ $stats['idle'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                    <p class="text-red-600 text-xs font-semibold uppercase">Erro / Parado</p>
                    <p class="text-2xl font-bold text-red-900 mt-1">{{ $stats['error'] + $stats['stopped'] }}</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-600 text-xs"></i>
                        Filtros de Pesquisa
                    </h3>
                </div>
                <div class="p-4">
                    <form method="GET" action="{{ route('portos.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Buscar Porto</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nome, domínio, email" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status do Worker</label>
                            <select name="status" id="status" class="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-xs">
                                <option value="">Todos</option>
                                @foreach(['idle','running','stopped','error'] as $s)
                                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="flex-1 text-white font-medium py-2 px-3 rounded-md transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 text-xs" style="background: #3395da;">
                                <i class="fas fa-search mr-1 text-xs"></i>Filtrar
                            </button>
                            <a href="{{ route('portos.index') }}" class="text-white font-medium py-2 px-3 rounded-md transition duration-200 flex items-center justify-center text-xs" style="background: #6b7280;">
                                <i class="fas fa-times text-xs"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-list mr-2 text-blue-600 text-xs"></i>
                                Lista de Portos
                            </h3>
                            @can('portos.create')
                                <a href="{{ route('portos.create') }}" class="text-white font-bold py-1.5 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center text-xs" style="background: #3395da;">
                                    <i class="fas fa-plus mr-1"></i>
                                    Adicionar Novo Porto
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Porto</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Domínio</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Contato</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Responsável</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Funcionários</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Worker</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($portos as $porto)
                                <tr class="hover:bg-blue-50 transition-colors duration-200 group">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                <i class="fas fa-anchor text-blue-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $porto->nome }}</div>
                                                <div class="text-xs text-gray-500">Slug: {{ $porto->slug }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $porto->dominio }}</div>
                                        <div class="text-xs text-gray-500">{{ $porto->path }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $porto->email ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $porto->telefone ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $porto->responsavel ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $porto->telefone_responsavel ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ $porto->num_funcionarios ?? 0 }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $map = ['running' => 'green', 'idle' => 'yellow', 'stopped' => 'gray', 'error' => 'red'];
                                            $color = $map[$porto->worker_status] ?? 'gray';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                            <i class="fas fa-circle mr-1 text-{{ $color }}-500"></i>{{ ucfirst($porto->worker_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('portos.show', $porto) }}" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('portos.edit', $porto) }}" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('portos.destroy', $porto) }}" method="POST" class="inline" onsubmit="return confirm('Confirma excluir este porto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                <i class="fas fa-anchor text-gray-400 text-xl"></i>
                                            </div>
                                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Nenhum porto encontrado</h3>
                                            <p class="text-gray-500 mb-4 text-xs">Não há portos que correspondam aos filtros aplicados.</p>
                                            <a href="{{ route('portos.create') }}" class="text-white font-semibold py-2 px-4 rounded-md transition duration-200 flex items-center text-xs" style="background: #3395da;">
                                                <i class="fas fa-plus mr-2"></i>Adicionar Primeiro Porto
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                    {{ $portos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>