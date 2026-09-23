<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-eye text-white text-xs"></i>
                    </div>
                    {{ __('Detalhes do Log de Auditoria') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Visualize os detalhes completos desta ação de auditoria</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('audit-logs.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Informações Principais -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Informações Principais
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data e Hora</label>
                        <div class="flex items-center">
                            <i class="fas fa-calendar text-blue-500 mr-2"></i>
                            <span class="text-sm text-gray-900">{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ $auditLog->created_at->diffForHumans() }}</span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuário</label>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                <i class="fas fa-user text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $auditLog->user->name ?? 'Sistema' }}</div>
                                <div class="text-xs text-gray-500">{{ $auditLog->user->email ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ação</label>
                        @php
                            $actionColors = [
                                'create' => 'bg-green-100 text-green-800',
                                'update' => 'bg-blue-100 text-blue-800',
                                'delete' => 'bg-red-100 text-red-800',
                                'view' => 'bg-gray-100 text-gray-800',
                                'login' => 'bg-blue-100 text-blue-800',
                                'logout' => 'bg-yellow-100 text-yellow-800',
                                'login_failed' => 'bg-red-100 text-red-800',
                            ];
                            $colorClass = $actionColors[$auditLog->action] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                            {{ ucfirst($auditLog->action) }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                        <div class="flex items-center">
                            <i class="fas fa-database text-blue-500 mr-2"></i>
                            <span class="text-sm text-gray-900">{{ $auditLog->getModelName() }}</span>
                        </div>
                        @if($auditLog->model_id)
                        <span class="text-xs text-gray-500">ID: {{ $auditLog->model_id }}</span>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Endereço IP</label>
                        <div class="flex items-center">
                            <i class="fas fa-globe text-blue-500 mr-2"></i>
                            <span class="text-sm text-gray-900 font-mono">{{ $auditLog->ip_address }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID do Log</label>
                        <div class="flex items-center">
                            <i class="fas fa-fingerprint text-blue-500 mr-2"></i>
                            <span class="text-sm text-gray-900 font-mono">{{ $auditLog->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            @if($auditLog->description)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                    Descrição
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-900">{{ $auditLog->description }}</p>
                </div>
            </div>
            @endif

            <!-- User Agent -->
            @if($auditLog->user_agent)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-desktop text-blue-500 mr-2"></i>
                    Informações do Navegador
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-900 font-mono break-all">{{ $auditLog->user_agent }}</p>
                </div>
            </div>
            @endif

            <!-- Alterações -->
            @if($auditLog->old_values || $auditLog->new_values)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-code-branch text-blue-500 mr-2"></i>
                    Alterações Realizadas
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @if($auditLog->old_values)
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-minus-circle text-red-500 mr-2"></i>
                            Valores Anteriores
                        </h4>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <pre class="text-sm text-gray-900 overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif
                    
                    @if($auditLog->new_values)
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-plus-circle text-green-500 mr-2"></i>
                            Novos Valores
                        </h4>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <pre class="text-sm text-gray-900 overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Logs Relacionados -->
            @if($relatedLogs->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-link text-blue-500 mr-2"></i>
                    Logs Relacionados
                    <span class="ml-2 text-sm font-normal text-gray-500">({{ $relatedLogs->count() }} registros)</span>
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($relatedLogs as $relatedLog)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $relatedLog->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $relatedLog->user->name ?? 'Sistema' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $colorClass = $actionColors[$relatedLog->action] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ ucfirst($relatedLog->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 max-w-xs">
                                    <div class="truncate" title="{{ $relatedLog->description }}">
                                        {{ $relatedLog->description ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('audit-logs.show', $relatedLog) }}" 
                                       class="text-blue-600 hover:text-blue-900" 
                                       title="Ver detalhes">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>