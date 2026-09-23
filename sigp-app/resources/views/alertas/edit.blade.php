<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);">
                    <i class="fas fa-edit text-white text-sm"></i>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Editar Alerta') }}
                </h2>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('alertas.show', $alerta) }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #10b981; border: 2px solid #10b981;">
                    <i class="fas fa-eye mr-2"></i>Visualizar
                </a>
                <a href="{{ route('alertas.index') }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3b82f6; border: 2px solid #3b82f6;">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
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

            <!-- Status do Alerta -->
            @if($alerta->status === 'resolvido')
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-yellow-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">Este alerta já foi resolvido e não pode ser editado.</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Formulário -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-lg">
                <!-- Cabeçalho -->
                <div class="px-8 py-6 border-b border-gray-100" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-exclamation-triangle text-blue-600 mr-2"></i>
                        Editar Alerta: {{ $alerta->titulo }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Atualize as informações do alerta do sistema</p>
                </div>

                <form action="{{ route('alertas.update', $alerta) }}" method="POST" class="p-8">
                    @csrf
                    @method('PUT')

                    <!-- Informações Básicas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Básicas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Título -->
                            <div class="md:col-span-2">
                                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Título do Alerta <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="titulo" 
                                       id="titulo"
                                       value="{{ old('titulo', $alerta->titulo) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('titulo') border-red-500 @enderror"
                                       placeholder="Ex: Falha no sistema de monitoramento"
                                       required>
                                @error('titulo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo do Alerta <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo" 
                                        id="tipo"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('tipo') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="operacional" {{ old('tipo', $alerta->tipo) === 'operacional' ? 'selected' : '' }}>Operacional</option>
                                    <option value="seguranca" {{ old('tipo', $alerta->tipo) === 'seguranca' ? 'selected' : '' }}>Segurança</option>
                                    <option value="ambiental" {{ old('tipo', $alerta->tipo) === 'ambiental' ? 'selected' : '' }}>Ambiental</option>
                                    <option value="manutencao" {{ old('tipo', $alerta->tipo) === 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                                    <option value="outro" {{ old('tipo', $alerta->tipo) === 'outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('tipo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nível -->
                            <div>
                                <label for="nivel" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nível <span class="text-red-500">*</span>
                                </label>
                                <select name="nivel" 
                                        id="nivel"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('nivel') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione o nível</option>
                                    <option value="baixa" {{ old('nivel', $alerta->nivel) === 'baixa' ? 'selected' : '' }}>Baixa</option>
                                    <option value="media" {{ old('nivel', $alerta->nivel) === 'media' ? 'selected' : '' }}>Média</option>
                                    <option value="alta" {{ old('nivel', $alerta->nivel) === 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="critica" {{ old('nivel', $alerta->nivel) === 'critica' ? 'selected' : '' }}>Crítica</option>
                                </select>
                                @error('nivel')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" 
                                        id="status"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('status') border-red-500 @enderror"
                                        required>
                                    <option value="">Selecione o status</option>
                                    <option value="activo" {{ old('status', $alerta->status) === 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="resolvido" {{ old('status', $alerta->status) === 'resolvido' ? 'selected' : '' }}>Resolvido</option>
                                    <option value="cancelado" {{ old('status', $alerta->status) === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descrição -->
                            <div class="md:col-span-2">
                                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descrição <span class="text-red-500">*</span>
                                </label>
                                <textarea name="descricao" 
                                          id="descricao"
                                          rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('descricao') border-red-500 @enderror"
                                          placeholder="Descreva detalhadamente o alerta..."
                                          required>{{ old('descricao', $alerta->descricao) }}</textarea>
                                @error('descricao')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Período e Áreas -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-calendar text-blue-600 mr-2"></i>
                            Período e Áreas Afetadas
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Data de Início -->
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de Início
                                </label>
                                <input type="datetime-local" 
                                       name="data_inicio" 
                                       id="data_inicio"
                                       value="{{ old('data_inicio', $alerta->data_inicio ? $alerta->data_inicio->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_inicio') border-red-500 @enderror">
                                @error('data_inicio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data de Fim -->
                            <div>
                                <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data de Fim
                                </label>
                                <input type="datetime-local" 
                                       name="data_fim" 
                                       id="data_fim"
                                       value="{{ old('data_fim', $alerta->data_fim ? $alerta->data_fim->format('Y-m-d\TH:i') : '') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('data_fim') border-red-500 @enderror">
                                @error('data_fim')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Áreas Afetadas -->
                            <div class="md:col-span-2">
                                <label for="areas_afetadas" class="block text-sm font-medium text-gray-700 mb-2">
                                    Áreas Afetadas
                                </label>
                                <textarea name="areas_afetadas" 
                                          id="areas_afetadas"
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('areas_afetadas') border-red-500 @enderror"
                                          placeholder="Liste as áreas afetadas pelo alerta (uma por linha)">{{ old('areas_afetadas', is_array($alerta->areas_afetadas) ? implode("\n", $alerta->areas_afetadas) : $alerta->areas_afetadas) }}</textarea>
                                @error('areas_afetadas')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Ações e Configurações -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cog text-blue-600 mr-2"></i>
                            Ações e Configurações
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Ações Tomadas -->
                            <div class="md:col-span-2">
                                <label for="acoes_tomadas" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ações Tomadas
                                </label>
                                <textarea name="acoes_tomadas" 
                                          id="acoes_tomadas"
                                          rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('acoes_tomadas') border-red-500 @enderror"
                                          placeholder="Descreva as ações já tomadas para resolver o alerta...">{{ old('acoes_tomadas', $alerta->acoes_tomadas) }}</textarea>
                                @error('acoes_tomadas')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notificar Usuários -->
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="notificar_usuarios" 
                                           value="1"
                                           {{ old('notificar_usuarios', $alerta->notificar_usuarios) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Notificar usuários sobre este alerta</span>
                                </label>
                            </div>

                            <!-- Indicador Visual de Nível -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Indicador Visual
                                </label>
                                <div id="nivel-indicator" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-center font-medium">
                                    Selecione um nível
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('alertas.index') }}" 
                           class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        
                        @if($alerta->status !== 'resolvido')
                            <button type="submit" 
                                    class="w-full sm:w-auto px-6 py-3 text-white rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                    style="background: #3b82f6;">
                                <i class="fas fa-save mr-2"></i>Atualizar Alerta
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript para funcionalidades interativas -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nivelSelect = document.getElementById('nivel');
            const nivelIndicator = document.getElementById('nivel-indicator');
            const descricaoTextarea = document.getElementById('descricao');
            const areasAfetadasTextarea = document.getElementById('areas_afetadas');
            const acoesTomadasTextarea = document.getElementById('acoes_tomadas');
            const dataInicioInput = document.getElementById('data_inicio');
            const dataFimInput = document.getElementById('data_fim');

            // Função para atualizar o indicador visual de nível
            function updateNivelIndicator() {
                const nivel = nivelSelect.value;
                const colors = {
                    'baixa': { bg: 'bg-green-100', text: 'text-green-800', border: 'border-green-300' },
                    'media': { bg: 'bg-yellow-100', text: 'text-yellow-800', border: 'border-yellow-300' },
                    'alta': { bg: 'bg-orange-100', text: 'text-orange-800', border: 'border-orange-300' },
                    'critica': { bg: 'bg-red-100', text: 'text-red-800', border: 'border-red-300' }
                };

                if (nivel && colors[nivel]) {
                    const color = colors[nivel];
                    nivelIndicator.className = `w-full px-4 py-3 rounded-lg text-center font-medium border-2 ${color.bg} ${color.text} ${color.border}`;
                    nivelIndicator.textContent = nivel.charAt(0).toUpperCase() + nivel.slice(1);
                } else {
                    nivelIndicator.className = 'w-full px-4 py-3 border border-gray-300 rounded-lg text-center font-medium text-gray-500';
                    nivelIndicator.textContent = 'Selecione um nível';
                }
            }

            // Auto-resize dos textareas
            function autoResize(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            }

            // Validação das datas
            function validateDatas() {
                const dataInicio = new Date(dataInicioInput.value);
                const dataFim = new Date(dataFimInput.value);
                
                if (dataInicioInput.value && dataFimInput.value && dataFim <= dataInicio) {
                    dataFimInput.setCustomValidity('A data de fim deve ser posterior à data de início');
                } else {
                    dataFimInput.setCustomValidity('');
                }
            }

            // Event listeners
            nivelSelect.addEventListener('change', updateNivelIndicator);
            descricaoTextarea.addEventListener('input', () => autoResize(descricaoTextarea));
            areasAfetadasTextarea.addEventListener('input', () => autoResize(areasAfetadasTextarea));
            acoesTomadasTextarea.addEventListener('input', () => autoResize(acoesTomadasTextarea));
            dataInicioInput.addEventListener('change', validateDatas);
            dataFimInput.addEventListener('change', validateDatas);

            // Inicializar
            updateNivelIndicator();
            autoResize(descricaoTextarea);
            autoResize(areasAfetadasTextarea);
            autoResize(acoesTomadasTextarea);

            // Converter áreas afetadas em array no envio do formulário
            document.querySelector('form').addEventListener('submit', function() {
                const areasValue = areasAfetadasTextarea.value;
                if (areasValue.trim()) {
                    const areasArray = areasValue.split('\n').filter(area => area.trim() !== '');
                    areasAfetadasTextarea.value = JSON.stringify(areasArray);
                }
            });
        });
    </script>
</x-app-layout>