<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Pedidos</title>
    <style>
        @page {
            margin: 2cm 1.5cm 3cm 1.5cm;
            size: A4 landscape;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0084de;
            padding-bottom: 20px;
        }
        
        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }
        
        .header-info h1 {
            color: #0084de;
            font-size: 18px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .header-info h2 {
            color: #666;
            font-size: 14px;
            margin: 0 0 10px 0;
            font-weight: normal;
        }
        
        .header-info p {
            margin: 2px 0;
            font-size: 9px;
            color: #666;
        }
        
        .report-title {
            text-align: center;
            margin: 20px 0;
        }
        
        .report-title h3 {
            color: #0084de;
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 9px;
        }
        
        .filters-section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .filters-title {
            font-weight: bold;
            color: #0084de;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .filter-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
            font-size: 9px;
        }
        
        .filter-label {
            font-weight: bold;
            color: #333;
        }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #0084de;
            display: block;
        }
        
        .stat-label {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 8px;
        }
        
        .data-table th {
            background-color: #0084de;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        .data-table td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-aprovado {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejeitado {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            position: fixed;
            bottom: 1cm;
            left: 1.5cm;
            right: 1.5cm;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .text-center {
            text-align: center;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        @if(is_array($configuracoes['cabecalho']))
            <h1>{{ $configuracoes['cabecalho']['titulo'] ?? 'PORTO DE SOYO' }}</h1>
            <h2>{{ $configuracoes['cabecalho']['subtitulo'] ?? 'Sistema Integrado de Gestão Portuária' }}</h2>
            @if(!empty($configuracoes['cabecalho']['endereco']))
                <p>{{ $configuracoes['cabecalho']['endereco'] }}</p>
            @endif
            @if(!empty($configuracoes['cabecalho']['telefone']) || !empty($configuracoes['cabecalho']['email']))
                <p>
                    @if(!empty($configuracoes['cabecalho']['telefone']))
                        Tel: {{ $configuracoes['cabecalho']['telefone'] }}
                    @endif
                    @if(!empty($configuracoes['cabecalho']['email']))
                        | Email: {{ $configuracoes['cabecalho']['email'] }}
                    @endif
                </p>
            @endif
        @else
            <h1>PORTO DE SOYO</h1>
            <h2>Sistema Integrado de Gestão Portuária</h2>
        @endif
    </div>

    <!-- Título do Relatório -->
    <div class="report-title">
        <h3>RELATÓRIO DE PEDIDOS</h3>
    </div>

    <!-- Informações do Relatório -->
    <div class="report-info">
        <div>
            <strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i:s') }}
        </div>
        <div>
            <strong>Total de Registros:</strong> {{ $pedidos->count() }}
        </div>
        <div>
            <strong>Gerado por:</strong> {{ Auth::user()->name }}
        </div>
    </div>

    <!-- Filtros Aplicados -->
    @if(!empty($filtrosAplicados))
    <div class="filters-section">
        <div class="filters-title">Filtros Aplicados:</div>
        @foreach($filtrosAplicados as $filtro => $valor)
        <div class="filter-item">
            <span class="filter-label">{{ $filtro }}:</span>
            <span class="filter-value">{{ $valor }}</span>
        </div>
        @endforeach
    </div>
    @endif

   
    <!-- Tabela de Dados -->
    @if($pedidos->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Data Criação</th>
                <th style="width: 12%;">Nome do Navio</th>
                <th style="width: 10%;">IMO</th>
                <th style="width: 10%;">Indicativo</th>
                <th style="width: 8%;">Data Chegada</th>
                <th style="width: 12%;">Nome do Agente</th>
                <th style="width: 10%;">Solicitante</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 10%;">Decisor</th>
                <th style="width: 12%;">Observações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $pedido)
            <tr>
                <td>{{ $pedido->created_at->format('d/m/Y') }}</td>
                <td>{{ $pedido->nome_navio }}</td>
                <td>{{ $pedido->numero_imo }}</td>
                <td>{{ $pedido->indicativo_chamada }}</td>
                <td>{{ $pedido->data_chegada ? date('d/m/Y', strtotime($pedido->data_chegada)) : '-' }}</td>
                <td>{{ $pedido->nome_agente }}</td>
                <td>{{ $pedido->user->name ?? '-' }}</td>
                <td>
                    <span class="status-badge status-{{ $pedido->status }}">
                        {{ ucfirst($pedido->status) }}
                    </span>
                </td>
                <td>{{ $pedido->decisor->name ?? '-' }}</td>
                <td>{{ Str::limit($pedido->observacoes, 50) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>Nenhum pedido encontrado com os filtros aplicados.</p>
    </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        {{ $configuracoes['rodape'] }}
    </div>
</body>
</html>