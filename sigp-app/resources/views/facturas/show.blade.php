<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                        <i class="fas fa-file-invoice-dollar text-white text-sm"></i>
                    </div>
                    Documento {{ $factura->numero }}
                </h2>
                <p class="text-gray-600 text-sm mt-1">
                    Emitido a {{ $factura->created_at->format('d/m/Y H:i') }} 
                    &bull; <span class="font-medium text-gray-800">{{ $factura->concessionaria->nome ?? 'Cliente Não Definido' }}</span>
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('facturas.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm shadow-sm border border-gray-200">
                    <i class="fas fa-arrow-left mr-2 text-sm"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Coluna Principal: Visualização da Fatura -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <!-- Cabeçalho da Fatura (Estilo Documento) -->
                        <div class="p-8 border-b border-gray-100 bg-gray-50 flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $factura->tipo_documento }}</h3>
                                <p class="text-sm text-gray-500 font-medium">Nº {{ $factura->numero }}</p>
                                <div class="mt-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $factura->status === 'paga' ? 'bg-green-100 text-green-800' : ($factura->status === 'cancelada' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ ucfirst($factura->status) }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 ml-2" title="Assinatura AGT">
                                        <i class="fas fa-fingerprint mr-1 text-gray-500"></i> Hash: {{ substr($factura->hash, 0, 4) }}-{{ substr($factura->hash, -4) }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">{{ app('config.helper')->nomeEmpresa() }}</p>
                                <p class="text-xs text-gray-500 mt-1">NIF: {{ app('config.helper')->get('nif_porto', 'Não configurado') }}</p>
                            </div>
                        </div>

                        <!-- Dados do Cliente -->
                        <div class="p-8 border-b border-gray-100 grid grid-cols-2 gap-8">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Faturado A:</p>
                                <p class="text-sm font-bold text-gray-900">{{ $factura->concessionaria->nome ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">NIF: {{ $factura->concessionaria->nif ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">{{ $factura->concessionaria->email ?? '' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Detalhes:</p>
                                <p class="text-sm text-gray-600"><span class="font-medium text-gray-900">Emissão:</span> {{ $factura->created_at->format('d/m/Y') }}</p>
                                @if($factura->contrato)
                                <p class="text-sm text-gray-600 mt-1"><span class="font-medium text-gray-900">Contrato:</span> {{ $factura->contrato->titulo }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Itens -->
                        <div class="p-8">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wider">Descrição dos Itens</h3>
                            @if($factura->items && $factura->items->count())
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Artigo / Serviço</th>
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">Qtd</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Preço Unit.</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">IVA</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($factura->items as $it)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $it->descricao }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-center">{{ number_format($it->quantidade, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($it->preco_unitario, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format($it->valor_iva, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right">{{ number_format($it->subtotal, 2, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-sm text-gray-500 p-4 border border-dashed border-gray-300 rounded-lg text-center">Nenhum item registado nesta fatura.</div>
                            @endif
                        </div>

                        <!-- Totais -->
                        <div class="bg-gray-50 p-8 border-t border-gray-200">
                            <div class="flex flex-col items-end space-y-3">
                                <div class="flex justify-between w-64 text-sm text-gray-600">
                                    <span>Total Ilíquido:</span>
                                    <span class="font-medium">{{ number_format($factura->total_s_iva, 2, ',', '.') }} Kz</span>
                                </div>
                                <div class="flex justify-between w-64 text-sm text-gray-600">
                                    <span>Total IVA:</span>
                                    <span class="font-medium">{{ number_format($factura->total_iva, 2, ',', '.') }} Kz</span>
                                </div>
                                @if($factura->total_imposto_selo > 0)
                                <div class="flex justify-between w-64 text-sm text-gray-600">
                                    <span>Imposto de Selo:</span>
                                    <span class="font-medium">{{ number_format($factura->total_imposto_selo, 2, ',', '.') }} Kz</span>
                                </div>
                                @endif
                                @if($factura->total_retencao > 0)
                                <div class="flex justify-between w-64 text-sm text-red-600 border-t border-gray-200 pt-2">
                                    <span>Retenção na Fonte:</span>
                                    <span class="font-medium">- {{ number_format($factura->total_retencao, 2, ',', '.') }} Kz</span>
                                </div>
                                @endif
                                <div class="flex justify-between w-72 pt-4 border-t-2 border-gray-300 mt-2">
                                    <span class="text-base font-bold text-gray-900 uppercase">Total a Pagar:</span>
                                    <span class="text-xl font-black text-blue-700">{{ number_format($factura->total_a_pagar > 0 ? $factura->total_a_pagar : $factura->valor_total, 2, ',', '.') }} Kz</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral: Ações e Partilha -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Ações Rápidas -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center uppercase tracking-wider">
                                <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                                Ações Rápidas
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <!-- Gerar PDF -->
                            <a href="{{ route('facturas.exportPdf', $factura) }}" target="_blank" class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                                <i class="fas fa-file-pdf mr-2 text-lg"></i>
                                Descarregar PDF
                            </a>

                            @if($factura->status !== 'paga' && !in_array($factura->tipo_documento, ['NC','FP','OR']))
                            <!-- Marcar Paga -->
                            <form method="post" action="{{ route('facturas.marcar-paga', $factura) }}" class="block">
                                @csrf
                                <button class="w-full flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                                    <i class="fas fa-check-circle mr-2 text-lg"></i>
                                    Registar Pagamento
                                </button>
                            </form>
                            @endif

                            @if(!in_array($factura->tipo_documento, ['NC', 'FP', 'OR']))
                            <!-- Emitir NC -->
                            <form method="post" action="{{ route('facturas.emitir-nota-credito', $factura) }}" class="block" onsubmit="return confirm('Tem certeza que deseja emitir uma Nota de Crédito para esta fatura? Isso irá anular este documento no SAF-T.');">
                                @csrf
                                <button class="w-full flex items-center justify-center px-4 py-3 bg-white border border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 font-medium rounded-lg transition duration-200">
                                    <i class="fas fa-undo-alt mr-2"></i>
                                    Anular com Nota de Crédito
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Partilhar Documento -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center uppercase tracking-wider">
                                <i class="fas fa-share-alt mr-2 text-indigo-500"></i>
                                Partilhar Documento
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <!-- Imprimir -->
                            <button onclick="window.print()" class="w-full flex items-center justify-center px-4 py-3 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium rounded-lg transition duration-200">
                                <i class="fas fa-print mr-2"></i>
                                Imprimir Resumo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <style>
        /* Estilos específicos para impressão */
        @media print {
            body { background: white !important; }
            nav, header, .lg\:col-span-1 { display: none !important; }
            .lg\:col-span-2 { width: 100% !important; max-width: 100% !important; box-shadow: none !important; border: none !important; }
            .content-layer { margin: 0 !important; padding: 0 !important; }
            .bg-white { box-shadow: none !important; }
        }
    </style>

</x-app-layout>