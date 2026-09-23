<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Novo Movimento de Embarcação') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Registre um novo movimento de entrada ou saída</p>
            </div>
            <div class="flex items-center space-x-3">
                // Linha 14 - Link voltar
                <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                
                // Linha 79 - Form action
                <form method="POST" action="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.store') }}" enctype="multipart/form-data">
                
                // Linha 178 - Botão cancelar
                <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                    <i class="fas fa-times mr-1"></i>Cancelar
                </a>
                <button type="submit" class="text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow-lg hover:shadow-xl" style="background: #0084de;">
                    <i class="fas fa-save mr-1"></i>Salvar Movimento
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <!-- Seção de Mensagens de Erro e Sucesso -->
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

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 mb-2">Foram encontrados os seguintes erros:</h3>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Formulário de Criação -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-plus-circle mr-2" style="color: #0084de;"></i>
                        Dados do Movimento
                    </h3>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Informações Básicas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="embarcacao_id" class="block text-sm font-medium text-gray-700 mb-2">Embarcação *</label>
                                <select name="embarcacao_id" id="embarcacao_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" required>
                                    <option value="">Selecione uma embarcação</option>
                                    @foreach($embarcacoes as $embarcacao)
                                        <option value="{{ $embarcacao->id }}" {{ old('embarcacao_id') == $embarcacao->id ? 'selected' : '' }}>
                                            {{ $embarcacao->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">Terminal *</label>
                                <select name="terminal_id" id="terminal_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" required>
                                    <option value="">Selecione um terminal</option>
                                    @foreach($terminais as $terminal)
                                        <option value="{{ $terminal->id }}" {{ old('terminal_id') == $terminal->id ? 'selected' : '' }}>
                                            {{ $terminal->nome }} - {{ $terminal->codigo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="tipo_movimento" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimento *</label>
                                <select name="tipo_movimento" id="tipo_movimento" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="entrada" {{ old('tipo_movimento') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                    <option value="saida" {{ old('tipo_movimento') == 'saida' ? 'selected' : '' }}>Saída</option>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                                    <option value="programado" {{ old('status', 'programado') == 'programado' ? 'selected' : '' }}>Programado</option>
                                    <option value="autorizado" {{ old('status') == 'autorizado' ? 'selected' : '' }}>Autorizado</option>
                                    <option value="em_andamento" {{ old('status') == 'em_andamento' ? 'selected' : '' }}>Em Andamento</option>
                                    <option value="concluido" {{ old('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                                    <option value="cancelado" {{ old('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <!-- Berço -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="berco" class="block text-sm font-medium text-gray-700 mb-2">Berço </label>
                                <input type="text" name="berco" id="berco" value="{{ old('berco') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Ex: Berço 1">
                            </div>

                            <div>
                                <label for="agente_maritimo" class="block text-sm font-medium text-gray-700 mb-2">Agente Marítimo</label>
                                <input type="text" name="agente_maritimo" id="agente_maritimo" value="{{ old('agente_maritimo') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Nome do agente marítimo">
                            </div>
                        </div>

                        <!-- Capitanias -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="capitania_origem" class="block text-sm font-medium text-gray-700 mb-2">Capitania de Origem</label>
                                <input type="text" name="capitania_origem" id="capitania_origem" value="{{ old('capitania_origem') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Capitania de origem">
                            </div>

                            <div>
                                <label for="capitania_destino" class="block text-sm font-medium text-gray-700 mb-2">Capitania de Destino</label>
                                <input type="text" name="capitania_destino" id="capitania_destino" value="{{ old('capitania_destino') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Capitania de destino">
                            </div>
                        </div>

                        <!-- Motivo -->
                        <div class="mb-6">
                            <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">Motivo</label>
                            <textarea name="motivo" id="motivo" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Motivo do movimento...">{{ old('motivo') }}</textarea>
                        </div>

                        <!-- Data Programada e Data Efetiva -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="data_programada" class="block text-sm font-medium text-gray-700 mb-2">Data de Entrada *</label>
                                <input type="datetime-local" name="data_programada" id="data_programada" value="{{ old('data_programada') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" required>
                            </div>

                            <div>
                                <label for="data_efetiva" class="block text-sm font-medium text-gray-700 mb-2">Data de Saída *</label>
                                <input type="datetime-local" name="data_efetiva" id="data_efetiva" value="{{ old('data_efetiva') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;">
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="mb-6">
                            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                            <textarea name="observacoes" id="observacoes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" placeholder="Informações adicionais sobre o movimento...">{{ old('observacoes') }}</textarea>
                        </div>

                        <!-- Documentos -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Documentos</label>
                            <div id="documentos-container" class="space-y-3">
                                <div class="documento-item flex items-center space-x-3">
                                    <input type="file" name="documentos[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <button type="button" onclick="adicionarDocumento()" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-md text-sm">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Formatos aceitos: PDF, DOC, DOCX, JPG, JPEG, PNG</p>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                                <i class="fas fa-times mr-1"></i>Cancelar
                            </a>
                            <button type="submit" class="text-white font-medium py-2 px-4 rounded-md transition duration-200 shadow-lg hover:shadow-xl" style="background: #0084de;">
                                <i class="fas fa-save mr-1"></i>Salvar Movimento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adicionarDocumento() {
            const container = document.getElementById('documentos-container');
            const novoItem = document.createElement('div');
            novoItem.className = 'documento-item flex items-center space-x-3';
            novoItem.innerHTML = `
                <input type="file" name="documentos[]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent" style="focus:ring-color: #0084de;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <button type="button" onclick="removerDocumento(this)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(novoItem);
        }

        function removerDocumento(button) {
            button.parentElement.remove();
        }

        // Definir data mínima como hoje
        document.getElementById('data_programada').min = new Date().toISOString().split('T')[0];
        document.getElementById('data_real').min = new Date().toISOString().split('T')[0];
    </script>
</x-app-layout>