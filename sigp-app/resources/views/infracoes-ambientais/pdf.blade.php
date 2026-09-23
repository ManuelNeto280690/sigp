<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infração Ambiental {{ $infracao->numero_auto }}</title>
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
        
        .status-pendente { background: #fff3cd; color: #856404; }
        .status-aprovada { background: #d4edda; color: #155724; }
        .status-rejeitada { background: #f8d7da; color: #721c24; }
        .status-em-analise { background: #cce5ff; color: #004085; }
        
        .nivel-leve { background: #d1ecf1; color: #0c5460; }
        .nivel-moderada { background: #fff3cd; color: #856404; }
        .nivel-grave { background: #f5c6cb; color: #721c24; }
        .nivel-gravissima { background: #f8d7da; color: #721c24; }
        
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
            
            
            <h1>{{ $configuracoes['nome_porto'] ?? 'PORTO DE SOYO' }}</h1>
            <h2>{{ $configuracoes['nome_sistema'] ?? 'Sistema Integrado de Gestão Portuária' }}</h2>
            
            <!--div class="header-info">
                {{ $configuracoes['endereco'] ?? 'Soyo, Província do Zaire, Angola' }}<br>
                Tel: {{ $configuracoes['telefone'] ?? '+244 XXX XXX XXX' }} | 
                Email: {{ $configuracoes['email'] ?? 'contato@portodesoyo.ao' }}<br>
            </div-->
        </div>

        <!-- Título do Documento -->
        <div class="document-title">
            <h3>AUTO DE INFRAÇÃO AMBIENTAL</h3>
            <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }} por {{ Auth::user()->name }}</p>
        </div>

        <!-- Informações Básicas -->
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES BÁSICAS</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Número do Auto</div>
                    <div class="info-value">{{ $infracao->numero_auto }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipo de Infração</div>
                    <div class="info-value">{{ ucfirst(str_replace('_', ' ', $infracao->tipo_infracao)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nível de Gravidade</div>
                    <div class="info-value">
                        <span class="status-badge nivel-{{ $infracao->nivel_gravidade }}">
                            {{ ucfirst($infracao->nivel_gravidade) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $infracao->status }}">
                            {{ ucfirst($infracao->status) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data da Infração</div>
                    <div class="info-value">{{ $infracao->data_infracao->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data de Cadastro</div>
                    <div class="info-value">{{ $infracao->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
            </div>
        </div>

        <!-- Local e Contexto -->
        <div class="info-section">
            <div class="section-title">LOCAL E CONTEXTO</div>
            <div class="info-grid">
                @if($infracao->embarcacao)
                <div class="info-row">
                    <div class="info-label">Embarcação</div>
                    <div class="info-value">{{ $infracao->embarcacao->nome }} ({{ $infracao->embarcacao->numero_registro ?? $infracao->embarcacao->imo ?? 'N/A' }})</div>
                </div>
                @endif
                @if($infracao->terminal)
                <div class="info-row">
                    <div class="info-label">Terminal</div>
                    <div class="info-value">{{ $infracao->terminal->nome }}</div>
                </div>
                @endif
                @if($infracao->inspetor)
                <div class="info-row">
                    <div class="info-label">Inspetor Responsável</div>
                    <div class="info-value">{{ $infracao->inspetor->name }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Descrição da Infração -->
        @if($infracao->descricao)
        <div class="info-section">
            <div class="section-title">DESCRIÇÃO DA INFRAÇÃO</div>
            <div class="observacoes-box">
                {{ $infracao->descricao }}
            </div>
        </div>
        @endif

        <!-- Medidas Corretivas -->
        @if($infracao->medidas_corretivas)
        <div class="info-section">
            <div class="section-title">MEDIDAS CORRETIVAS APLICADAS</div>
            <div class="observacoes-box">
                {{ $infracao->medidas_corretivas }}
            </div>
        </div>
        @endif

        <!-- Observações -->
        @if($infracao->observacoes)
        <div class="info-section">
            <div class="section-title">OBSERVAÇÕES</div>
            <div class="observacoes-box">
                {{ $infracao->observacoes }}
            </div>
        </div>
        @endif

        <!-- Informações de Aprovação -->
        @if($infracao->aprovadoPor)
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES DE APROVAÇÃO</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Aprovado por</div>
                    <div class="info-value">{{ $infracao->aprovadoPor->name }}</div>
                </div>
                @if($infracao->data_aprovacao)
                <div class="info-row">
                    <div class="info-label">Data de Aprovação</div>
                    <div class="info-value">{{ $infracao->data_aprovacao->format('d/m/Y H:i:s') }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

       
    </div>

    <!-- Rodapé -->
    <div class="footer">
        {{ $configuracoes['rodape'] ?? 'Porto de Soyo - Sistema Integrado de Gestão Portuária' }}
        <br>
        Página 1 de 1 | Gerado em {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>