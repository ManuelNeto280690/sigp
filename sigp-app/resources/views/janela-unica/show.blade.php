<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Escala') }} {{ $pedido->numero_escala }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">{{ $pedido->embarcacao->nome }} - {{ $pedido->terminal->nome }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @can('escalas.edit')
                <a href="{{ route('janela-unica.edit', $pedido) }}" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
                @endcan
                <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-print mr-2"></i>Imprimir
                </button>
                <a href="{{ route('janela-unica.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <!-- Resumo da Escala -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Informações Principais -->
                <div class="lg:col-span-2">
                    <x-card title="Informações da Escala" icon="fas fa-info-circle">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Embarcação</h4>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-ship text-blue-600 text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-lg font-medium text-gray-900">{{ $pedido->embarcacao->nome }}</div>
                                        <div class="text-sm text-gray-500">IMO: {{ $pedido->embarcacao->imo }}</div>
                                        <div class="text-sm text-gray-500">Bandeira: {{ $pedido->embarcacao->bandeira }}</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Terminal</h4>
                                <div class="text-lg font-medium text-gray-900">{{ $pedido->terminal->nome }}</div>
                                <div class="text-sm text-gray-500">{{ $pedido->terminal->endereco }}</div>
                                @if($pedido->berco)
                                <div class="text-sm text-gray-500">Berço: {{ $pedido->berco }}</div>
                                @endif
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Operação</h4>
                                <div class="text-lg font-medium text-gray-900">{{ ucfirst($pedido->tipo_operacao) }}</div>
                                <div class="text-sm text-gray-500">Número: {{ $pedido->numero_escala }}</div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-2">Status</h4>
                                <x-badge :type="$pedido->status_escala" class="text-sm">
                                    {{ ucfirst($pedido->status_escala) }}
                                </x-badge>
                                <div class="text-sm text-gray-500 mt-1">
                                    Atualizado em {{ $pedido->updated_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Status dos FALs -->
                <div>
                    <x-card title="Status dos FALs" icon="fas fa-file-alt">
                        <div class="space-y-4">
                            <div class="text-center">
                                <div class="relative inline-flex items-center justify-center w-20 h-20">
                                    <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-gray-300" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                        <path class="text-blue-600" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="{{ $pedido->progresso_fals }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                    </svg>
                                    <span class="absolute text-lg font-semibold text-gray-700">{{ $pedido->progresso_fals }}%</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-2">{{ $pedido->fals_obrigatorios_aprovados }}/7 FALs aprovados</p>
                            </div>

                            <div class="space-y-2">
                                @php
                                    $fals = [
                                        'fal1' => ['nome' => 'FAL 1 - Declaração Geral', 'model' => $pedido->fal1DeclaracaoGeral],
                                        'fal2' => ['nome' => 'FAL 2 - Declaração de Carga', 'model' => $pedido->fal2DeclaracaoCarga],
                                        'fal3' => ['nome' => 'FAL 3 - Provisões de Bordo', 'model' => $pedido->fal3ProvisoesBordo],
                                        'fal4' => ['nome' => 'FAL 4 - Pertences da Tripulação', 'model' => $pedido->fal4PertencessTripulacao],
                                        'fal5' => ['nome' => 'FAL 5 - Lista de Tripulantes', 'model' => $pedido->fal5ListaTripulantes],
                                        'fal6' => ['nome' => 'FAL 6 - Lista de Passageiros', 'model' => $pedido->fal6ListaPassageiros],
                                        'fal7' => ['nome' => 'FAL 7 - Mercadorias Perigosas', 'model' => $pedido->fal7MercadoriasPerigosas]
                                    ];
                                @endphp

                                @foreach($fals as $key => $fal)
                                <div class="flex items-center justify-between p-2 rounded-lg {{ $fal['model'] && $fal['model']->status_aprovacao == 'aprovado' ? 'bg-green-50' : ($fal['model'] && $fal['model']->status_aprovacao == 'rejeitado' ? 'bg-red-50' : 'bg-gray-50') }}">
                                    <div class="flex items-center">
                                        <i class="fas {{ $fal['model'] && $fal['model']->status_aprovacao == 'aprovado' ? 'fa-check-circle text-green-500' : ($fal['model'] && $fal['model']->status_aprovacao == 'rejeitado' ? 'fa-times-circle text-red-500' : 'fa-clock text-yellow-500') }} mr-2"></i>
                                        <span class="text-xs font-medium">{{ $fal['nome'] }}</span>
                                    </div>
                                    <a href="{{ route($key . '.show', $pedido) }}" class="text-xs text-blue-600 hover:text-blue-800">
                                        Ver
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Cronograma -->
            <x-card title="Cronograma" icon="fas fa-calendar-alt">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-500 mb-1">ETA Previsto</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $pedido->eta_previsto?->format('d/m/Y') }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $pedido->eta_previsto?->format('H:i') }}
                        </div>
                    </div>

                    @if($pedido->ata_real)
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-500 mb-1">ATA Real</div>
                        <div class="text-lg font-semibold text-green-600">
                            {{ $pedido->ata_real->format('d/m/Y') }}
                        </div>
                        <div class="text-sm text-green-600">
                            {{ $pedido->ata_real->format('H:i') }}
                        </div>
                    </div>
                    @endif

                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-500 mb-1">ETD Previsto</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $pedido->etd_previsto?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $pedido->etd_previsto?->format('H:i') ?? '' }}
                        </div>
                    </div>

                    @if($pedido->atd_real)
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-500 mb-1">ATD Real</div>
                        <div class="text-lg font-semibold text-blue-600">
                            {{ $pedido->atd_real->format('d/m/Y') }}
                        </div>
                        <div class="text-sm text-blue-600">
                            {{ $pedido->atd_real->format('H:i') }}
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Formulários FAL -->
            <x-card title="Formulários FAL" icon="fas fa-file-alt">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($fals as $key => $fal)
                    <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">{{ $fal['nome'] }}</h4>
                            <x-badge :type="$fal['model'] ? $fal['model']->status_aprovacao : 'pendente'" class="text-xs">
                                {{ $fal['model'] ? ucfirst($fal['model']->status_aprovacao) : 'Pendente' }}
                            </x-badge>
                        </div>
                        
                        @if($fal['model'])
                        <div class="text-sm text-gray-600 mb-3">
                            <p>Última atualização: {{ $fal['model']->updated_at->format('d/m/Y H:i') }}</p>
                            @if($fal['model']->observacoes_aprovacao)
                            <p class="mt-1 text-xs">{{ $fal['model']->observacoes_aprovacao }}</p>
                            @endif
                        </div>
                        @endif

                        <div class="flex space-x-2">
                            <a href="{{ route($key . '.show', $pedido) }}" 
                               class="flex-1 text-center px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                            @can('fals.edit')
                            <a href="{{ route($key . '.edit', $pedido) }}" 
                               class="flex-1 text-center px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Observações -->
            @if($pedido->observacoes)
            <x-card title="Observações" icon="fas fa-sticky-note">
                <div class="prose max-w-none">
                    <p class="text-gray-700">{{ $pedido->observacoes }}</p>
                </div>
            </x-card>
            @endif

            <!-- Histórico de Ações -->
            <x-card title="Histórico de Ações" icon="fas fa-history">
                <div class="space-y-4">
                    @forelse($pedido->auditLogs()->latest()->take(10)->get() as $log)
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle text-gray-400 text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">{{ $log->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <p class="text-sm text-gray-600">{{ $log->action }}</p>
                            @if($log->details)
                            <p class="text-xs text-gray-500 mt-1">{{ $log->details }}</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 py-4">Nenhuma ação registrada</p>
                    @endforelse
                </div>
            </x-card>

        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
    @endpush
</x-app-layout>