<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidente {{ $incidente->numero_incidente }}</title>
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
        
        .status-aberto { background: #ffebee; color: #c62828; }
        .status-investigando { background: #fff3e0; color: #ef6c00; }
        .status-resolvido { background: #e8f5e8; color: #2e7d32; }
        .status-fechado { background: #f3e5f5; color: #7b1fa2; }
        
        .nivel-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .nivel-baixo { background: #e8f5e8; color: #2e7d32; }
        .nivel-medio { background: #fff3e0; color: #ef6c00; }
        .nivel-alto { background: #ffebee; color: #c62828; }
        .nivel-critico { background: #f3e5f5; color: #7b1fa2; }
        
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

          
                <!--img src="{{ public_path('storage/' . $configuracoes['logo_sistema']) }}" alt="Logo" class="logo"-->
            
            
            <h1>{{ is_array($configuracoes['cabecalho_documentos']) ? $configuracoes['cabecalho_documentos']['titulo'] : 'PORTO DE SOYO' }}</h1>
            <h2>{{ is_array($configuracoes['cabecalho_documentos']) ? $configuracoes['cabecalho_documentos']['subtitulo'] : 'Sistema Integrado de Gestão Portuária' }}</h2>
            
            @if(is_array($configuracoes['cabecalho_documentos']))
            <div class="header-info">
                {{ $configuracoes['cabecalho_documentos']['endereco'] ?? '' }}<br>
                Tel: {{ $configuracoes['cabecalho_documentos']['telefone'] ?? '' }} | 
                Email: {{ $configuracoes['cabecalho_documentos']['email'] ?? '' }}<br>
                {{ $configuracoes['cabecalho_documentos']['website'] ?? '' }}
            </div>
            @endif
        </div>

        <!-- Título do Documento -->
        <div class="document-title">
            <h3>RELATÓRIO DE INCIDENTE</h3>
            <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }} por {{ Auth::user()->name }}</p>
        </div>

        <!-- Informações Básicas -->
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES BÁSICAS</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Número do Incidente</div>
                    <div class="info-value">{{ $incidente->numero_incidente }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Título</div>
                    <div class="info-value">{{ $incidente->titulo }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipo</div>
                    <div class="info-value">{{ ucfirst($incidente->tipo) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nível</div>
                    <div class="info-value">
                        <span class="nivel-badge nivel-{{ $incidente->nivel }}">{{ ucfirst($incidente->nivel) }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $incidente->status }}">{{ ucfirst($incidente->status) }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Reportado por</div>
                    <div class="info-value">{{ $incidente->user->name ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data de Ocorrência</div>
                    <div class="info-value">{{ $incidente->data_ocorrencia ? $incidente->data_ocorrencia->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data de Criação</div>
                    <div class="info-value">{{ $incidente->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                @if($incidente->data_resolucao)
                <div class="info-row">
                    <div class="info-label">Data de Resolução</div>
                    <div class="info-value">{{ $incidente->data_resolucao->format('d/m/Y H:i:s') }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Local e Contexto -->
        <div class="info-section">
            <div class="section-title">LOCAL E CONTEXTO</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Local da Ocorrência</div>
                    <div class="info-value">{{ $incidente->local_ocorrencia ?: 'Não informado' }}</div>
                </div>
                @if($incidente->embarcacao)
                <div class="info-row">
                    <div class="info-label">Embarcação</div>
                    <div class="info-value">{{ $incidente->embarcacao->nome }} (IMO: {{ $incidente->embarcacao->imo }})</div>
                </div>
                @endif
                @if($incidente->terminal)
                <div class="info-row">
                    <div class="info-label">Terminal</div>
                    <div class="info-value">{{ $incidente->terminal->nome }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Descrição -->
        <div class="info-section">
            <div class="section-title">DESCRIÇÃO DO INCIDENTE</div>
            <div class="observacoes-box">
                {{ $incidente->descricao ?: 'Nenhuma descrição fornecida.' }}
            </div>
        </div>

        <!-- Áreas Afetadas -->
        @if($incidente->areas_afetadas)
        <div class="info-section">
            <div class="section-title">ÁREAS AFETADAS</div>
            <div class="observacoes-box">
                {{ $incidente->areas_afetadas }}
            </div>
        </div>
        @endif

        <!-- Ações Tomadas -->
        @if($incidente->acoes_tomadas)
        <div class="info-section">
            <div class="section-title">AÇÕES TOMADAS</div>
            <div class="observacoes-box">
                {{ $incidente->acoes_tomadas }}
            </div>
        </div>
        @endif

        <!-- Informações de Investigação -->
        @if($incidente->status === 'investigando' || $incidente->status === 'resolvido' || $incidente->status === 'fechado')
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES DE INVESTIGAÇÃO</div>
            <div class="info-grid">
                @if($incidente->responsavelInvestigacao)
                <div class="info-row">
                    <div class="info-label">Responsável pela Investigação</div>
                    <div class="info-value">{{ $incidente->responsavelInvestigacao->name }}</div>
                </div>
                @endif
                @if($incidente->data_inicio_investigacao)
                <div class="info-row">
                    <div class="info-label">Início da Investigação</div>
                    <div class="info-value">{{ $incidente->data_inicio_investigacao->format('d/m/Y H:i:s') }}</div>
                </div>
                @endif
                @if($incidente->observacoes_investigacao)
                <div class="info-row">
                    <div class="info-label">Observações da Investigação</div>
                    <div class="info-value">{{ $incidente->observacoes_investigacao }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Informações de Resolução -->
        @if($incidente->status === 'resolvido' || $incidente->status === 'fechado')
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES DE RESOLUÇÃO</div>
            <div class="info-grid">
                @if($incidente->data_resolucao)
                <div class="info-row">
                    <div class="info-label">Data de Resolução</div>
                    <div class="info-value">{{ $incidente->data_resolucao->format('d/m/Y H:i:s') }}</div>
                </div>
                @endif
                @if($incidente->observacoes_resolucao)
                <div class="info-row">
                    <div class="info-label">Observações da Resolução</div>
                    <div class="info-value">{{ $incidente->observacoes_resolucao }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

       
    </div>

    <!-- Rodapé -->
    <div class="footer">
        {{ $configuracoes['rodape_documentos'] ?? 'Sistema Integrado de Gestão Portuária' }}
        <br>
        Página 1 de 1 | Gerado em {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>