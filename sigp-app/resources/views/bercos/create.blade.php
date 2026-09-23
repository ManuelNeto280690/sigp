
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-anchor text-white text-xs"></i>
                    </div>
                    {{ __('Novo Berço') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Cadastre um berço vinculado a um terminal</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('bercos.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>Voltar
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
                            <h3 class="text-sm font-medium text-red-800 mb-2">Corrija os campos abaixo:</h3>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('bercos.store') }}" class="p-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">Terminal *</label>
                            <select name="terminal_id" id="terminal_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                    style="focus:ring-color: #3b82f6;">
                                <option value="">Selecione o terminal</option>
                                @foreach($terminais as $terminal)
                                    <option value="{{ $terminal->id }}" {{ old('terminal_id') == $terminal->id ? 'selected' : '' }}>
                                        {{ $terminal->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                            <input type="text" name="nome" id="nome" value="{{ old('nome') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                   style="focus:ring-color: #3b82f6;" placeholder="Ex: Berço 1, Berço A">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="calado_maximo" class="block text-sm font-medium text-gray-700 mb-2">Calado Máximo (m)</label>
                            <input type="number" step="0.01" min="0" name="calado_maximo" id="calado_maximo" value="{{ old('calado_maximo') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                   style="focus:ring-color: #3b82f6;" placeholder="Ex: 12.50">
                        </div>

                        <div>
                            <label for="comprimento_maximo" class="block text-sm font-medium text-gray-700 mb-2">Comprimento Máximo (m)</label>
                            <input type="number" step="0.01" min="0" name="comprimento_maximo" id="comprimento_maximo" value="{{ old('comprimento_maximo') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                   style="focus:ring-color: #3b82f6;" placeholder="Ex: 300.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" id="status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                    style="focus:ring-color: #3b82f6;">
                                <option value="disponivel" {{ old('status', 'disponivel') == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                                <option value="ocupado" {{ old('status') == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                                <option value="manutencao" {{ old('status') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('bercos.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                            Salvar Berço
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>