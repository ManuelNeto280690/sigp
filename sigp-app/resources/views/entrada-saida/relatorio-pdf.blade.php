<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Movimentos de Entrada/Saída</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px 0;
            border-bottom: 2px solid #0084de;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #0084de;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            color: #666;
            margin-bottom: 3px;
        }

        .header p {
            font-size: 9px;
            color: #888;
        }

        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .filters-section {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .filters-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
            color: #495057;
        }

        .filter-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 3px;
            font-size: 9px;
        }

        .filter-label {
            font-weight: bold;
            color: #6c757d;
        }

        .filter-value {
            color: #495057;
        }

        .summary-section {
            margin-bottom: 15px;
            text-align: center;
        }

        .summary-item {
            display: inline-block;
            margin: 0 10px;
            padding: 5px 10px;
            background: #e9ecef;
            border-radius: 3px;
            font-size: 9px;
        }

        .summary-label {
            font-weight: bold;
            color: #495057;
        }

        .summary-value {
            color: #0084de;
            font-weight: bold;
        }

        .table-container {
            margin: 15px 20px 0 20px;
        }

        table {
            width: calc(100% - 40px);
            margin: 0 20px;
            border-collapse: collapse;
            font-size: 8px;
        }

        th {
            background: #0084de;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0084de;
        }

        td {
            padding: 4px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pendente { background: #fff3cd; color: #856404; }
        .status-autorizado { background: #d4edda; color: #155724; }
        .status-em_andamento { background: #cce5ff; color: #004085; }
        .status-concluido { background: #d1ecf1; color: #0c5460; }
        .status-cancelado { background: #f8d7da; color: #721c24; }

        .tipo-entrada { color: #28a745; font-weight: bold; }
        .tipo-saida { color: #dc3545; font-weight: bold; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #6c757d;
            padding: 10px;
            border-top: 1px solid #dee2e6;
            background: white;
        }

        .page-break {
            page-break-before: always;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }

        @page {
            margin: 1cm;
            size: A4 landscape;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1>{{ $configuracoes['cabecalho']['titulo'] ?? 'PORTO DE SOYO' }}</h1>
        <h2>{{ $configuracoes['cabecalho']['subtitulo'] ?? 'Sistema Integrado de Gestão Portuária' }}</h2>
        <p>{{ $configuracoes['cabecalho']['endereco'] ?? '' }}</p>
        @if(isset($configuracoes['cabecalho']['telefone']) || isset($configuracoes['cabecalho']['email']))
        <p>
            @if(isset($configuracoes['cabecalho']['telefone']))
                Tel: {{ $configuracoes['cabecalho']['telefone'] }}
            @endif
            @if(isset($configuracoes['cabecalho']['email']))
                | Email: {{ $configuracoes['cabecalho']['email'] }}
            @endif
        </p>
        @endif
    </div>

    <!-- Título do Relatório -->
    <div class="report-title">
        Relatório de Movimentos de Entrada/Saída
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

    <!-- Resumo -->
    <div class="summary-section">
        <div class="summary-item">
            <span class="summary-label">Total de Movimentos:</span>
            <span class="summary-value">{{ $movimentos->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Entradas:</span>
            <span class="summary-value">{{ $movimentos->where('tipo_movimento', 'entrada')->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Saídas:</span>
            <span class="summary-value">{{ $movimentos->where('tipo_movimento', 'saida')->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Gerado em:</span>
            <span class="summary-value">{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    <!-- Tabela de Movimentos -->
    @if($movimentos->count() > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Número</th>
                    <th style="width: 6%;">Tipo</th>
                    <th style="width: 18%;">Embarcação</th>
                    <th style="width: 8%;">IMO</th>
                    <th style="width: 15%;">Terminal</th>
                    <th style="width: 10%;">Data/Hora</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 12%;">Agente</th>
                    <th style="width: 15%;">Motivo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimentos as $movimento)
                <tr>
                    <td>{{ $movimento->numero_movimento ?? 'N/A' }}</td>
                    <td>
                        <span class="tipo-{{ $movimento->tipo_movimento }}">
                            {{ ucfirst($movimento->tipo_movimento ?? 'N/A') }}
                        </span>
                    </td>
                    <td>{{ $movimento->embarcacao->nome ?? 'N/A' }}</td>
                    <td>{{ $movimento->embarcacao->imo ?? 'N/A' }}</td>
                    <td>{{ $movimento->terminal->nome ?? 'N/A' }}</td>
                    <td>{{ $movimento->data_programada ? \Carbon\Carbon::parse($movimento->data_programada)->format('d/m/Y H:i') : 'N/A' }}</td>
                    <td>
                        <span class="status-badge status-{{ $movimento->status }}">
                            {{ ucfirst(str_replace('_', ' ', $movimento->status ?? 'N/A')) }}
                        </span>
                    </td>
                    <td>{{ $movimento->agente_maritimo ?? 'N/A' }}</td>
                    <td>{{ Str::limit($movimento->motivo ?? 'N/A', 30) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="no-data">
        <p>Nenhum movimento encontrado com os filtros aplicados.</p>
    </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        <p>{{ $configuracoes['rodape'] }}</p>
        <p>Página <span class="pagenum"></span> - Gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>