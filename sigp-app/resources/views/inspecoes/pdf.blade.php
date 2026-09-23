<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspeção do Navio {{ $inspecao->numero_inspecao }}</title>
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
        
        .status-agendada { background: #fff3cd; color: #856404; }
        .status-em-andamento { background: #cce5ff; color: #004085; }
        .status-concluida { background: #d4edda; color: #155724; }
        .status-cancelada { background: #f8d7da; color: #721c24; }
        
        .resultado-aprovado { background: #d4edda; color: #155724; }
        .resultado-reprovado { background: #f8d7da; color: #721c24; }
        .resultado-pendente { background: #fff3cd; color: #856404; }
        
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
            <h3>RELATÓRIO DE INSPEÇÃO DO NAVIO</h3>
            <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }} por {{ Auth::user()->name }}</p>
        </div>

        <!-- Informações Básicas -->
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES BÁSICAS</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Número da Inspeção</div>
                    <div class="info-value">{{ $inspecao->numero_inspecao }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipo de Inspeção</div>
                    <div class="info-value">{{ ucfirst(str_replace('_', ' ', $inspecao->tipo_inspecao)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $inspecao->status }}">
                            {{ ucfirst(str_replace('_', ' ', $inspecao->status)) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Resultado</div>
                    <div class="info-value">
                        <span class="status-badge resultado-{{ $inspecao->resultado }}">
                            {{ ucfirst($inspecao->resultado) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data da Inspeção</div>
                    <div class="info-value">{{ $inspecao->data_inspecao->format('d/m/Y H:i:s') }}</div>
                </div>
                @if($inspecao->hora_inicio)
                <div class="info-row">
                    <div class="info-label">Hora de Início</div>
                    <div class="info-value">{{ $inspecao->hora_inicio->format('H:i:s') }}</div>
                </div>
                @endif
                @if($inspecao->hora_fim)
                <div class="info-row">
                    <div class="info-label">Hora de Término</div>
                    <div class="info-value">{{ $inspecao->hora_fim->format('H:i:s') }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Embarcação e Responsáveis -->
        <div class="info-section">
            <div class="section-title">EMBARCAÇÃO E RESPONSÁVEIS</div>
            <div class="info-grid">
                @if($inspecao->embarcacao)
                <div class="info-row">
                    <div class="info-label">Embarcação</div>
                    <div class="info-value">{{ $inspecao->embarcacao->nome }} ({{ $inspecao->embarcacao->numero_registro ?? $inspecao->embarcacao->imo ?? 'N/A' }})</div>
                </div>
                @endif
                @if($inspecao->inspetor)
                <div class="info-row">
                    <div class="info-label">Inspetor Responsável</div>
                    <div class="info-value">{{ $inspecao->inspetor->name }}</div>
                </div>
                @endif
                @if($inspecao->local_inspecao)
                <div class="info-row">
                    <div class="info-label">Local da Inspeção</div>
                    <div class="info-value">{{ $inspecao->local_inspecao }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Checklist de Itens -->
        @if($inspecao->checklist_itens && count($inspecao->checklist_itens) > 0)
        <div class="info-section">
            <div class="section-title">CHECKLIST DE ITENS VERIFICADOS</div>
            <div class="observacoes-box">
                <ul style="margin-left: 20px;">
                    @foreach($inspecao->checklist_itens as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Não Conformidades -->
        @if($inspecao->nao_conformidades && count($inspecao->nao_conformidades) > 0)
        <div class="info-section">
            <div class="section-title">NÃO CONFORMIDADES IDENTIFICADAS</div>
            <div class="observacoes-box">
                <ul style="margin-left: 20px;">
                    @foreach($inspecao->nao_conformidades as $nao_conformidade)
                        <li>{{ $nao_conformidade }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Ações Corretivas -->
        @if($inspecao->acoes_corretivas)
        <div class="info-section">
            <div class="section-title">AÇÕES CORRETIVAS RECOMENDADAS</div>
            <div class="observacoes-box">
                {{ $inspecao->acoes_corretivas }}
            </div>
        </div>
        @endif

        <!-- Restrições -->
        @if($inspecao->restricoes)
        <div class="info-section">
            <div class="section-title">RESTRIÇÕES APLICADAS</div>
            <div class="observacoes-box">
                {{ $inspecao->restricoes }}
            </div>
        </div>
        @endif

        <!-- Observações -->
        @if($inspecao->observacoes)
        <div class="info-section">
            <div class="section-title">OBSERVAÇÕES</div>
            <div class="observacoes-box">
                {{ $inspecao->observacoes }}
            </div>
        </div>
        @endif

        <!-- Informações de Aprovação -->
        @if($inspecao->aprovadoPor)
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES DE APROVAÇÃO</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Aprovado por</div>
                    <div class="info-value">{{ $inspecao->aprovadoPor->name }}</div>
                </div>
                @if($inspecao->aprovado_em)
                <div class="info-row">
                    <div class="info-label">Data de Aprovação</div>
                    <div class="info-value">{{ $inspecao->aprovado_em->format('d/m/Y H:i:s') }}</div>
                </div>
                @endif
                @if($inspecao->prazo_correcao)
                <div class="info-row">
                    <div class="info-label">Prazo para Correção</div>
                    <div class="info-value">{{ $inspecao->prazo_correcao->format('d/m/Y H:i:s') }}</div>
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