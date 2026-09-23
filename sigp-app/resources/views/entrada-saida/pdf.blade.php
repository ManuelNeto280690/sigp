<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimento {{ $entradaSaida->numero_movimento }} - {{ $entradaSaida->embarcacao->nome }}</title>
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
            font-size: 12px;
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
        
        .status-programado { background: #fff3cd; color: #856404; }
        .status-autorizado { background: #d4edda; color: #155724; }
        .status-em_andamento { background: #cce5ff; color: #004085; }
        .status-concluido { background: #d1ecf1; color: #0c5460; }
        .status-cancelado { background: #f8d7da; color: #721c24; }
        
        .documents-section {
            margin-top: 30px;
        }
        
        .documents-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .documents-row {
            display: table-row;
        }
        
        .document-cell {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            font-size: 10px;
            vertical-align: top;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
          
                <!--img src="{{ public_path('storage/' . $configuracoes['logo_relatorios']) }}" alt="Logo" class="logo"-->
        
            
            <h1>{{ is_array($configuracoes['cabecalho']) ? $configuracoes['cabecalho']['titulo'] : 'PORTO DE SOYO' }}</h1>
            <h2>{{ is_array($configuracoes['cabecalho']) ? $configuracoes['cabecalho']['subtitulo'] : 'Sistema Integrado de Gestão Portuária' }}</h2>
            <div class="header-info">
                <p>Documento gerado em {{ date('d/m/Y H:i:s') }} | Usuário: {{ auth()->user()->name }}</p>
            </div>
        </div>

        <!-- Título do Documento -->
        <div class="document-title">
            <h3>MOVIMENTO DE ENTRADA/SAÍDA DE EMBARCAÇÃO</h3>
            <p>Movimento #{{ $entradaSaida->numero_movimento }} - {{ $entradaSaida->embarcacao->nome }}</p>
        </div>

        <!-- Informações do Movimento -->
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES DO MOVIMENTO</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Número do Movimento</div>
                    <div class="info-value">{{ $entradaSaida->numero_movimento }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipo de Movimento</div>
                    <div class="info-value">{{ ucfirst($entradaSaida->tipo_movimento) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class=" status-{{ $entradaSaida->status }}">
                            {{ ucfirst(str_replace('_', ' ', $entradaSaida->status)) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Terminal</div>
                    <div class="info-value">{{ $entradaSaida->terminal->nome ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data Programada</div>
                    <div class="info-value">{{ $entradaSaida->data_programada ? $entradaSaida->data_programada->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data Efetiva</div>
                    <div class="info-value">{{ $entradaSaida->data_efetiva ? $entradaSaida->data_efetiva->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Informações da Embarcação -->
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES DA EMBARCAÇÃO</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Nome</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->nome }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">IMO</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->imo ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Bandeira</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->bandeira ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipo</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->tipo_embarcacao ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Comprimento</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->comprimento ? number_format($entradaSaida->embarcacao->comprimento, 2, ',', '.') . ' metros' : 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Boca</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->boca ? number_format($entradaSaida->embarcacao->boca, 2, ',', '.') . ' metros' : 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Detalhes Operacionais -->
        <div class="info-section">
            <div class="section-title">DETALHES OPERACIONAIS</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Agente Marítimo</div>
                    <div class="info-value">{{ $entradaSaida->agente_maritimo ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Berço</div>
                    <div class="info-value">{{ $entradaSaida->berco ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Estado da Embarcação</div>
                    <div class="info-value">{{ $entradaSaida->estado_embarcacao ? ucfirst($entradaSaida->estado_embarcacao) : 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Capitania Origem</div>
                    <div class="info-value">{{ $entradaSaida->capitania_origem ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Capitania Destino</div>
                    <div class="info-value">{{ $entradaSaida->capitania_destino ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Porto Origem</div>
                    <div class="info-value">{{ $entradaSaida->porto_origem ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Porto Destino</div>
                    <div class="info-value">{{ $entradaSaida->porto_destino ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Documentos Apresentados -->
        @if($entradaSaida->documentos_apresentados && count($entradaSaida->documentos_apresentados) > 0)
        <div class="info-section documents-section">
            <div class="section-title">DOCUMENTOS APRESENTADOS</div>
            <div class="documents-grid">
                @php
                    $documentos = $entradaSaida->documentos_apresentados;
                    $chunks = array_chunk($documentos, 3);
                @endphp
                @foreach($chunks as $chunk)
                <div class="documents-row">
                    @foreach($chunk as $documento)
                    <div class="document-cell">
                        @if(is_array($documento))
                            {{ $documento['nome_original'] ?? $documento['nome_arquivo'] ?? 'Documento' }}
                        @else
                            {{ $documento }}
                        @endif
                    </div>
                    @endforeach
                    @if(count($chunk) < 3)
                        @for($i = count($chunk); $i < 3; $i++)
                        <div class="document-cell">&nbsp;</div>
                        @endfor
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Informações de Autorização -->
        @if($entradaSaida->autorizado_por)
        <div class="info-section">
            <div class="section-title">INFORMAÇÕES DE AUTORIZAÇÃO</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Autorizado Por</div>
                    <div class="info-value">{{ $entradaSaida->autorizadoPor->name ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Data de Autorização</div>
                    <div class="info-value">{{ $entradaSaida->autorizado_em ? $entradaSaida->autorizado_em->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Motivo -->
        @if($entradaSaida->motivo)
        <div class="info-section">
            <div class="section-title">MOTIVO</div>
            <div class="observacoes-box">
                {{ $entradaSaida->motivo }}
            </div>
        </div>
        @endif

        <!-- Observações -->
        @if($entradaSaida->observacoes)
        <div class="info-section">
            <div class="section-title">OBSERVAÇÕES</div>
            <div class="observacoes-box">
                {{ $entradaSaida->observacoes }}
            </div>
        </div>
        @endif
    </div>

    <!-- Rodapé -->
    <div class="footer">
        Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária
        <br>
        Página 1 de 1 | Gerado em {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>