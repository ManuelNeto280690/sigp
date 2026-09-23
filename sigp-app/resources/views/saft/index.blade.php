<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
            <div class="w-6 h-6 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                <i class="fas fa-file-code text-white text-xs"></i>
            </div>
            Exportação SAF-T (AO)
        </h2>
        <p class="text-gray-600 text-xs mt-1">Gere o ficheiro XML de auditoria exigido pela AGT</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-10 bg-white border-b border-gray-200">
                    
                    <div class="mb-8 flex items-start p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-medium text-blue-800">O que é o SAF-T (AO)?</h3>
                            <p class="text-sm text-blue-600 mt-1">O Standard Audit File for Tax (SAF-T) é um ficheiro no formato XML contendo os dados de faturação num formato normalizado, que deve ser submetido mensalmente à AGT.</p>
                        </div>
                    </div>

                    <form action="{{ route('saft.export') }}" method="GET" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="mes" class="block text-sm font-medium text-gray-700">Mês</label>
                                <select id="mes" name="mes" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($i)->locale('pt')->monthName }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div>
                                <label for="ano" class="block text-sm font-medium text-gray-700">Ano</label>
                                <select id="ano" name="ano" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" required>
                                    @for($i = date('Y'); $i >= 2020; $i--)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="pt-5 flex items-center justify-end border-t border-gray-200">
                            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-download mr-2"></i>
                                Gerar e Baixar SAF-T
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
