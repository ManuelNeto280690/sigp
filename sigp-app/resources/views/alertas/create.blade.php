<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);">
                    <i class="fas fa-plus text-white text-sm"></i>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Novo Alerta') }}
                </h2>
            </div>
            <a href="{{ route('alertas.index') }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3b82f6; border: 2px solid #3b82f6;">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
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
            
            <!-- Formulário -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-lg">
                <!-- Cabeçalho -->
                <div class="px-8 py-6 border-b border-gray-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2" style="color: #3b82f6;"></i>
                        Cadastro de Novo Alerta
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Preencha as informações do alerta do sistema</p>
                </div>

                <form action="{{ route('alertas.store') }}" method="POST" class="p-8">
                    @csrf

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle mr-2" style="color: #3b82f6;"></i>
                            Informações Básicas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Título -->
                            <div class="md:col-span-2">
                                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Título do Alerta <span style="color: #3b82f6;">*</span>
                                </label>
                                <input type="text" 
                                       name="titulo" 
                                       id="titulo"
                                       value="{{ old('titulo') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition duration-200 @error('titulo') border-red-500 @enderror"
                                       style="focus:ring-color: #3b82f6;"
                                       placeholder="Ex: Falha no sistema de monitoramento"
                                       required>
                                @error('titulo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo do Alerta <span style="color: #3b82f6;">*</span>
                                </label>
                                <select name="tipo" 
                                        id="tipo"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition duration-200 @error('tipo') border-red-500 @enderror"
                                        style="focus:ring-color: #3b82f6;"
                                        required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="operacional" {{ old('tipo') == 'operacional' ? 'selected' : '' }}>Operacional</option>
                                    <option value="seguranca" {{ old('tipo') == 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="ambiental" {{ old('tipo') == 'ambiental' ? 'selected' : '' }}>Ambiental</option>
                                    <option value="manutencao" {{ old('tipo') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                                    <option value="sistema" {{ old('tipo') == 'sistema' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('tipo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nível -->
                            <div>
                                <label for="nivel" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nível <span style="color: #3b82f6;">*</span>
                                </label>
                                <select name="nivel" 
                                        id="nivel"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition duration-200 @error('nivel') border-red-500 @enderror"
                                        style="focus:ring-color: #3b82f6;"
                                        required>
                                    <option value="">Selecione o nível</option>
                                    <option value="baixa" {{ old('nivel') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                    <option value="media" {{ old('nivel') == 'media' ? 'selected' : '' }}>Média</option>
                                    <option value="alta" {{ old('nivel') == 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="critica" {{ old('nivel') == 'critica' ? 'selected' : '' }}>Crítica</option>
                                </select>
                                @error('nivel')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descrição -->
                            <div class="md:col-span-2">
                                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrição do Alerta <span style="color: #3b82f6;">*</span>
                                </label>
                                <textarea name="descricao" 
                                          id="descricao"
                                          rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition duration-200 @error('descricao') border-red-500 @enderror"
                                          style="focus:ring-color: #3b82f6;"
                                          placeholder="Descreva detalhadamente o alerta, incluindo contexto e impactos..."
                                          required>{{ old('descricao') }}</textarea>
                                @error('descricao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Configurações Adicionais -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cog mr-2" style="color: #3b82f6;"></i>
                            Configurações Adicionais
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Data Início -->
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de Início
                                </label>
                                <input type="datetime-local" 
                                       name="data_inicio" 
                                       id="data_inicio"
                                       value="{{ old('data_inicio') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition duration-200 @error('data_inicio') border-red-500 @enderror"
                                       style="focus:ring-color: #3b82f6;">
                                @error('data_inicio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Data e hora de início do alerta</p>
                            </div>

                            <!-- Data Fim -->
                            <div>
                                <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de Fim
                                </label>
                                <input type="datetime-local" 
                                       name="data_fim" 
                                       id="data_fim"
                                       value="{{ old('data_fim') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition duration-200 @error('data_fim') border-red-500 @enderror"
                                       style="focus:ring-color: #3b82f6;">
                                @error('data_fim')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Data e hora de fim do alerta (opcional)</p>
                            </div>

                            <!-- Áreas Afetadas -->
                            <div>
                                <label for="areas_afetadas" class="block text-sm font-medium text-gray-700 mb-2">
                                    Áreas Afetadas
                                </label>
                                <input type="text" 
                                       name="areas_afetadas" 
                                       id="areas_afetadas"
                                       value="{{ old('areas_afetadas.0') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition duration-200 @error('areas_afetadas') border-red-500 @enderror"
                                       style="focus:ring-color: #3b82f6;"
                                       placeholder="Ex: Terminal 1, Cais 3, Armazém A">
                                @error('areas_afetadas')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Separe múltiplas áreas com vírgula</p>
                            </div>

                            <!-- Ações Tomadas -->
                            <div>
                                <label for="acoes_tomadas" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ações Tomadas
                                </label>
                                <textarea name="acoes_tomadas" 
                                          id="acoes_tomadas"
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition duration-200 @error('acoes_tomadas') border-red-500 @enderror"
                                          style="focus:ring-color: #3b82f6;"
                                          placeholder="Descreva as ações já tomadas ou planejadas...">{{ old('acoes_tomadas') }}</textarea>
                                @error('acoes_tomadas')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notificar Usuários -->
                            <div class="md:col-span-2">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="notificar_usuarios" 
                                           id="notificar_usuarios"
                                           value="1"
                                           {{ old('notificar_usuarios') ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 focus:ring-2 transition duration-200"
                                           style="color: #3b82f6; focus:ring-color: #3b82f6;">
                                    <label for="notificar_usuarios" class="ml-2 block text-sm text-gray-700">
                                        Notificar usuários sobre este alerta
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Enviar notificações por email para usuários relevantes</p>
                            </div>

                            <!-- Indicadores Visuais de Nível -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Indicador Visual de Nível
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    <div class="flex items-center p-3 border border-blue-200 rounded-lg bg-blue-50">
                                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                        <span class="text-sm text-blue-700">Baixa</span>
                                    </div>
                                    <div class="flex items-center p-3 border border-yellow-200 rounded-lg bg-yellow-50">
                                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                        <span class="text-sm text-yellow-700">Média</span>
                                    </div>
                                    <div class="flex items-center p-3 border border-orange-200 rounded-lg bg-orange-50">
                                        <div class="w-3 h-3 bg-orange-500 rounded-full mr-2"></div>
                                        <span class="text-sm text-orange-700">Alta</span>
                                    </div>
                                    <div class="flex items-center p-3 border border-red-200 rounded-lg bg-red-50">
                                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                        <span class="text-sm text-red-700">Crítica</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            Campos marcados com <span style="color: #3b82f6;">*</span> são obrigatórios
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('alertas.index') }}" 
                               class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 transition duration-200"
                               style="focus:ring-color: #3b82f6;">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                    style="background-color: #3b82f6; focus:ring-color: #3b82f6;"
                                    onmouseover="this.style.backgroundColor='#2563eb'" 
                                    onmouseout="this.style.backgroundColor='#3b82f6'">
                                <i class="fas fa-save mr-2"></i>Criar Alerta
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Atualizar indicador visual baseado no nível selecionado
        document.getElementById('nivel').addEventListener('change', function() {
            const nivel = this.value;
            const indicadores = document.querySelectorAll('[class*="border-"][class*="-200"]');
            
            // Reset all indicators
            indicadores.forEach(ind => {
                ind.classList.remove('ring-2', 'ring-blue-500', 'ring-yellow-500', 'ring-orange-500', 'ring-red-500');
            });
            
            // Highlight selected priority
            if (nivel) {
                const colorMap = {
                    'baixa': 'blue',
                    'media': 'yellow', 
                    'alta': 'orange',
                    'critica': 'red'
                };
                
                const color = colorMap[nivel];
                if (color) {
                    const indicator = document.querySelector(`[class*="border-${color}-200"]`);
                    if (indicator) {
                        indicator.classList.add('ring-2', `ring-${color}-500`);
                    }
                }
            }
        });

        // Validação de data fim
        document.getElementById('data_fim').addEventListener('change', function() {
            const dataInicio = document.getElementById('data_inicio').value;
            const dataFim = this.value;
            
            if (dataInicio && dataFim && new Date(dataFim) <= new Date(dataInicio)) {
                alert('A data de fim deve ser posterior à data de início.');
                this.value = '';
            }
        });

        // Auto-resize textarea
        document.getElementById('descricao').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        document.getElementById('acoes_tomadas').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Converter áreas afetadas em array
        document.querySelector('form').addEventListener('submit', function(e) {
            const areasInput = document.getElementById('areas_afetadas');
            if (areasInput.value) {
                const areas = areasInput.value.split(',').map(area => area.trim()).filter(area => area);
                areasInput.name = 'areas_afetadas';
                areasInput.value = JSON.stringify(areas);
            }
        });
    </script>
</x-app-layout>