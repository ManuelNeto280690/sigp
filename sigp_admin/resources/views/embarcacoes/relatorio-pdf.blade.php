<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Embarcações</title>
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
            text-align: center;
        }
        
        .stat-item {
            flex: 1;
            padding: 10px;
            background-color: #f0f8ff;
            margin: 0 5px;
            border-radius: 5px;
            border: 1px solid #0084de;
        }
        
        .stat-number {
            font-size: 16px;
            font-weight: bold;
            color: #0084de;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 9px;
            color: #666;
        }
        
        .embarcacoes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8px;
        }
        
        .embarcacoes-table th {
            background-color: #0084de;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        .embarcacoes-table td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .embarcacoes-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .embarcacoes-table tr:hover {
            background-color: #f0f8ff;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 40px;
        }
        
        .status-ativa {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inativa {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-outros {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .tipo-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            background-color: #e3f2fd;
            color: #1565c0;
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
        
        .text-right {
            text-align: right;
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
        @if(!empty($configuracoes['logotipo']))
            <img src="{{ $configuracoes['logotipo'] }}" alt="Logo" class="logo">
        @endif
        
        <div class="header-info">
            @if(is_array($configuracoes['cabecalho']))
                <h1>{{ $configuracoes['cabecalho']['titulo'] ?? '' }}</h1>
                <h2>{{ $configuracoes['cabecalho']['subtitulo'] ?? 'Sistema Integrado de Gestão Portuária' }}</h2>
                @if(!empty($configuracoes['cabecalho']['endereco']))
                    <p>{{ $configuracoes['cabecalho']['endereco'] }}</p>
                @endif
                @if(!empty($configuracoes['cabecalho']['telefone']))
                    <p>Tel: {{ $configuracoes['cabecalho']['telefone'] }}</p>
                @endif
                @if(!empty($configuracoes['cabecalho']['email']))
                    <p>Email: {{ $configuracoes['cabecalho']['email'] }}</p>
                @endif
            @else
                <h1>PORTO DE SOYO</h1>
                <h2>Sistema Integrado de Gestão Portuária</h2>
            @endif
        </div>
    </div>

    <!-- Título do Relatório -->
    <div class="report-title">
        <h3>RELATÓRIO DE EMBARCAÇÕES</h3>
    </div>

    <!-- Informações do Relatório -->
    <div class="report-info">
        <div>
            <strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i:s') }}
        </div>
        <div>
            <strong>Total de Registros:</strong> {{ $embarcacoes->count() }}
        </div>
        <div>
            <strong>Gerado por:</strong> {{ Auth::user()->name }}
        </div>
    </div>

    <!-- Filtros Aplicados -->
    @if(!empty($filtrosAplicados))
    <div class="filters-section">
        <div class="filters-title">Filtros Aplicados:</div>
        @foreach($filtrosAplicados as $label => $value)
            <div class="filter-item">
                <span class="filter-label">{{ $label }}:</span> {{ $value }}
            </div>
        @endforeach
    </div>
    @endif


    <!-- Tabela de Embarcações -->
    @if($embarcacoes->count() > 0)
        <table class="embarcacoes-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Nome</th>
                    <th style="width: 8%;">IMO</th>
                    <th style="width: 8%;">MMSI</th>
                    <th style="width: 10%;">Bandeira</th>
                    <th style="width: 12%;">Tipo</th>
                    <th style="width: 6%;">Comp.</th>
                    <th style="width: 6%;">Larg.</th>
                    <th style="width: 6%;">Calado</th>
                    <th style="width: 8%;">Arq. Bruta</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 13%;">Data Cadastro</th>
                </tr>
            </thead>
            <tbody>
                @foreach($embarcacoes as $embarcacao)
                    <tr>
                        <td><strong>{{ $embarcacao->nome }}</strong></td>
                        <td>{{ $embarcacao->imo ?? 'N/A' }}</td>
                        <td>{{ $embarcacao->mmsi ?? 'N/A' }}</td>
                        <td>{{ $embarcacao->bandeira }}</td>
                        <td>
                            <span class="tipo-badge">{{ $embarcacao->tipo_embarcacao }}</span>
                        </td>
                        <td class="text-center">{{ $embarcacao->comprimento ? $embarcacao->comprimento . 'm' : 'N/A' }}</td>
                        <td class="text-center">{{ $embarcacao->largura ? $embarcacao->largura . 'm' : 'N/A' }}</td>
                        <td class="text-center">{{ $embarcacao->calado ? $embarcacao->calado . 'm' : 'N/A' }}</td>
                        <td class="text-center">{{ $embarcacao->arqueacao_bruta ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($embarcacao->status == 'Ativa')
                                <span class="status-badge status-ativa">Ativa</span>
                            @elseif($embarcacao->status == 'Inativa')
                                <span class="status-badge status-inativa">Inativa</span>
                            @else
                                <span class="status-badge status-outros">{{ $embarcacao->status }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $embarcacao->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Nenhuma embarcação encontrada com os filtros aplicados.</p>
        </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        <p>{{ $configuracoes['rodape'] }}</p>
        <p>Página <span class="pagenum"></span> - Gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>