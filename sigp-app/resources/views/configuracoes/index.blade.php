<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-cogs text-white text-xs"></i>
                    </div>
                    {{ __('Configurações da Plataforma') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Gerencie as configurações gerais do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
               
                <button onclick="refreshData()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
                <button onclick="exportarConfiguracoes()" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-download mr-1 text-xs"></i>Exportar
                </button>
                <button onclick="limparCache()" class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-trash mr-1 text-xs"></i>Limpar Cache
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensagens de Sucesso/Erro -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter text-blue-500 mr-2"></i>
                        Filtros
                    </h3>
                </div>
                
                <div class="p-4 bg-gray-50">
                    <form method="GET" action="{{ route('configuracoes.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Busca -->
                            <div>
                                <label for="busca" class="block text-xs font-medium text-gray-700 mb-1">Buscar</label>
                                <input type="text" 
                                       name="busca" 
                                       id="busca"
                                       value="{{ request('busca') }}"
                                       placeholder="Chave, nome ou descrição..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                            </div>

                            <!-- Categoria -->
                            <div>
                                <label for="categoria" class="block text-xs font-medium text-gray-700 mb-1">Categoria</label>
                                <select name="categoria" 
                                        id="categoria"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                    <option value="">Todas as categorias</option>
                                    <option value="geral" {{ request('categoria') === 'geral' ? 'selected' : '' }}>Geral</option>
                                    <option value="sistema" {{ request('categoria') === 'sistema' ? 'selected' : '' }}>Sistema</option>
                                    <option value="seguranca" {{ request('categoria') === 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="email" {{ request('categoria') === 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="notificacoes" {{ request('categoria') === 'notificacoes' ? 'selected' : '' }}>Notificações</option>
                                    <option value="relatorios" {{ request('categoria') === 'relatorios' ? 'selected' : '' }}>Relatórios</option>
                                    <option value="backup" {{ request('categoria') === 'backup' ? 'selected' : '' }}>Backup</option>
                                    <option value="performance" {{ request('categoria') === 'performance' ? 'selected' : '' }}>Performance</option>
                                </select>
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                <select name="tipo" 
                                        id="tipo"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                    <option value="">Todos os tipos</option>
                                    <option value="string" {{ request('tipo') === 'string' ? 'selected' : '' }}>String</option>
                                    <option value="integer" {{ request('tipo') === 'integer' ? 'selected' : '' }}>Integer</option>
                                    <option value="boolean" {{ request('tipo') === 'boolean' ? 'selected' : '' }}>Boolean</option>
                                    <option value="json" {{ request('tipo') === 'json' ? 'selected' : '' }}>JSON</option>
                                    <option value="date" {{ request('tipo') === 'date' ? 'selected' : '' }}>Date</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="ativo" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                <select name="ativo" 
                                        id="ativo"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                    <option value="">Todos</option>
                                    <option value="true" {{ request('ativo') === 'true' ? 'selected' : '' }}>Ativo</option>
                                    <option value="false" {{ request('ativo') === 'false' ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('configuracoes.index') }}" 
                               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 text-xs">
                                Limpar
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs">
                                Filtrar
                            </button>
                             <a href="{{ route('configuracoes.create') }}" class="bg-green-100 hover:bg-green-200 text-green-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-plus mr-1 text-xs"></i>Nova Configuração
                </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configurações por Categoria -->
            @if($configuracoesAgrupadas->count() > 0)
                @foreach($configuracoesAgrupadas as $categoria => $configs)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    @switch($categoria)
                                        @case('geral')
                                            <i class="fas fa-globe text-blue-600 mr-2"></i>
                                            Configurações Gerais
                                            @break
                                        @case('sistema')
                                            <i class="fas fa-server text-blue-600 mr-2"></i>
                                            Sistema
                                            @break
                                        @case('seguranca')
                                            <i class="fas fa-shield-alt text-red-600 mr-2"></i>
                                            Segurança
                                            @break
                                        @case('email')
                                            <i class="fas fa-envelope text-green-600 mr-2"></i>
                                            Email
                                            @break
                                        @case('notificacoes')
                                            <i class="fas fa-bell text-yellow-600 mr-2"></i>
                                            Notificações
                                            @break
                                        @case('relatorios')
                                            <i class="fas fa-chart-bar text-indigo-600 mr-2"></i>
                                            Relatórios
                                            @break
                                        @case('backup')
                                            <i class="fas fa-database text-orange-600 mr-2"></i>
                                            Backup
                                            @break
                                        @case('performance')
                                            <i class="fas fa-tachometer-alt text-teal-600 mr-2"></i>
                                            Performance
                                            @break
                                        @default
                                            <i class="fas fa-cog text-gray-600 mr-2"></i>
                                            {{ ucfirst($categoria) }}
                                    @endswitch
                                </h3>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    {{ $configs->count() }} {{ $configs->count() === 1 ? 'configuração' : 'configurações' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <form method="POST" action="{{ route('configuracoes.updateBatch') }}" class="space-y-4" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                
                                <div class="grid grid-cols-1 gap-4">
                                    @foreach($configs as $config)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2 mb-1">
                                                        <h4 class="text-sm font-semibold text-gray-900">{{ $config->chave }}</h4>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $config->getTipoBadgeClass() }}">
                                                            {{ strtoupper($config->tipo) }}
                                                        </span>
                                                        @if($config->is_active)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                <i class="fas fa-check-circle mr-1"></i>Ativo
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                <i class="fas fa-times-circle mr-1"></i>Inativo
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($config->descricao)
                                                        <p class="text-xs text-gray-600 mb-2">{{ $config->descricao }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Valor Atual
                                                    </label>
                                                    @if($config->editavel)
                                                        @switch($config->tipo)
                                                            @case('boolean')
                                                                <select name="configs[{{ $config->id }}][valor]" 
                                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                                                    <option value="1" {{ $config->getValorTipado() ? 'selected' : '' }}>Sim</option>
                                                                    <option value="0" {{ !$config->getValorTipado() ? 'selected' : '' }}>Não</option>
                                                                </select>
                                                                @break
                                                            @case('json')
                                                                <textarea name="configs[{{ $config->id }}][valor]" 
                                                                          rows="3"
                                                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs font-mono"
                                                                          placeholder="JSON válido">{{ $config->valor ? json_encode($config->getValorTipado(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '' }}</textarea>
                                                                @break
                                                            @case('textarea')
                                                                <textarea name="configs[{{ $config->id }}][valor]" 
                                                                          rows="3"
                                                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs"
                                                                          placeholder="Digite o conteúdo...">{{ $config->valor }}</textarea>
                                                                @break
                                                            @case('integer')
                                                                <input type="number" 
                                                                       name="configs[{{ $config->id }}][valor]" 
                                                                       value="{{ $config->valor }}"
                                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                                                @break
                                                            @case('date')
                                                                <input type="date" 
                                                                       name="configs[{{ $config->id }}][valor]" 
                                                                       value="{{ $config->valor }}"
                                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                                                @break
                                                            @case('file')
                                                                <div class="space-y-2">
                                                                    @if($config->valor)
                                                                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
                                                                            <i class="fas fa-file-image text-blue-500"></i>
                                                                            <span class="text-xs text-gray-700">{{ basename($config->valor) }}</span>
                                                                            @if(in_array(strtolower(pathinfo($config->valor, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                                                                <img src="{{ asset('storage/' . $config->valor) }}" alt="Preview" class="h-8 w-8 object-cover rounded">
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                    <input type="file" 
                                                                           name="configs[{{ $config->id }}][file]" 
                                                                           accept="image/*"
                                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                                                    <p class="text-xs text-gray-500">{{ $config->descricao }}</p>
                                                                </div>
                                                                @break
                                                            @default
                                                                <input type="text" 
                                                                       name="configs[{{ $config->id }}][valor]" 
                                                                       value="{{ $config->valor }}"
                                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                                        @endswitch
                                                        <input type="hidden" name="configs[{{ $config->id }}][id]" value="{{ $config->id }}">
                                                    @else
                                                        <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-xs text-gray-600">
                                                            @if($config->tipo === 'boolean')
                                                                {{ $config->getValorTipado() ? 'Sim' : 'Não' }}
                                                            @elseif($config->tipo === 'json')
                                                                <pre class="whitespace-pre-wrap">{{ json_encode($config->getValorTipado(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                            @else
                                                                {{ $config->valor }}
                                                            @endif
                                                            <div class="mt-1 text-xs text-red-600">
                                                                <i class="fas fa-lock mr-1"></i>Não editável
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Status
                                                    </label>
                                                    <select name="configs[{{ $config->id }}][is_active]" 
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs">
                                                        <option value="1" {{ $config->is_active ? 'selected' : '' }}>Ativo</option>
                                                        <option value="0" {{ !$config->is_active ? 'selected' : '' }}>Inativo</option>
                                                    </select>
                                                </div>
                                            </div>

                                            @if($config->updated_at)
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <div class="flex justify-between items-center">
                                                        <p class="text-xs text-gray-500">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Última atualização: {{ $config->updated_at->format('d/m/Y H:i') }}
                                                        </p>
                                                        <div class="flex items-center space-x-2">
                                                            @can('update', $config)
                                                                <a href="{{ route('configuracoes.edit', $config) }}" 
                                                                   class="text-green-600 hover:text-green-800 text-xs" 
                                                                   title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            @endcan
                                                            @can('delete', $config)
                                                                <form method="POST" action="{{ route('configuracoes.destroy', $config) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta configuração?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" 
                                                                            class="text-red-600 hover:text-red-800 text-xs" 
                                                                            title="Excluir">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex justify-end pt-4 border-t border-gray-200">
                                    <button type="submit" 
                                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 text-sm">
                                        <i class="fas fa-save mr-2"></i>Salvar Alterações
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-cogs text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma configuração encontrada</h3>
                    <p class="text-gray-600 mb-4">Não há configurações que correspondam aos filtros aplicados.</p>
                    <a href="{{ route('configuracoes.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                        <i class="fas fa-refresh mr-2"></i>Limpar Filtros
                    </a>
                </div>
            @endif

            <!-- Paginação -->
            @if($configuracoes->hasPages())
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    {{ $configuracoes->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function refreshData() {
            window.location.reload();
        }

        function exportarConfiguracoes() {
            window.location.href = '/configuracoes.export';
        }

        function limparCache() {
            if (confirm('Tem certeza que deseja limpar o cache das configurações?')) {
                fetch('/configuracoes.clearCache', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cache limpo com sucesso!');
                        window.location.reload();
                    } else {
                        alert('Erro ao limpar cache: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao limpar cache');
                });
            }
        }

        // Validação de JSON em tempo real
        document.querySelectorAll('textarea[name*="[valor]"]').forEach(textarea => {
            if (textarea.closest('.font-mono')) {
                textarea.addEventListener('blur', function() {
                    const value = this.value.trim();
                    if (value && value !== '') {
                        try {
                            JSON.parse(value);
                            this.classList.remove('border-red-500');
                            this.classList.add('border-green-500');
                        } catch (e) {
                            this.classList.remove('border-green-500');
                            this.classList.add('border-red-500');
                        }
                    } else {
                        this.classList.remove('border-red-500', 'border-green-500');
                    }
                });
            }
        });

        // Auto-save para mudanças críticas
        document.querySelectorAll('select[name*="[is_active]"]').forEach(select => {
            select.addEventListener('change', function() {
                const configId = this.name.match(/\[(\d+)\]/)[1];
                const isActive = this.value;
                
                if (isActive === '0') {
                    if (!confirm('Tem certeza que deseja desativar esta configuração? Isso pode afetar o funcionamento do sistema.')) {
                        this.value = '1';
                        return;
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>