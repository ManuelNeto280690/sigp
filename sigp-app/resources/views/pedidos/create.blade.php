<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-plus text-white text-xs"></i>
                    </div>
                    {{ __('Novo Pedido') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Crie um novo pedido no sistema</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('pedidos.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
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

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-red-800">
                                <p class="mb-2">Por favor, corrija os seguintes erros:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <form action="{{ route('pedidos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <!-- Informações do Navio -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-ship text-blue-500 mr-2"></i>
                            Informações do Navio
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label for="nome_navio" class="block text-sm font-medium text-gray-700 mb-1">Nome do Navio *</label>
                                <input type="text" name="nome_navio" id="nome_navio" value="{{ old('nome_navio') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            
                            <div>
                                <label for="numero_imo" class="block text-sm font-medium text-gray-700 mb-1">Número IMO *</label>
                                <input type="text" name="numero_imo" id="numero_imo" value="{{ old('numero_imo') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            
                            <div>
                                <label for="indicativo_chamada" class="block text-sm font-medium text-gray-700 mb-1">Indicativo de Chamada *</label>
                                <input type="text" name="indicativo_chamada" id="indicativo_chamada" value="{{ old('indicativo_chamada') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            
                            <div>
                                <label for="numero_viagem" class="block text-sm font-medium text-gray-700 mb-1">Número da Viagem *</label>
                                <input type="text" name="numero_viagem" id="numero_viagem" value="{{ old('numero_viagem') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            
                            <div>
                                <label for="bandeira_navio" class="block text-sm font-medium text-gray-700 mb-1">Bandeira do Navio *</label>
                                <select name="bandeira_navio" id="bandeira_navio" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                    <option value="">Selecione a bandeira</option>
                                    @foreach(\App\Helpers\CountryHelper::getCountriesForSelect() as $code => $country)
                                        <option value="{{ $country }}" {{ old('bandeira_navio') == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Datas e Horários -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-calendar text-blue-500 mr-2"></i>
                            Datas e Horários
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="data_chegada" class="block text-sm font-medium text-gray-700 mb-1">Data de Chegada *</label>
                                <input type="date" name="data_chegada" id="data_chegada" value="{{ old('data_chegada') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            
                            <div>
                                <label for="hora_chegada" class="block text-sm font-medium text-gray-700 mb-1">Hora de Chegada *</label>
                                <input type="time" name="hora_chegada" id="hora_chegada" value="{{ old('hora_chegada') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            
                            <div>
                                <label for="data_partida" class="block text-sm font-medium text-gray-700 mb-1">Data de Partida</label>
                                <input type="date" name="data_partida" id="data_partida" value="{{ old('data_partida') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="hora_partida" class="block text-sm font-medium text-gray-700 mb-1">Hora de Partida</label>
                                <input type="time" name="hora_partida" id="hora_partida" value="{{ old('hora_partida') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Informações do Agente -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user-tie text-blue-500 mr-2"></i>
                            Informações do Agente
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="nome_agente" class="block text-sm font-medium text-gray-700 mb-1">Nome do Agente *</label>
                                <input type="text" name="nome_agente" id="nome_agente" value="{{ old('nome_agente') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            
                            <div>
                                <label for="contato_agente" class="block text-sm font-medium text-gray-700 mb-1">Contato do Agente *</label>
                                <input type="text" name="contato_agente" id="contato_agente" value="{{ old('contato_agente') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                        </div>
                    </div>

                    <!-- Tripulação e Passageiros -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-users text-blue-500 mr-2"></i>
                            Tripulação e Passageiros
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="numero_tripulantes" class="block text-sm font-medium text-gray-700 mb-1">Número de Tripulantes *</label>
                                <input type="number" name="numero_tripulantes" id="numero_tripulantes" value="{{ old('numero_tripulantes') }}" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            
                            <div>
                                <label for="numero_passageiros" class="block text-sm font-medium text-gray-700 mb-1">Número de Passageiros</label>
                                <input type="number" name="numero_passageiros" id="numero_passageiros" value="{{ old('numero_passageiros') }}" min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-comment text-blue-500 mr-2"></i>
                            Observações
                        </h3>
                        
                        <div>
                            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                            <textarea name="observacoes" id="observacoes" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('observacoes') }}</textarea>
                        </div>
                    </div>

                    <!-- Upload de Documentos -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-file-upload text-blue-500 mr-2"></i>
                            Upload de Documentos
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="certificados_navio" class="block text-sm font-medium text-gray-700 mb-2">Certificados do Navio</label>
                                <input type="file" name="certificados_navio[]" id="certificados_navio" multiple accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG</p>
                            </div>
                            
                            <div>
                                <label for="declaracoes_cargas" class="block text-sm font-medium text-gray-700 mb-2">Declarações de Cargas</label>
                                <input type="file" name="declaracoes_cargas[]" id="declaracoes_cargas" multiple accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG</p>
                            </div>
                            
                            <div>
                                <label for="declaracao_provisoes" class="block text-sm font-medium text-gray-700 mb-2">Declaração de Provisões de Bordo</label>
                                <input type="file" name="declaracao_provisoes[]" id="declaracao_provisoes" multiple accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG</p>
                            </div>
                            
                            <div>
                                <label for="declaracao_pertences" class="block text-sm font-medium text-gray-700 mb-2">Declaração de Pertences da Tripulação</label>
                                <input type="file" name="declaracao_pertences[]" id="declaracao_pertences" multiple accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG</p>
                            </div>
                            
                            <div>
                                <label for="documentos_tripulantes" class="block text-sm font-medium text-gray-700 mb-2">Documentos de Identificação de Tripulantes</label>
                                <input type="file" name="documentos_tripulantes[]" id="documentos_tripulantes" multiple accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG</p>
                            </div>
                            
                            <div>
                                <label for="documentos_passageiros" class="block text-sm font-medium text-gray-700 mb-2">Documentos de Identificação de Passageiros</label>
                                <input type="file" name="documentos_passageiros[]" id="documentos_passageiros" multiple accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG</p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="declaracao_mercadorias_perigosas" class="block text-sm font-medium text-gray-700 mb-2">Declaração de Mercadorias Perigosas</label>
                                <input type="file" name="declaracao_mercadorias_perigosas[]" id="declaracao_mercadorias_perigosas" multiple accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-xs text-gray-500 mt-1">Formatos aceitos: PDF, JPG, PNG (máx. 10MB por arquivo)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="p-6 bg-gray-50">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('pedidos.index') }}" 
                               class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-1"></i>
                                Salvar Pedido
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
     @push('scripts')
     <script>
document.addEventListener('DOMContentLoaded', function() {
    // Função simples para mostrar arquivos selecionados
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Remover display anterior se existir
            const existingDisplay = this.parentNode.querySelector('.file-info');
            if (existingDisplay) {
                existingDisplay.remove();
            }
            
            // Mostrar arquivos selecionados
            if (this.files.length > 0) {
                const fileInfo = document.createElement('div');
                fileInfo.className = 'file-info mt-2 p-2 bg-green-50 border border-green-200 rounded text-sm';
                
                let fileList = '<strong>✓ Arquivos selecionados:</strong><br>';
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                    fileList += `• ${file.name} (${sizeMB} MB)<br>`;
                }
                
                fileInfo.innerHTML = fileList;
                this.parentNode.appendChild(fileInfo);
            }
        });
    });
    
    // Mostrar aviso sobre arquivos perdidos em caso de erro
    @if($errors->any())
        const errorMessage = document.createElement('div');
        errorMessage.className = 'mb-4 p-4 bg-orange-50 border border-orange-200 rounded-lg';
        errorMessage.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                <strong class="text-orange-800">Atenção:</strong>
                <span class="text-orange-700 ml-1">Os arquivos selecionados foram perdidos devido ao erro. Por favor, selecione-os novamente.</span>
            </div>
        `;
        
        const form = document.querySelector('form');
        form.parentNode.insertBefore(errorMessage, form);
    @endif
    
    // Indicador de envio
    const form = document.querySelector('form');
    const submitButton = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function() {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando pedido...';
    });
});
</script>
     @endpush
</x-app-layout>

