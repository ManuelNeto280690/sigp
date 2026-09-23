<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-edit text-white text-xs"></i>
                    </div>
                    {{ __('Editar Escala') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Escala: {{ $escala->numero_escala }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('janela-unica.show', $escala) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-eye mr-2"></i>Visualizar
                </a>
                <a href="{{ route('janela-unica.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Corrija os seguintes erros:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

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

            <form action="{{ route('janela-unica.update', $escala) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Status da Escala -->
                <x-card title="Status da Escala" icon="fas fa-info-circle">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="planejada" {{ $escala->status == 'planejada' ? 'selected' : '' }}>Planejada</option>
                                <option value="confirmada" {{ $escala->status == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                                <option value="atracada" {{ $escala->status == 'atracada' ? 'selected' : '' }}>Atracada</option>
                                <option value="operando" {{ $escala->status == 'operando' ? 'selected' : '' }}>Operando</option>
                                <option value="desatracada" {{ $escala->status == 'desatracada' ? 'selected' : '' }}>Desatracada</option>
                                <option value="cancelada" {{ $escala->status == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Progresso FALs</label>
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-3 mr-3">
                                    <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $escala->progresso_fals }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ $escala->progresso_fals }}%</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $escala->fals_obrigatorios_aprovados }}/7 aprovados</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Última Atualização</label>
                            <p class="text-sm text-gray-900">{{ $escala->updated_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-gray-500">por {{ $escala->createdBy->name ?? 'Sistema' }}</p>
                        </div>
                    </div>
                </x-card>

                <!-- Informações Básicas -->
                <x-card title="Informações Básicas" icon="fas fa-ship">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="embarcacao_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Embarcação <span class="text-red-500">*</span>
                            </label>
                            <select name="embarcacao_id" id="embarcacao_id" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($embarcacoes as $embarcacao)
                                <option value="{{ $embarcacao->id }}" {{ $escala->embarcacao_id == $embarcacao->id ? 'selected' : '' }}>
                                    {{ $embarcacao->nome }} ({{ $embarcacao->imo }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                
                        <div>
                            <label for="tipo_operacao" class="block text-sm font-medium text-gray-700 mb-1">
                                Tipo de Operação <span class="text-red-500">*</span>
                            </label>
                            <select name="tipo_operacao" id="tipo_operacao" required 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="atracacao" {{ $escala->tipo_operacao == 'atracacao' ? 'selected' : '' }}>Atracacao</option>
                                <option value="desatracacao" {{ $escala->tipo_operacao == 'desatracacao' ? 'selected' : '' }}>Desatracacao</option>
                                <option value="transito" {{ $escala->tipo_operacao == 'transito' ? 'selected' : '' }}>Trânsito</option>
                                <option value="transfer" {{ $escala->tipo_operacao == 'transfer' ? 'selected' : '' }}>Transferência</option>
                                <option value="fundeio" {{ $escala->tipo_operacao == 'fundeio' ? 'selected' : '' }}>Fundeio</option>
                                <option value="abast" {{ $escala->tipo_operacao == 'abast' ? 'selected' : '' }}>Abastecimento</option>
                                <option value="reparos" {{ $escala->tipo_operacao == 'reparos' ? 'selected' : '' }}>Reparos</option>
                                <option value="arribada" {{ $escala->tipo_operacao == 'arribada' ? 'selected' : '' }}>Arribada</option>
                                <option value="docagem" {{ $escala->tipo_operacao == 'docagem' ? 'selected' : '' }}>Docagem</option>
                                <option value="layup" {{ $escala->tipo_operacao == 'layup' ? 'selected' : '' }}>Lay-up</option>
                            </select>
                        </div>
                
                        <div>
                            <label for="berco" class="block text-sm font-medium text-gray-700 mb-1">
                                Berço
                            </label>
                            <input type="text" name="berco" id="berco" value="{{ $escala->berco }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Ex: Berço 1">
                        </div>
                
                        <div class="md:col-span-3">
                            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">
                                Descrição
                            </label>
                            <textarea name="descricao" id="descricao" rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Descrição da operação">{{ $escala->descricao }}</textarea>
                        </div>
                    </div>
                </x-card>

                <!-- Programação -->
                <x-card title="Programação" icon="fas fa-calendar-alt">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="eta_previsto" class="block text-sm font-medium text-gray-700 mb-1">
                                ETA Previsto <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="eta_previsto" id="eta_previsto" required
                                   value="{{ $escala->eta_previsto?->format('Y-m-d\TH:i') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="etd_previsto" class="block text-sm font-medium text-gray-700 mb-1">
                                ETD Previsto
                            </label>
                            <input type="datetime-local" name="etd_previsto" id="etd_previsto" 
                                   value="{{ $escala->etd_previsto?->format('Y-m-d\TH:i') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        @if($escala->ata_real)
                        <div>
                            <label for="ata_real" class="block text-sm font-medium text-gray-700 mb-1">
                                ATA Real
                            </label>
                            <input type="datetime-local" name="ata_real" id="ata_real" 
                                   value="{{ $escala->ata_real?->format('Y-m-d\TH:i') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        @endif

                        @if($escala->atd_real)
                        <div>
                            <label for="atd_real" class="block text-sm font-medium text-gray-700 mb-1">
                                ATD Real
                            </label>
                            <input type="datetime-local" name="atd_real" id="atd_real" 
                                   value="{{ $escala->atd_real?->format('Y-m-d\TH:i') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        @endif
                    </div>
                </x-card>

                <!-- Observações -->
                <x-card title="Observações" icon="fas fa-sticky-note">
                    <div>
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">
                            Observações Gerais
                        </label>
                        <textarea name="observacoes" id="observacoes" rows="4" 
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Informações adicionais sobre a escala...">{{ $escala->observacoes }}</textarea>
                    </div>
                </x-card>

                <!-- Botões -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('janela-unica.show', $escala) }}" 
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>