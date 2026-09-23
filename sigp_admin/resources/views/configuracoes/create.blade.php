<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-plus text-white text-xs"></i>
                    </div>
                    {{ __('Nova Configuração') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Criar uma nova configuração do sistema</p>
            </div>
            <div class="flex items-center space-x-3">
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
                    <form method="POST" action="{{ route('configuracoes.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Chave -->
                            <div>
                                <label for="chave" class="block text-sm font-medium text-gray-700 mb-2">
                                    Chave da Configuração <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="chave" 
                                       id="chave"
                                       value="{{ old('chave') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="ex: nome_empresa"
                                       required>
                                @error('chave')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Identificador único da configuração (sem espaços)</p>
                            </div>

                            <!-- Nome -->
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nome da Configuração <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="nome" 
                                       id="nome"
                                       value="{{ old('nome') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="ex: Nome da Empresa"
                                       required>
                                @error('nome')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo" 
                                        id="tipo"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                        onchange="toggleFileInput()">
                                    <option value="">Selecione o tipo</option>
                                    <option value="string" {{ old('tipo') == 'string' ? 'selected' : '' }}>Texto</option>
                                    <option value="integer" {{ old('tipo') == 'integer' ? 'selected' : '' }}>Número Inteiro</option>
                                    <option value="float" {{ old('tipo') == 'float' ? 'selected' : '' }}>Número Decimal</option>
                                    <option value="textarea" {{ old('tipo') == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                    <option value="boolean" {{ old('tipo') == 'boolean' ? 'selected' : '' }}>Verdadeiro/Falso</option>
                                    <option value="json" {{ old('tipo') == 'json' ? 'selected' : '' }}>JSON</option>
                                    <option value="file" {{ old('tipo') == 'file' ? 'selected' : '' }}>Arquivo</option>
                                    <option value="email" {{ old('tipo') == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="url" {{ old('tipo') == 'url' ? 'selected' : '' }}>URL</option>
                                    <option value="date" {{ old('tipo') == 'date' ? 'selected' : '' }}>Data</option>
                                    <option value="time" {{ old('tipo') == 'time' ? 'selected' : '' }}>Hora</option>
                                    <option value="datetime" {{ old('tipo') == 'datetime' ? 'selected' : '' }}>Data e Hora</option>
                                </select>
                                @error('tipo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Categoria -->
                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoria <span class="text-red-500">*</span>
                                </label>
                                <select name="categoria" 
                                        id="categoria"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required>
                                    <option value="">Selecione a categoria</option>
                                    <option value="sistema" {{ old('categoria') == 'sistema' ? 'selected' : '' }}>Sistema</option>
                                    <option value="empresa" {{ old('categoria') == 'empresa' ? 'selected' : '' }}>Empresa</option>
                                    <option value="visual" {{ old('categoria') == 'visual' ? 'selected' : '' }}>Visual</option>
                                    <option value="email" {{ old('categoria') == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="seguranca" {{ old('categoria') == 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="interface" {{ old('categoria') == 'interface' ? 'selected' : '' }}>Interface</option>
                                    <option value="relatorios" {{ old('categoria') == 'relatorios' ? 'selected' : '' }}>Relatórios</option>
                                    <option value="notificacoes" {{ old('categoria') == 'notificacoes' ? 'selected' : '' }}>Notificações</option>
                                    <option value="backup" {{ old('categoria') == 'backup' ? 'selected' : '' }}>Backup</option>
                                    <option value="integracao" {{ old('categoria') == 'integracao' ? 'selected' : '' }}>Integração</option>
                                </select>
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
                                      placeholder="Descreva o propósito desta configuração">{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valor -->
                        <div id="valor-container">
                            <label for="valor" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="valor" 
                                   id="valor"
                                   value="{{ old('valor') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Valor da configuração"
                                   required>
                            @error('valor')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Upload de Arquivo (oculto por padrão) -->
                        <div id="arquivo-container" style="display: none;">
                            <label for="arquivo" class="block text-sm font-medium text-gray-700 mb-2">
                                Arquivo
                            </label>
                            <input type="file" 
                                   name="arquivo" 
                                   id="arquivo"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   accept="image/*,.pdf,.doc,.docx,.txt">
                            @error('arquivo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Formatos aceitos: JPG, PNG, PDF, DOC, DOCX, TXT (máx: 5MB)</p>
                        </div>

                        <!-- Opções Avançadas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Validação -->
                            <div>
                                <label for="validacao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Regra de Validação
                                </label>
                                <input type="text" 
                                       name="validacao" 
                                       id="validacao"
                                       value="{{ old('validacao') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="ex: required|min:3|max:100">
                                @error('validacao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Regras de validação do Laravel</p>
                            </div>

                            <!-- Opções JSON -->
                            <div>
                                <label for="opcoes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Opções (JSON)
                                </label>
                                <textarea name="opcoes" 
                                          id="opcoes"
                                          rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-xs"
                                          placeholder='{"opcao1": "valor1", "opcao2": "valor2"}'>{{ old('opcoes') }}</textarea>
                                @error('opcoes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Opções adicionais em formato JSON</p>
                            </div>
                        </div>

                        <!-- Checkboxes -->
                        <div class="flex items-center space-x-6">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="ativo" 
                                       id="ativo"
                                       value="1"
                                       {{ old('ativo', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="ativo" class="ml-2 block text-sm text-gray-700">
                                    Configuração ativa
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="publico" 
                                       id="publico"
                                       value="1"
                                       {{ old('publico') ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="publico" class="ml-2 block text-sm text-gray-700">
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
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i>Salvar Configuração
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleFileInput() {
            const tipo = document.getElementById('tipo').value;
            const valorContainer = document.getElementById('valor-container');
            const arquivoContainer = document.getElementById('arquivo-container');
            
            if (tipo === 'file') {
                valorContainer.style.display = 'none';
                arquivoContainer.style.display = 'block';
                document.getElementById('valor').required = false;
            } else {
                valorContainer.style.display = 'block';
                arquivoContainer.style.display = 'none';
                document.getElementById('valor').required = true;
            }
        }
    </script>
</x-app-layout>