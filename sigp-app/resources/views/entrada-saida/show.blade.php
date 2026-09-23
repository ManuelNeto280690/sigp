<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-ship text-white text-xs"></i>
                    </div>
                    {{ __('Detalhes do Movimento') }} #{{ $movimento->numero_movimento }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Visualize todas as informações do movimento</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('entrada-saida-embarcacao.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
                
                <a href="{{ route('entrada-saida-embarcacao.exportPdf', $movimento) }}" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-file-pdf mr-1"></i>PDF
                </a>
                
                @if($movimento->podeEditar())
                    <a href="{{ route('entrada-saida-embarcacao.edit', $movimento) }}" class="text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs" style="background: #0084de;">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </a>
                @endif
                
            @if($movimento->podeAutorizar())
                @if(auth()->user()->can('autorizar-movimentos'))
                <form action="{{ route('entrada-saida-embarcacao.autorizar', $movimento) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja autorizar este movimento?')">
                        <i class="fas fa-check mr-1"></i>Autorizar
                    </button>
                </form>
                @else
                <button type="button" class="bg-green-300 text-white px-4 py-2 rounded-lg text-sm font-medium opacity-60 cursor-not-allowed">
                    <i class="fas fa-check mr-1"></i>Autorizar
                </button>
                @endif
            @endif
            @if($movimento->podeIniciar())
                @if(auth()->user()->can('iniciar-movimentos'))
                <form action="{{ route('entrada-saida-embarcacao.iniciar', $movimento) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja iniciar este movimento?')">
                        <i class="fas fa-play mr-1"></i>Iniciar
                    </button>
                </form>
                @else
                <button type="button" class="bg-yellow-300 text-white px-4 py-2 rounded-lg text-sm font-medium opacity-60 cursor-not-allowed">
                    <i class="fas fa-play mr-1"></i>Iniciar
                </button>
                @endif
            @endif
            @if($movimento->podeConcluir())
                @if(auth()->user()->can('concluir-movimentos'))
                <form action="{{ route('entrada-saida-embarcacao.concluir', $movimento) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja concluir este movimento?')">
                        <i class="fas fa-flag-checkered mr-1"></i>Concluir
                    </button>
                </form>
                @else
                <button type="button" class="bg-purple-300 text-white px-4 py-2 rounded-lg text-sm font-medium opacity-60 cursor-not-allowed">
                    <i class="fas fa-flag-checkered mr-1"></i>Concluir
                </button>
                @endif
            @endif
        </div>
    </x-slot>

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

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Status e Ações Rápidas -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-info-circle mr-2" style="color: #0084de;"></i>
                                Status do Movimento
                            </h3>
                        </div>
                        <div class="flex items-center space-x-3">
                            @php
                                $statusColors = [
                                    'programado' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'autorizado' => 'bg-green-100 text-green-800 border-green-200',
                                    'em_andamento' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'concluido' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'cancelado' => 'bg-red-100 text-red-800 border-red-200'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold border {{ $statusColors[$movimento->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                {{ $movimento->getStatusLabel() }}
                            </span>
                        </div>
                         <a href="{{ route('entrada-saida-embarcacao.exportPdf', $movimento) }}" class="bg-red-500 hover:bg-red-600 text-white font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-file-pdf mr-1"></i>PDF
                </a>
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-md {{ $movimento->tipo_movimento == 'entrada' ? 'bg-green-500' : 'bg-red-500' }}">
                                <i class="fas {{ $movimento->tipo_movimento == 'entrada' ? 'fa-arrow-down' : 'fa-arrow-up' }} text-white text-lg"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900">{{ ucfirst($movimento->tipo_movimento) }} de Embarcação</h4>
                                <p class="text-gray-600">Movimento #{{ $movimento->numero_movimento }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if($movimento->podeAutorizar())
                                @if(auth()->user()->can('autorizar-movimentos'))
                                <form action="{{ route('entrada-saida-embarcacao.autorizar', $movimento) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja autorizar este movimento?')">
                                        <i class="fas fa-check mr-1"></i>Autorizar
                                    </button>
                                </form>
                                @else
                                <button type="button" class="bg-green-300 text-white px-4 py-2 rounded-lg text-sm font-medium opacity-60 cursor-not-allowed">
                                    <i class="fas fa-check mr-1"></i>Autorizar
                                </button>
                                @endif
                            @endif
                            @if($movimento->podeIniciar())
                                @if(auth()->user()->can('iniciar-movimentos'))
                                <form action="{{ route('entrada-saida-embarcacao.iniciar', $movimento) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja iniciar este movimento?')">
                                        <i class="fas fa-play mr-1"></i>Iniciar
                                    </button>
                                </form>
                                @else
                                <button type="button" class="bg-yellow-300 text-white px-4 py-2 rounded-lg text-sm font-medium opacity-60 cursor-not-allowed">
                                    <i class="fas fa-play mr-1"></i>Iniciar
                                </button>
                                @endif
                            @endif
                            @if(Auth::user()->hasAnyRole(['admin', 'operador']))
                                @if($movimento->podeAtracar())
                                    <form action="{{ route('entrada-saida-embarcacao.atracar', $movimento) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja confirmar a atracação?')">
                                            <i class="fas fa-anchor mr-1"></i>Atracar
                                        </button>
                                    </form>
                                @endif

                                @if($movimento->podeDesatracar())
                                    <button type="button" onclick="openDesatracarModal()" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                        <i class="fas fa-ship mr-1"></i>Desatracar
                                    </button>
                                @endif
                            @endif

                            @if($movimento->podeConcluir())
                                @if(auth()->user()->can('concluir-movimentos'))
                                <form action="{{ route('entrada-saida-embarcacao.concluir', $movimento) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200" onclick="return confirm('Deseja concluir este movimento?')">
                                        <i class="fas fa-flag-checkered mr-1"></i>Concluir
                                    </button>
                                </form>
                                @else
                                <button type="button" class="bg-purple-300 text-white px-4 py-2 rounded-lg text-sm font-medium opacity-60 cursor-not-allowed">
                                    <i class="fas fa-flag-checkered mr-1"></i>Concluir
                                </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações Principais -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Dados da Embarcação -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-ship mr-2" style="color: #0084de;"></i>
                            Embarcação
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($movimento->embarcacao)
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $movimento->embarcacao->nome }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                        <p class="text-gray-900">{{ $movimento->embarcacao->tipo ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bandeira</label>
                                        <p class="text-gray-900">{{ $movimento->embarcacao->bandeira ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Comprimento</label>
                                        <p class="text-gray-900">{{ $movimento->embarcacao->comprimento ? $movimento->embarcacao->comprimento . 'm' : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Calado</label>
                                        <p class="text-gray-900">{{ $movimento->embarcacao->calado ? $movimento->embarcacao->calado . 'm' : 'N/A' }}</p>
                                    </div>
                            </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estado da Embarcação</label>
                                    <p class="text-gray-900">{{ $movimento->estado_embarcacao ? ucfirst($movimento->estado_embarcacao) : 'N/A' }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500">Informações da embarcação não disponíveis</p>
                        @endif
                    </div>
                </div>

                <!-- Dados do Terminal -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-anchor mr-2" style="color: #0084de;"></i>
                            Terminal
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($movimento->terminal)
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $movimento->terminal->nome }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Código</label>
                                        <p class="text-gray-900">{{ $movimento->terminal->codigo ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                        <p class="text-gray-900">{{ $movimento->terminal->tipo ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                @if($movimento->terminal->localizacao)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Localização</label>
                                        <p class="text-gray-900">{{ $movimento->terminal->localizacao }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-500">Informações do terminal não disponíveis</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Portos e Tempos -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-route mr-2" style="color: #0084de;"></i>
                        Portos e Tempos
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Porto de Origem</label>
                            <p class="text-gray-900">{{ $movimento->porto_origem ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Porto de Destino</label>
                            <p class="text-gray-900">{{ $movimento->porto_destino ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ETA - Chegada Estimada</label>
                            <p class="text-gray-900">{{ $movimento->eta ? $movimento->eta->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ETD - Saída Estimada</label>
                            <p class="text-gray-900">{{ $movimento->etd ? $movimento->etd->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ATA - Chegada Real</label>
                            <p class="text-gray-900">{{ $movimento->ata ? $movimento->ata->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ATD - Saída Real</label>
                            <p class="text-gray-900">{{ $movimento->atd ? $movimento->atd->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faturação -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-file-invoice-dollar mr-2" style="color: #0084de;"></i>
                        Faturação
                    </h3>
                </div>
                <div class="p-6">
                    @if($movimento->facturas && $movimento->facturas->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Número</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Status</th>
                                        <th class="px-4 py-2 text-right font-medium text-gray-700">Valor Total</th>
                                        <th class="px-4 py-2 text-left font-medium text-gray-700">Evento</th>
                                        <th class="px-4 py-2 text-right font-medium text-gray-700">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($movimento->facturas as $factura)
                                        <tr>
                                            <td class="px-4 py-2 text-gray-900 font-medium">{{ $factura->numero }}</td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded bg-gray-100 text-gray-800 text-xs">{{ ucfirst($factura->status) }}</span>
                                            </td>
                                            <td class="px-4 py-2 text-right text-gray-900 font-bold">{{ number_format($factura->valor_total, 2, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-gray-700 text-xs">{{ $factura->metadados['evento'] ?? '-' }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <a href="{{ route('facturas.exportPdf', $factura) }}" class="inline-flex items-center px-2 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 active:bg-red-700 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150" title="Gerar PDF">
                                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                                </a>
                                            </td>
                                        </tr>
                                        <!-- Detalhes dos Itens -->
                                        @if($factura->items && $factura->items->count() > 0)
                                            <tr class="bg-gray-50">
                                                <td colspan="5" class="px-4 py-2">
                                                    <div class="ml-4 border-l-2 border-blue-200 pl-4">
                                                        <table class="w-full text-xs">
                                                            <thead>
                                                                <tr class="text-gray-500 border-b border-gray-200">
                                                                    <th class="text-left py-1">Descrição</th>
                                                                    <th class="text-right py-1">Qtd</th>
                                                                    <th class="text-right py-1">Unit.</th>
                                                                    <th class="text-right py-1">Subtotal</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-100">
                                                                @foreach($factura->items as $item)
                                                                    <tr>
                                                                        <td class="py-1 text-gray-700">{{ $item->descricao }}</td>
                                                                        <td class="py-1 text-right text-gray-600">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                                                                        <td class="py-1 text-right text-gray-600">{{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                                                        <td class="py-1 text-right text-gray-800 font-medium">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">Nenhuma fatura emitida para este movimento até o momento.</p>
                    @endif
                </div>
            </div>

            @if($movimento->eta && ($movimento->previsao_tempo_eta || $movimento->eta_temperatura_c || $movimento->eta_vento_max_kph || $movimento->eta_rajada_kph || $movimento->eta_chuva_chance_pct))
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-cloud-sun mr-2" style="color: #0084de;"></i>
                        Weather (ETA {{ $movimento->eta ? $movimento->eta->format('d/m/Y H:i') : '' }})
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <div class="text-gray-600">Condição</div>
                            <div class="text-gray-900 flex items-center">
                                @if(!empty($movimento->previsao_tempo_eta['condition_icon']))
                                    <img src="{{ $movimento->previsao_tempo_eta['condition_icon'] }}" class="w-6 h-6 mr-2">
                                @endif
                                {{ $movimento->previsao_tempo_eta['condition_text'] ?? 'N/A' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-600">Temp Média (°C)</div>
                            <div class="text-gray-900">{{ $movimento->eta_temperatura_c ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600">Vento Máx (kph)</div>
                            <div class="text-gray-900">{{ $movimento->eta_vento_max_kph ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600">Rajada (kph)</div>
                            <div class="text-gray-900">{{ $movimento->eta_rajada_kph ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600">Prob. Chuva (%)</div>
                            <div class="text-gray-900">{{ $movimento->eta_chuva_chance_pct ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($movimento->eta_swell_altura_m || $movimento->eta_onda_altura_m)
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-water mr-2" style="color: #0084de;"></i>
                        Condições do Mar (ETA)
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <div class="text-gray-600">Swell (m)</div>
                            <div class="text-gray-900">{{ $movimento->eta_swell_altura_m ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600">Período (s)</div>
                            <div class="text-gray-900">{{ $movimento->eta_swell_periodo_s ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600">Direção Swell</div>
                            <div class="text-gray-900">{{ $movimento->eta_swell_direcao ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600">Onda (m)</div>
                            <div class="text-gray-900">{{ $movimento->eta_onda_altura_m ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600">Direção Onda</div>
                            <div class="text-gray-900">{{ $movimento->eta_onda_direcao ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Cronograma -->
            <!--div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock mr-2" style="color: #0084de;"></i>
                        Cronograma
                    </h3>
                </div>
                <div class="p-6">
                    <!--div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900">Programado</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data</label>
                                    <p class="text-gray-900">{{ $movimento->data_programada ? $movimento->data_programada->format('d/m/Y') : 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hora</label>
                                    <p class="text-gray-900">{{ $movimento->hora_programada ? $movimento->hora_programada->format('H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900">Real</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data</label>
                                    <p class="text-gray-900">{{ $movimento->data_real ? $movimento->data_real->format('d/m/Y') : 'Não informado' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hora</label>
                                    <p class="text-gray-900">{{ $movimento->hora_real ? $movimento->hora_real->format('H:i') : 'Não informado' }}</p>
                                </div>
                            </div>
                        </div>
                    </div->

                    @if($movimento->getDuracaoEstimada())
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Duração Estimada</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $movimento->getDuracaoEstimada() }}</p>
                                </div>
                                @if($movimento->isAtrasado())
                                    <div class="bg-red-100 text-red-800 px-3 py-2 rounded-lg">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Atrasado
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div-->

            <!-- Observações e Documentos -->
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
                <!-- Observações -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-comment-alt mr-2" style="color: #0084de;"></i>
                            Observações
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($movimento->observacoes)
                            <p class="text-gray-900 whitespace-pre-line">{{ $movimento->observacoes }}</p>
                        @else
                            <p class="text-gray-500 italic">Nenhuma observação registrada</p>
                        @endif
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
              

                <!-- Documentos -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-file-alt mr-2" style="color: #0084de;"></i>
                            Documentos
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($movimento->documentos_apresentados && count($movimento->documentos_apresentados) > 0)
                            <div class="space-y-3">
                                @foreach($movimento->documentos_apresentados as $documento)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <i class="fas fa-file text-gray-400 mr-3"></i>
                                            <span class="text-sm text-gray-900">{{ $documento['nome_original'] ?? basename($documento['caminho'] ?? $documento) }}</span>
                                        </div>
                                        <a href="{{ Storage::url($documento['caminho'] ?? $documento) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic">Nenhum documento anexado</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informações de Auditoria -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history mr-2" style="color: #0084de;"></i>
                        Informações de Auditoria
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Criado por</label>
                            <p class="text-gray-900">{{ $movimento->usuario->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">{{ $movimento->created_at ? $movimento->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>

                        @if($movimento->autorizado_por)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Autorizado por</label>
                                <p class="text-gray-900">{{ $movimento->autorizadoPor->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">{{ $movimento->data_autorizacao ? $movimento->data_autorizacao->format('d/m/Y H:i') : 'N/A' }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Última atualização</label>
                            <p class="text-gray-900">{{ $movimento->updated_at ? $movimento->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Desatracar -->
    <div id="modalDesatracar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                    <i class="fas fa-ship text-indigo-600"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirmar Desatracação</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Informe a data e hora real da saída (Desatracação).
                    </p>
                    <form id="formDesatracar" action="{{ route('entrada-saida-embarcacao.desatracar', $movimento) }}" method="POST" class="mt-4 text-left">
                        @csrf
                        <div class="mb-4">
                            <label for="atd" class="block text-sm font-medium text-gray-700 mb-1">Data/Hora Saída Real <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="atd" id="atd_desatracar" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div class="flex items-center justify-end space-x-3 mt-4">
                            <button type="button" onclick="closeDesatracarModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none transition duration-200">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none transition duration-200 shadow-md">
                                Confirmar Saída
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDesatracarModal() {
            document.getElementById('modalDesatracar').classList.remove('hidden');
            // Sugerir hora atual
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('atd_desatracar').value = now.toISOString().slice(0, 16);
        }

        function closeDesatracarModal() {
            document.getElementById('modalDesatracar').classList.add('hidden');
        }

        // Fechar modal ao clicar fora
        document.getElementById('modalDesatracar').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDesatracarModal();
            }
        });
    </script>
</x-app-layout>