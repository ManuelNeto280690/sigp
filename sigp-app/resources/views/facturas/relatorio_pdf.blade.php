<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Faturas</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { width: 100%; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .header table { width: 100%; border: none; }
        .header td { border: none; vertical-align: middle; }
        .logo-container { width: 30%; }
        .logo { max-width: 150px; max-height: 80px; }
        .info-porto { width: 70%; text-align: right; }
        .info-porto h1 { margin: 0 0 5px 0; color: #0056b3; font-size: 18px; text-transform: uppercase; }
        .info-porto p { margin: 2px 0; font-size: 11px; }
        .title-box { background-color: #f8f9fa; border-left: 4px solid #0056b3; padding: 10px; margin-bottom: 20px; }
        .title-box h2 { margin: 0; color: #333; font-size: 16px; }
        .title-box p { margin: 5px 0 0 0; font-size: 11px; color: #666; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table.data th { background-color: #f1f5f9; color: #333; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        table.data tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .status-emitida { color: #0284c7; }
        .status-paga { color: #16a34a; }
        .status-cancelada { color: #dc2626; }
        .totais { width: 40%; float: right; border: 1px solid #ddd; background-color: #f8f9fa; }
        .totais th, .totais td { padding: 8px; text-align: right; }
        .totais th { background-color: #f1f5f9; text-align: left; border-bottom: 1px solid #ddd; }
        .totais-row td { border-top: 1px solid #ddd; }
        .totais-final { font-size: 12px; font-weight: bold; color: #000; background-color: #e2e8f0; }
        .footer { position: fixed; bottom: -20px; left: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
        .page-number:before { content: "Página " counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td class="logo-container">
                    @if(!empty($configuracoes['logo_relatorios']))
                        <img src="{{ $configuracoes['logo_relatorios'] }}" class="logo" alt="Logotipo">
                    @else
                        <h2 style="color:#0056b3; margin:0;">{{ $configuracoes['nome_porto'] }}</h2>
                    @endif
                </td>
                <td class="info-porto">
                    <h1>{{ $configuracoes['nome_porto'] }}</h1>
                    @if(!empty($configuracoes['endereco_porto']))<p>{{ $configuracoes['endereco_porto'] }}</p>@endif
                    @if(!empty($configuracoes['email_porto']))<p>Email: {{ $configuracoes['email_porto'] }}</p>@endif
                    @if(!empty($configuracoes['telefone_porto']))<p>Tel: {{ $configuracoes['telefone_porto'] }}</p>@endif
                    @if(!empty($configuracoes['nif_porto']))<p>NIF: {{ $configuracoes['nif_porto'] }}</p>@endif
                </td>
            </tr>
        </table>
    </div>

    <div class="title-box">
        <h2>Relatório Comercial de Faturas / Vendas</h2>
        <p>
            Período: 
            @if($request->data_inicio && $request->data_fim)
                {{ \Carbon\Carbon::parse($request->data_inicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($request->data_fim)->format('d/m/Y') }}
            @elseif($request->data_inicio)
                A partir de {{ \Carbon\Carbon::parse($request->data_inicio)->format('d/m/Y') }}
            @elseif($request->data_fim)
                Até {{ \Carbon\Carbon::parse($request->data_fim)->format('d/m/Y') }}
            @else
                Todo o histórico
            @endif
            | Documentos encontrados: {{ $facturas->count() }}
        </p>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Data</th>
                <th>Número</th>
                <th>Cliente (NIF)</th>
                <th class="text-center">Status</th>
                <th class="text-right">Total c/ IVA</th>
            </tr>
        </thead>
        <tbody>
            @php
                $somaTotal = 0;
                $somaS_IVA = 0;
                $somaIVA = 0;
            @endphp
            @forelse($facturas as $f)
                @php
                    $totalPagar = $f->total_a_pagar > 0 ? $f->total_a_pagar : $f->valor_total;
                    $subTotal = $f->total_s_iva > 0 ? $f->total_s_iva : $totalPagar;
                    
                    // Only sum non-canceled invoices
                    if (strtolower($f->status) !== 'cancelada') {
                        $somaTotal += $totalPagar;
                        $somaS_IVA += $subTotal;
                        $somaIVA += $f->total_iva;
                    }
                @endphp
                <tr>
                    <td>{{ $f->created_at->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ $f->numero }}</strong></td>
                    <td>
                        {{ $f->concessionaria->nome ?? '-' }}
                        <br><span style="color:#666; font-size:8px;">NIF: {{ $f->concessionaria->nif ?? '-' }}</span>
                    </td>
                    <td class="text-center">
                        <strong class="status-{{ strtolower($f->status) }}">{{ strtoupper($f->status) }}</strong>
                    </td>
                    <td class="text-right">{{ number_format($totalPagar, 2, ',', '.') }} Kz</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhum documento encontrado para o período/filtros selecionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($facturas->count() > 0)
    <table class="totais" cellspacing="0" cellpadding="0">
        <tr>
            <th colspan="2">RESUMO DE VENDAS (Exclui doc. cancelados)</th>
        </tr>
        <tr>
            <td>Subtotal (S/ IVA):</td>
            <td>{{ number_format($somaS_IVA, 2, ',', '.') }} Kz</td>
        </tr>
        <tr>
            <td>Total IVA:</td>
            <td>{{ number_format($somaIVA, 2, ',', '.') }} Kz</td>
        </tr>
        <tr class="totais-row totais-final">
            <td>TOTAL FATURADO:</td>
            <td>{{ number_format($somaTotal, 2, ',', '.') }} Kz</td>
        </tr>
    </table>
    <div style="clear: both;"></div>
    @endif

    <div class="footer">
        <p>{{ $configuracoes['rodape'] }}</p>
        <p class="page-number"></p>
        <p>Gerado em: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
