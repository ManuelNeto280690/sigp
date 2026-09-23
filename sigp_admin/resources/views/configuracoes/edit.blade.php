<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-edit text-white text-xs"></i>
                    </div>
                    {{ __('Editar Configuração') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Editar configuração: {{ $configuracao->nome }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('configuracoes.show', $configuracao) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-eye mr-1 text-xs"></i>Visualizar
                </a>
                <a href="{{ route('configuracoes.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('configuracoes.update', $configuracao) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Chave (readonly) -->
                            <div>
                                <label for="chave" class="block text-sm font-medium text-gray-700 mb-2">
                                    Chave da Configuração
                                </label>
                                <input type="text" 
                                       name="chave" 
                                       id="chave"
                                       value="{{ $configuracao->chave }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                                       readonly>
                                <p class="mt-1 text-xs text-gray-500">A chave não pode ser alterada</p>
                            </div>

                            <!-- Nome -->
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nome da Configuração <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="nome" 
                                       id="nome"
                                       value="{{ old('nome', $configuracao->nome) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       required>
                                @error('nome')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo (readonly) -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo
                                </label>
                                <input type="text" 
                                       name="tipo" 
                                       id="tipo"
                                       value="{{ $configuracao->tipo }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                                       readonly>
                                <p class="mt-1 text-xs text-gray-500">O tipo não pode ser alterado</p>
                            </div>

                            <!-- Categoria -->
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoria
                                </label>
                                <input type="text" 
                                       name="categoria" 
                                       id="categoria"
                                       value="{{ old('categoria', $configuracao->categoria) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="ex: Sistema, Empresa, Relatórios">
                                @error('categoria')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição
                            </label>
                            <textarea name="descricao" 
                                      id="descricao"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Descreva o propósito desta configuração">{{ old('descricao', $configuracao->descricao) }}</textarea>
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valor -->
                        <div id="valor-container">
                            <label for="valor" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor <span class="text-red-500">*</span>
                            </label>
                            
                            @switch($configuracao->tipo)
                                @case('boolean')
                                    <select name="valor" id="valor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                        <option value="true" {{ old('valor', $configuracao->valor) == 'true' ? 'selected' : '' }}>Verdadeiro</option>
                                        <option value="false" {{ old('valor', $configuracao->valor) == 'false' ? 'selected' : '' }}>Falso</option>
                                    </select>
                                    @break
                                @case('integer')
                                    <input type="number" 
                                           name="valor" 
                                           id="valor"
                                           value="{{ old('valor', $configuracao->valor) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           required>
                                    @break
                                @case('json')
                                    <textarea name="valor" 
                                              id="valor"
                                              rows="5"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                                              placeholder='{"chave": "valor"}'
                                              required>{{ old('valor', is_array($configuracao->valor) ? json_encode($configuracao->valor, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $configuracao->valor) }}</textarea>
                                    @break
                                @case('textarea')
                                    <textarea name="valor" 
                                              id="valor"
                                              rows="5"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                              placeholder="Digite o conteúdo..."
                                              required>{{ old('valor', $configuracao->valor) }}</textarea>
                                    @break
                                @case('date')
                                    <input type="date" 
                                           name="valor" 
                                           id="valor"
                                           value="{{ old('valor', $configuracao->valor) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           required>
                                    @break
                                @case('file')
                                    <div class="space-y-3">
                                        @if($configuracao->valor)
                                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border">
                                                <i class="fas fa-file-image text-blue-500"></i>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ basename($configuracao->valor) }}</p>
                                                    <p class="text-xs text-gray-500">Arquivo atual</p>
                                                </div>
                                                @if(in_array(strtolower(pathinfo($configuracao->valor, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                                    <img src="{{ asset('storage/' . $configuracao->valor) }}" alt="Preview" class="h-12 w-12 object-cover rounded">
                                                @endif
                                            </div>
                                        @endif
                                        <input type="file" 
                                               name="arquivo" 
                                               id="arquivo"
                                               accept="image/*"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <p class="text-xs text-gray-500">Deixe em branco para manter o arquivo atual</p>
                                    </div>
                                    @break
                                @default
                                    <input type="text" 
                                           name="valor" 
                                           id="valor"
                                           value="{{ old('valor', $configuracao->valor) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           required>
                            @endswitch
                            
                            @error('valor')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('arquivo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Regras de Validação -->
                        <div>
                            <label for="validacao" class="block text-sm font-medium text-gray-700 mb-2">
                                Regras de Validação
                            </label>
                            <input type="text" 
                                   name="validacao" 
                                   id="validacao"
                                   value="{{ old('validacao', $configuracao->validacao) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="ex: required|string|max:255">
                            @error('validacao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Regras de validação do Laravel (opcional)</p>
                        </div>

                        <!-- Opções JSON -->
                        <div>
                            <label for="opcoes" class="block text-sm font-medium text-gray-700 mb-2">
                                Opções (JSON)
                            </label>
                            <textarea name="opcoes" 
                                      id="opcoes"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                                      placeholder='{"opcao1": "valor1", "opcao2": "valor2"}'>{{ old('opcoes', is_array($configuracao->opcoes) ? json_encode($configuracao->opcoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $configuracao->opcoes) }}</textarea>
                            @error('opcoes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Opções adicionais em formato JSON (opcional)</p>
                        </div>

                        <!-- Checkboxes -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="ativo" 
                                       id="ativo"
                                       value="1"
                                       {{ old('ativo', $configuracao->ativo) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="ativo" class="ml-2 block text-sm text-gray-900">
                                    Configuração ativa
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="publico" 
                                       id="publico"
                                       value="1"
                                       {{ old('publico', $configuracao->publico) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="publico" class="ml-2 block text-sm text-gray-900">
                                    Configuração pública
                                </label>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('configuracoes.index') }}" 
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                                <i class="fas fa-save mr-2"></i>Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>