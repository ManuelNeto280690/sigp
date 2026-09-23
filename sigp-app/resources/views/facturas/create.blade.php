<x-app-layout>
    <div class="py-6" x-data="invoiceForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Header section that was previously in x-slot -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0084de, #0a5ae8);">
                            <i class="fas fa-file-invoice-dollar text-white text-sm"></i>
                        </div>
                        Nova Fatura Manual
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">Criação de fatura avulsa (FT ou FR)</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('facturas.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm">
                        <i class="fas fa-arrow-left mr-2 text-sm"></i>Voltar
                    </a>
                </div>
            </div>
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-400 text-lg"></i>
                        <ul class="ml-3 list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('facturas.store') }}" method="POST" id="facturaForm">
                @csrf
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-600 text-xs"></i>
                            Detalhes Gerais
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cliente (Concessionária) <span class="text-red-500">*</span></label>
                            <select name="concessionaria_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Selecione o Cliente</option>
                                @foreach($concessionarias as $c)
                                    <option value="{{ $c->id }}" {{ old('concessionaria_id') == $c->id ? 'selected' : '' }}>{{ $c->nome }} (NIF: {{ $c->nif }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Documento <span class="text-red-500">*</span></label>
                            <select name="tipo_documento" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <optgroup label="Faturas / Comerciais">
                                    <option value="FT" {{ old('tipo_documento') == 'FT' ? 'selected' : '' }}>Fatura (FT)</option>
                                    <option value="FR" {{ old('tipo_documento') == 'FR' ? 'selected' : '' }}>Fatura-Recibo (FR)</option>
                                    <option value="FS" {{ old('tipo_documento') == 'FS' ? 'selected' : '' }}>Fatura Simplificada (FS)</option>
                                    <option value="VD" {{ old('tipo_documento') == 'VD' ? 'selected' : '' }}>Venda a Dinheiro (VD)</option>
                                </optgroup>
                                <optgroup label="Documentos de Conferência">
                                    <option value="FP" {{ old('tipo_documento') == 'FP' ? 'selected' : '' }}>Fatura Pró-forma (FP)</option>
                                    <option value="OR" {{ old('tipo_documento') == 'OR' ? 'selected' : '' }}>Orçamento (OR)</option>
                                </optgroup>
                                <optgroup label="Retificativos">
                                    <option value="ND" {{ old('tipo_documento') == 'ND' ? 'selected' : '' }}>Nota de Débito (ND)</option>
                                    <option value="NC" {{ old('tipo_documento') == 'NC' ? 'selected' : '' }}>Nota de Crédito (NC)</option>
                                </optgroup>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Todos os tipos seguem o formato estruturado do ficheiro SAF-T (AO).</p>
                        </div>
                    </div>
                </div>

                <!-- Itens da Fatura -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-boxes mr-2 text-blue-600 text-xs"></i>
                            Itens da Fatura
                        </h3>
                        <button type="button" @click="addItem" class="bg-blue-50 text-blue-600 hover:bg-blue-100 font-medium py-1 px-3 rounded-md transition duration-200 text-xs border border-blue-200 flex items-center">
                            <i class="fas fa-plus mr-1"></i> Adicionar Linha
                        </button>
                    </div>
                    <div class="p-6 overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3">Descrição <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 w-32">Qtd <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 w-40">Preço Unit. <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 w-48">Imposto <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 w-32 text-right">Subtotal</th>
                                    <th class="px-4 py-3 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="item.id">
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-2">
                                            <input type="text" x-model="item.descricao" :name="`items[${index}][descricao]`" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required placeholder="Ex: Serviços Portuários">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" step="0.01" min="0.01" x-model="item.quantidade" :name="`items[${index}][quantidade]`" @input="calculateTotals" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" step="0.01" min="0" x-model="item.preco_unitario" :name="`items[${index}][preco_unitario]`" @input="calculateTotals" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                        </td>
                                        <td class="p-2">
                                            <select x-model="item.imposto_id" :name="`items[${index}][imposto_id]`" @change="calculateTotals" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                                                <option value="">Selecione IVA/IS...</option>
                                                @foreach($impostosIva as $imp)
                                                    <option value="{{ $imp->id }}">
                                                        {{ $imp->tipo }} - {{ $imp->sigla }} ({{ number_format($imp->taxa, 1, ',', '.') }}%)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-2 text-right font-medium text-gray-900">
                                            <span x-text="formatMoney(item.quantidade * item.preco_unitario)"></span>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 transition-colors" x-show="items.length > 1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <!-- Totais -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex flex-col items-end space-y-2 text-sm">
                            <div class="flex justify-between w-64 text-gray-600">
                                <span>Subtotal (Sem IVA):</span>
                                <span class="font-medium" x-text="formatMoney(totals.subtotal)"></span>
                            </div>
                            <div class="flex justify-between w-64 text-gray-600">
                                <span>Total IVA:</span>
                                <span class="font-medium" x-text="formatMoney(totals.iva)"></span>
                            </div>
                            <div class="flex justify-between w-64 text-gray-600">
                                <span>Imposto de Selo:</span>
                                <span class="font-medium" x-text="formatMoney(totals.imposto_selo)"></span>
                            </div>
                            <div class="flex items-center justify-between w-full max-w-sm text-gray-600 text-red-600 mt-2 border-t border-gray-200 pt-2">
                                <div class="flex flex-col">
                                    <span class="font-medium text-xs mb-1">Retenção na Fonte (Opcional):</span>
                                    <select name="retencao_imposto_id" x-model="retencao_imposto_id" @change="calculateTotals" class="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs py-1 w-48">
                                        <option value="">Sem Retenção</option>
                                        @foreach($impostosRetencao as $imp)
                                            <option value="{{ $imp->id }}">{{ $imp->tipo }} - {{ $imp->sigla }} ({{ number_format($imp->taxa, 1, ',', '.') }}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="font-medium" x-show="totals.retencao > 0" x-text="'- ' + formatMoney(totals.retencao)"></span>
                                <span class="font-medium" x-show="totals.retencao == 0">-</span>
                            </div>
                            <div class="flex justify-between w-64 pt-2 border-t border-gray-300 text-base font-bold text-gray-900">
                                <span>Total a Pagar:</span>
                                <span x-text="formatMoney(totals.total_pagar)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center">
                        <i class="fas fa-check mr-2"></i> Emitir Fatura
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script de AlpineJS para os cálculos dinâmicos -->
    <script>
        function invoiceForm() {
            return {
                items: [
                    { id: Date.now(), descricao: '', quantidade: 1, preco_unitario: 0, imposto_id: '' }
                ],
                totals: {
                    subtotal: 0,
                    iva: 0,
                    imposto_selo: 0,
                    retencao: 0,
                    total_pagar: 0
                },
                retencao_imposto_id: '',
                impostosData: {
                    @foreach($impostosIva->merge($impostosRetencao) as $imp)
                    '{{ $imp->id }}': { taxa: {{ $imp->taxa }}, tipo: '{{ $imp->tipo }}' },
                    @endforeach
                },
                addItem() {
                    this.items.push({ id: Date.now(), descricao: '', quantidade: 1, preco_unitario: 0, imposto_id: '' });
                    this.calculateTotals();
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                    this.calculateTotals();
                },
                calculateTotals() {
                    let sub = 0;
                    let iva = 0;
                    let iselo = 0;
                    let ret = 0;

                    this.items.forEach(item => {
                        let rowSubtotal = (parseFloat(item.quantidade) || 0) * (parseFloat(item.preco_unitario) || 0);
                        sub += rowSubtotal;

                        if(item.imposto_id && this.impostosData[item.imposto_id]) {
                            let impData = this.impostosData[item.imposto_id];
                            let impValue = rowSubtotal * (impData.taxa / 100);
                            
                            if (impData.tipo === 'IVA') iva += impValue;
                            else if (impData.tipo === 'IS') iselo += impValue;
                        }
                    });

                    if (this.retencao_imposto_id && this.impostosData[this.retencao_imposto_id]) {
                        let impData = this.impostosData[this.retencao_imposto_id];
                        ret = sub * (impData.taxa / 100);
                    }

                    this.totals.subtotal = sub;
                    this.totals.iva = iva;
                    this.totals.imposto_selo = iselo;
                    this.totals.retencao = ret;
                    this.totals.total_pagar = (sub + iva + iselo) - ret;
                },
                formatMoney(value) {
                    return new Intl.NumberFormat('pt-AO', { style: 'currency', currency: 'AOA' }).format(value || 0);
                }
            }
        }
    </script>
</x-app-layout>
