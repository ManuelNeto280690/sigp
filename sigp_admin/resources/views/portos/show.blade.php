<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-anchor mr-2 text-blue-600"></i>
                    {{ __('Detalhes do Porto') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $porto->nome }} - Domínio: {{ $porto->dominio }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('portos.edit', $porto) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
                <a href="{{ route('portos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Identificação</h3>
                            <p class="text-sm text-gray-900"><strong>Nome:</strong> {{ $porto->nome }}</p>
                            <p class="text-sm text-gray-900"><strong>Slug:</strong> {{ $porto->slug }}</p>
                            <p class="text-sm text-gray-900"><strong>Domínio:</strong> {{ $porto->dominio }}</p>
                            <p class="text-sm text-gray-900"><strong>Path:</strong> {{ $porto->path }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Contato</h3>
                            <p class="text-sm text-gray-900"><strong>Email:</strong> {{ $porto->email ?? '—' }}</p>
                            <p class="text-sm text-gray-900"><strong>Telefone:</strong> {{ $porto->telefone ?? '—' }}</p>
                            <p class="text-sm text-gray-900"><strong>Endereço:</strong> {{ $porto->endereco ?? '—' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Responsável</h3>
                            <p class="text-sm text-gray-900"><strong>Nome:</strong> {{ $porto->responsavel ?? '—' }}</p>
                            <p class="text-sm text-gray-900"><strong>Telefone:</strong> {{ $porto->telefone_responsavel ?? '—' }}</p>
                            <p class="text-sm text-gray-900"><strong>Funcionários:</strong> {{ $porto->num_funcionarios ?? 0 }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Worker</h3>
                            <p class="text-sm text-gray-900"><strong>Status:</strong> {{ ucfirst($porto->worker_status) }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Por segurança, credenciais de banco não são exibidas nesta página.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>