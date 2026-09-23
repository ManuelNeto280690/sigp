<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura {{ $factura->numero }}</title>
    <style>
        @page { margin: 2cm; size: A4; }
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0084de; padding-bottom: 20px; }
        .header h1 { color: #0084de; margin: 0; font-size: 24px; }
        .header p { color: #666; font-size: 10px; margin-top: 5px; }
        
        .columns { display: table; width: 100%; margin-bottom: 30px; }
        .column { display: table-cell; width: 48%; vertical-align: top; padding: 10px; border: 1px solid #ddd; background: #f9f9f9; }
        .column-spacer { display: table-cell; width: 4%; }
        
        .column h3 { color: #0084de; margin-top: 0; font-size: 12px; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .info-row { margin-bottom: 3px; }
        .info-label { font-weight: bold; color: #555; }
        
        .details-box { margin-bottom: 30px; }
        .details-table { width: 100%; border-collapse: collapse; }
        .details-table th { background: #0084de; color: white; text-align: left; padding: 8px; font-size: 11px; }
        .details-table td { border-bottom: 1px solid #eee; padding: 8px; font-size: 11px; }
        .details-table tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        
        .total-section { width: 100%; margin-top: 20px; text-align: right; }
        .total-box { display: inline-block; width: 250px; background: #f8f9fa; border: 1px solid #ddd; padding: 15px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .total-final { font-size: 14px; font-weight: bold; color: #0084de; border-top: 1px solid #ccc; padding-top: 5px; margin-top: 5px; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; text-align: center; color: #888; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($configuracoes['logo_relatorios']))
            <img src="{{ $configuracoes['logo_relatorios'] }}" style="max-height: 60px; margin-bottom: 10px;">
        @endif
         @if(!empty($configuracoes['logotipo']))
                <img src="{{ public_path('storage/' . $configuracoes['logotipo']) }}" alt="Logo" class="logo">
            @endif
        <h1>FATURA</h1>
        <p>Documento gerado eletronicamente</p>
    </div>

    <div class="columns">
        <div class="column">
            <h3>PRESTADOR DE SERVIÇOS</h3>
            <div class="info-row"><span class="info-label">Nome:</span> {{ $configuracoes['nome_porto'] }}</div>
            <div class="info-row"><span class="info-label">Endereço:</span> {{ $configuracoes['endereco_porto'] }}</div>
            <div class="info-row"><span class="info-label">NIF:</span> {{ $configuracoes['nif_porto'] }}</div>
            <div class="info-row"><span class="info-label">Email:</span> {{ $configuracoes['email_porto'] }}</div>
            <div class="info-row"><span class="info-label">Tel:</span> {{ $configuracoes['telefone_porto'] }}</div>
        </div>
        <div class="column-spacer"></div>
        <div class="column">
            <h3>CLIENTE</h3>
            @if($factura->concessionaria)
                <div class="info-row"><span class="info-label">Nome:</span> {{ $factura->concessionaria->nome }}</div>
                <div class="info-row"><span class="info-label">NIF:</span> {{ $factura->concessionaria->nif ?? 'N/A' }}</div>
                <div class="info-row"><span class="info-label">Endereço:</span> {{ $factura->concessionaria->endereco ?? 'N/A' }}</div>
                <div class="info-row"><span class="info-label">Email:</span> {{ $factura->concessionaria->email ?? 'N/A' }}</div>
            @else
                <div class="info-row">Cliente não identificado</div>
            @endif
        </div>
    </div>

    <div class="details-box">
        <table style="width: 100%; margin-bottom: 20px; font-size: 11px;">
            <tr>
                <td><strong>Fatura Nº:</strong> {{ $factura->numero }}</td>
                <td><strong>Data Emissão:</strong> {{ $factura->created_at->format('d/m/Y') }}</td>
                <td><strong>Estado:</strong> {{ ucfirst($factura->status) }}</td>
                <td><strong>Ref. Contrato:</strong> {{ $factura->contrato->titulo ?? 'N/A' }}</td>
            </tr>
            @if($factura->entradaSaida)
            <tr>
                <td colspan="4" style="padding-top: 5px;">
                    <strong>Movimento:</strong> #{{ $factura->entradaSaida->numero_movimento }} - {{ $factura->entradaSaida->embarcacao->nome ?? 'Embarcação N/A' }}
                </td>
            </tr>
            @endif
        </table>

        <table class="details-table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="text-right">Qtd</th>
                    <th class="text-right">Preço Unit.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->items as $item)
                    <tr>
                        <td>
                            {{ $item->descricao }}
                            @if($item->taxa_iva_id && $item->taxaIva && $item->taxaIva->taxa == 0)
                                <br><small style="color: #666;">Isento: {{ $item->taxaIva->motivo_isencao_codigo }} - {{ $item->taxaIva->motivo_isencao_descricao }}</small>
                            @elseif($item->motivo_isencao_codigo)
                                <br><small style="color: #666;">Isento: {{ $item->motivo_isencao_codigo }}</small>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="total-section">
        <div style="display: inline-block; width: 100%;">
            <!-- Quadro Resumo de Impostos (opcional visualmente mas útil) -->
            <table style="width: 250px; float: left; border: 1px solid #ddd; background: #f9f9f9; font-size: 9px;">
                <tr><th colspan="2" style="background:#eee; padding:5px; text-align:center;">Quadro de Impostos</th></tr>
                @if($factura->total_iva > 0)
                <tr><td style="padding: 3px;">IVA:</td><td class="text-right" style="padding: 3px;">{{ number_format($factura->total_iva, 2, ',', '.') }}</td></tr>
                @endif
                @if($factura->total_imposto_selo > 0)
                <tr><td style="padding: 3px;">Imposto de Selo:</td><td class="text-right" style="padding: 3px;">{{ number_format($factura->total_imposto_selo, 2, ',', '.') }}</td></tr>
                @endif
                @if($factura->total_retencao > 0)
                <tr><td style="padding: 3px;">Retenção na Fonte:</td><td class="text-right" style="padding: 3px;">-{{ number_format($factura->total_retencao, 2, ',', '.') }}</td></tr>
                @endif
                @if($factura->total_iva == 0 && $factura->total_imposto_selo == 0 && $factura->total_retencao == 0)
                <tr><td colspan="2" style="padding: 3px; text-align:center;">Sem impostos retidos/aplicados</td></tr>
                @endif
            </table>

            <!-- Totais Finais -->
            <table style="width: 250px; float: right; border: 1px solid #ddd; background: #f8f9fa;">
                <tr>
                    <td style="padding: 10px;">Total Iliquido:</td>
                    <td class="text-right" style="padding: 10px;">{{ number_format($factura->total_s_iva > 0 ? $factura->total_s_iva : $factura->valor_total, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-top: 1px solid #ccc; font-weight: bold; color: #0084de;">TOTAL A PAGAR:</td>
                    <td class="text-right" style="padding: 10px; border-top: 1px solid #ccc; font-weight: bold; color: #0084de;">{{ number_format($factura->total_a_pagar > 0 ? $factura->total_a_pagar : $factura->valor_total, 2, ',', '.') }} KZ</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <div style="font-weight:bold; margin-bottom: 5px; color:#333;">
            {{ substr($factura->hash ?? '1234', 0, 4) }}-{{ substr($factura->hash ?? '5678', -4) }}
        </div>
        Processado por programa validado nº 000/AGT/2026<br>
        {{ $configuracoes['rodape'] }}
        <br>
        Gerado em {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>