
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-industry text-white text-xs"></i>
                    </div>
                    {{ __('Editar Guindaste') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">{{ $guindaste->nome }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('guindastes.show', $guindaste) }}" class="text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 text-xs" style="background: #3395da;">
                    <i class="fas fa-eye mr-1 text-xs"></i>Visualizar
                </a>
                <a href="{{ route('guindastes.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 text-xs">
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
                <form method="POST" action="{{ route('guindastes.update', $guindaste) }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="terminal_id" class="block text-sm font-medium text-gray-700 mb-2">Terminal *</label>
                            <select name="terminal_id" id="terminal_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                    style="focus:ring-color: #3b82f6;">
                                <option value="">Selecione o terminal</option>
                                @foreach($terminais as $terminal)
                                    <option value="{{ $terminal->id }}" {{ old('terminal_id', $guindaste->terminal_id) == $terminal->id ? 'selected' : '' }}>
                                        {{ $terminal->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                            <input type="text" name="nome" id="nome" value="{{ old('nome', $guindaste->nome) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                   style="focus:ring-color: #3b82f6;">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                            <select name="tipo" id="tipo" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                    style="focus:ring-color: #3b82f6;">
                                @foreach(['container','graneis_solidos','graneis_liquidos','carga_geral','passageiros','tanque','ro_ro','frigorifico'] as $t)
                                    <option value="{{ $t }}" {{ old('tipo', $guindaste->tipo) == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" id="status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:border-transparent"
                                    style="focus:ring-color: #3b82f6;">
                                <option value="disponivel" {{ old('status', $guindaste->status) == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                                <option value="ocupado" {{ old('status', $guindaste->status) == 'ocupado' ? 'selected' : '' }}>Ocupado</option>
                                <option value="manutencao" {{ old('status', $guindaste->status) == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('guindastes.show', $guindaste) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>