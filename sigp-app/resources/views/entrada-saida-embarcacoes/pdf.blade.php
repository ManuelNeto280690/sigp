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
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .info-value {
            color: #333;
            font-size: 12px;
            word-wrap: break-word;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-em_andamento { background: #fff3cd; color: #856404; }
        .status-autorizado { background: #d4edda; color: #155724; }
        .status-iniciado { background: #cce5ff; color: #004085; }
        .status-concluido { background: #d1ecf1; color: #0c5460; }
        .status-cancelado { background: #f8d7da; color: #721c24; }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .footer {
            position: fixed;
            bottom: 1cm;
            left: 1.5cm;
            right: 1.5cm;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        
        .page-number:after {
            content: counter(page);
        }
        
        .documentos-list {
            list-style: none;
            padding: 0;
        }
        
        .documentos-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .documentos-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
            @endif
            <h1>SIGP - Sistema Integrado de Gestão Portuária</h1>
            <h2>Autoridade Portuária</h2>
            <div class="header-info">
                <p>Documento gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
                <p>Usuário: {{ Auth::user()->name ?? 'Sistema' }}</p>
            </div>
        </div>

        <!-- Título do Documento -->
        <div class="document-title">
            <h3>Movimento de Entrada/Saída de Embarcação</h3>
            <p>Número do Movimento: {{ $entradaSaida->numero_movimento }}</p>
        </div>

        <!-- Informações da Embarcação -->
        <div class="info-section">
            <div class="section-title">Informações da Embarcação</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nome da Embarcação</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->nome }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">IMO</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->imo }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Bandeira</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->bandeira }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tipo</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->tipo }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Comprimento</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->comprimento }} m</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Boca</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->boca }} m</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Calado</div>
                    <div class="info-value">{{ $entradaSaida->embarcacao->calado }} m</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Arqueação Bruta</div>
                    <div class="info-value">{{ number_format($entradaSaida->embarcacao->arqueacao_bruta, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Informações do Movimento -->
        <div class="info-section">
            <div class="section-title">Detalhes do Movimento</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Número do Movimento</div>
                    <div class="info-value">{{ $entradaSaida->numero_movimento }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tipo de Movimento</div>
                    <div class="info-value">{{ ucfirst($entradaSaida->tipo_movimento) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $entradaSaida->status }}">
                            {{ ucfirst(str_replace('_', ' ', $entradaSaida->status)) }}
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Terminal</div>
                    <div class="info-value">{{ $entradaSaida->terminal->nome ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Data Programada</div>
                    <div class="info-value">{{ $entradaSaida->data_programada ? $entradaSaida->data_programada->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Data Efetiva</div>
                    <div class="info-value">{{ $entradaSaida->data_efetiva ? $entradaSaida->data_efetiva->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Agente Marítimo</div>
                    <div class="info-value">{{ $entradaSaida->agente_maritimo ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Berço</div>
                    <div class="info-value">{{ $entradaSaida->berco ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Estado da Embarcação</div>
                    <div class="info-value">{{ $entradaSaida->estado_embarcacao ? ucfirst($entradaSaida->estado_embarcacao) : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Capitania de Origem</div>
                    <div class="info-value">{{ $entradaSaida->capitania_origem ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Capitania de Destino</div>
                    <div class="info-value">{{ $entradaSaida->capitania_destino ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Porto de Origem</div>
                    <div class="info-value">{{ $entradaSaida->porto_origem ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Porto de Destino</div>
                    <div class="info-value">{{ $entradaSaida->porto_destino ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ETA - Chegada Estimada</div>
                    <div class="info-value">{{ $entradaSaida->eta ? $entradaSaida->eta->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ETD - Saída Estimada</div>
                    <div class="info-value">{{ $entradaSaida->etd ? $entradaSaida->etd->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ATA - Chegada Real</div>
                    <div class="info-value">{{ $entradaSaida->ata ? $entradaSaida->ata->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ATD - Saída Real</div>
                    <div class="info-value">{{ $entradaSaida->atd ? $entradaSaida->atd->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                @if($entradaSaida->motivo)
                <div class="info-item full-width">
                    <div class="info-label">Motivo</div>
                    <div class="info-value">{{ $entradaSaida->motivo }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Documentos Apresentados -->
        @if($entradaSaida->documentos_apresentados && count($entradaSaida->documentos_apresentados) > 0)
        <div class="info-section">
            <div class="section-title">Documentos Apresentados</div>
            <ul class="documentos-list">
                @foreach($entradaSaida->documentos_apresentados as $documento)
                    <li>{{ $documento }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Informações de Autorização -->
        @if($entradaSaida->autorizado_por)
        <div class="info-section">
            <div class="section-title">Informações de Autorização</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Autorizado por</div>
                    <div class="info-value">{{ $entradaSaida->autorizadoPor->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Data de Autorização</div>
                    <div class="info-value">{{ $entradaSaida->autorizado_em ? $entradaSaida->autorizado_em->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Observações -->
        @if($entradaSaida->observacoes)
        <div class="info-section">
            <div class="section-title">Observações</div>
            <div class="info-value">{{ $entradaSaida->observacoes }}</div>
        </div>
        @endif

        <!-- Informações de Revogação -->
        @if($entradaSaida->revogado_por)
        <div class="info-section">
            <div class="section-title">Informações de Revogação</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Revogado por</div>
                    <div class="info-value">{{ $entradaSaida->revogadoPor->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Data de Revogação</div>
                    <div class="info-value">{{ $entradaSaida->revogado_em ? $entradaSaida->revogado_em->format('d/m/Y H:i') : 'N/A' }}</div>
                </div>
                @if($entradaSaida->motivo_revogacao)
                <div class="info-item full-width">
                    <div class="info-label">Motivo da Revogação</div>
                    <div class="info-value">{{ $entradaSaida->motivo_revogacao }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Informações do Sistema -->
        <div class="info-section">
            <div class="section-title">Informações do Sistema</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Criado por</div>
                    <div class="info-value">{{ $entradaSaida->user->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Data de Criação</div>
                    <div class="info-value">{{ $entradaSaida->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Última Atualização</div>
                    <div class="info-value">{{ $entradaSaida->updated_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ativo</div>
                    <div class="info-value">{{ $entradaSaida->is_active ? 'Sim' : 'Não' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária</p>
        <p>Página <span class="page-number"></span></p>
    </div>
</body>
</html>