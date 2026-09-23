<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Embarcação {{ $embarcacao->nome }} - {{ $embarcacao->imo }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm 3cm 1.5cm;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #0084de;
            padding-bottom: 25px;
            margin-bottom: 35px;
        }
        
        .logo {
            max-height: 60px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            color: #0084de;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .header h2 {
            color: #666;
            font-size: 16px;
            font-weight: normal;
            margin-bottom: 15px;
        }
        
        .header-info {
            font-size: 10px;
            color: #888;
            line-height: 1.4;
        }
        
        .document-title {
            text-align: center;
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 35px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .document-title h3 {
            color: #0084de;
            font-size: 18px;
            margin-bottom: 8px;
        }
        
        .document-title p {
            color: #666;
            font-size: 11px;
        }
        
        .info-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #0084de;
            color: white;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 35%;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            font-weight: bold;
            color: #495057;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            width: 65%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            background: white;
            vertical-align: top;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-esperado { background: #fff3cd; color: #856404; }
        .status-atracado { background: #d4edda; color: #155724; }
        .status-partido { background: #f8d7da; color: #721c24; }
        .status-em-transito { background: #cce5ff; color: #004085; }
        
        .movements-section {
            margin-top: 40px;
            page-break-before: auto;
        }
        
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .movements-table th {
            background: #f8f9fa;
            padding: 10px 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        .movements-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
        }
        
        .movements-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .observacoes-box {
            padding: 15px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 5px;
            line-height: 1.6;
        }
        
        .footer {
            position: fixed;
            bottom: 1cm;
            left: 1.5cm;
            right: 1.5cm;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-10 { margin-bottom: 10px; }
        .mb-15 { margin-bottom: 15px; }
        .mb-20 { margin-bottom: 20px; }
        .mb-30 { margin-bottom: 30px; }
        
        /* Melhorar quebras de página */
        .info-section {
            page-break-inside: avoid;
        }
        
        .movements-table {
            page-break-inside: auto;
        }
        
        .movements-table tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            @if(!empty($configuracoes['logotipo']))
                <img src="{{ public_path('storage/' . $configuracoes['logotipo']) }}" alt="Logo" class="logo">
            @endif
            
            <h1>{{ is_array($configuracoes['cabecalho']) ? $configuracoes['cabecalho']['titulo'] : 'PORTO DE SOYO' }}</h1>
            <h2>{{ is_array($configuracoes['cabecalho']) ? $configuracoes['cabecalho']['subtitulo'] : 'Sistema Integrado de Gestão Portuária' }}</h2>
            
            @if(is_array($configuracoes['cabecalho']))
            <div class="header-info">
                {{ $configuracoes['cabecalho']['endereco'] ?? '' }}<br>
                Tel: {{ $configuracoes['cabecalho']['telefone'] ?? '' }} | 
                Email: {{ $configuracoes['cabecalho']['email'] ?? '' }}<br>
                {{ $configuracoes['cabecalho']['website'] ?? '' }}
            </div>
            @endif
        </div>

        <!-- Título do Documento -->
        <div class="document-title">
            <h3>FICHA TÉCNICA DA EMBARCAÇÃO</h3>
            <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }} por {{ Auth::user()->name }}</p>
        </div>

        <!-- Informações Básicas -->
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES BÁSICAS</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Nome da Embarcação</div>
                    <div class="info-value">{{ $embarcacao->nome }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Número IMO</div>
                    <div class="info-value">{{ $embarcacao->imo }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">MMSI</div>
                    <div class="info-value">{{ $embarcacao->mmsi ?: 'Não informado' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Bandeira</div>
                    <div class="info-value">{{ $embarcacao->bandeira }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipo de Embarcação</div>
                    <div class="info-value">{{ ucfirst(str_replace('_', ' ', $embarcacao->tipo_embarcacao)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status Atual</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $embarcacao->status }}">
                            {{ ucfirst($embarcacao->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dimensões e Características -->
        <div class="info-section">
            <div class="section-title">DIMENSÕES E CARACTERÍSTICAS TÉCNICAS</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Comprimento Total</div>
                    <div class="info-value">{{ number_format($embarcacao->comprimento, 2, ',', '.') }} metros</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Largura (Boca)</div>
                    <div class="info-value">{{ number_format($embarcacao->largura, 2, ',', '.') }} metros</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Calado</div>
                    <div class="info-value">{{ number_format($embarcacao->calado, 2, ',', '.') }} metros</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Arqueação Bruta</div>
                    <div class="info-value">{{ number_format($embarcacao->arqueacao_bruta, 2, ',', '.') }} GT</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Arqueação Líquida</div>
                    <div class="info-value">{{ number_format($embarcacao->arqueacao_liquida, 2, ',', '.') }} NT</div>
                </div>
            </div>
        </div>

        <!-- Informações Operacionais -->
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES OPERACIONAIS</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Armador</div>
                    <div class="info-value">{{ $embarcacao->armador ?: 'Não informado' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Agente Marítimo</div>
                    <div class="info-value">{{ $embarcacao->agente_maritimo ?: 'Não informado' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Capitão</div>
                    <div class="info-value">{{ $embarcacao->capitao ?: 'Não informado' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data de Cadastro</div>
                    <div class="info-value">{{ $embarcacao->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Última Atualização</div>
                    <div class="info-value">{{ $embarcacao->updated_at->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
        </div>

        <!-- Observações -->
        @if($embarcacao->observacoes)
        <div class="info-section">
            <div class="section-title">OBSERVAÇÕES</div>
            <div class="observacoes-box">
                {{ $embarcacao->observacoes }}
            </div>
        </div>
        @endif

        <!-- Histórico de Movimentações -->
        @if($embarcacao->entradasSaidas->count() > 0)
        <div class="info-section movements-section">
            <div class="section-title">HISTÓRICO DE MOVIMENTAÇÕES (ÚLTIMAS 10)</div>
            <table class="movements-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Data/Hora</th>
                        <th style="width: 15%;">Tipo</th>
                        <th style="width: 25%;">Terminal</th>
                        <th style="width: 17%;">Status</th>
                        <th style="width: 25%;">Operador</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($embarcacao->entradasSaidas as $movimento)
                    <tr>
                        <td>{{ $movimento->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ ucfirst($movimento->tipo_movimento) }}</td>
                        <td>{{ $movimento->terminal->nome ?? 'N/A' }}</td>
                        <td>{{ ucfirst($movimento->status) }}</td>
                        <td>{{ $movimento->usuario->name ?? 'Sistema' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Rodapé -->
    <div class="footer">
        {{ $configuracoes['rodape'] }}
        <br>
        Página 1 de 1 | Gerado em {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>