<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Embarcações de Concessionárias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3395da;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #3395da;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .filters-section {
            margin-bottom: 20px;
        }
        
        .filters-title {
            font-weight: bold;
            color: #3395da;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .filter-item {
            margin-bottom: 5px;
            padding: 3px 0;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background-color: #3395da;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #ddd;
        }
        
        .table td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9px;
            vertical-align: top;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .table tbody tr:hover {
            background-color: #e3f2fd;
        }
        
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status.atracada {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status.navegando {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status.manutencao {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status.inativa {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $cabecalho_documentos }}</h1>
        <h2>Sistema Integrado de Gestão Portuária</h2>
        <h2>Relatório de Embarcações de Concessionárias</h2>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Data de Geração:</span>
            <span>{{ $data_geracao }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Usuário:</span>
            <span>{{ $usuario }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total de Embarcações:</span>
            <span>{{ $embarcacoes->count() }}</span>
        </div>
    </div>

    @if(!empty($filtros))
    <div class="filters-section">
        <div class="filters-title">Filtros Aplicados:</div>
        @foreach($filtros as $filtro => $valor)
        <div class="filter-item">
            <strong>{{ $filtro }}:</strong> {{ $valor }}
        </div>
        @endforeach
    </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Nome</th>
                <th style="width: 8%;">IMO</th>
                <th style="width: 8%;">MMSI</th>
                <th style="width: 8%;">Bandeira</th>
                <th style="width: 10%;">Tipo</th>
                <th style="width: 6%;">Comp.(m)</th>
                <th style="width: 6%;">Larg.(m)</th>
                <th style="width: 6%;">Calado(m)</th>
                <th style="width: 8%;">Arq.Bruta</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 12%;">Concessionária</th>
                <th style="width: 9%;">Data Cadastro</th>
            </tr>
        </thead>
        <tbody>
            @forelse($embarcacoes as $embarcacao)
            <tr>
                <td>{{ $embarcacao->nome ?? '-' }}</td>
                <td>{{ $embarcacao->imo ?? '-' }}</td>
                <td>{{ $embarcacao->mmsi ?? '-' }}</td>
                <td>{{ $embarcacao->bandeira ?? '-' }}</td>
                <td>{{ $embarcacao->tipo_embarcacao ?? '-' }}</td>
                <td>{{ $embarcacao->comprimento ?? '-' }}</td>
                <td>{{ $embarcacao->largura ?? '-' }}</td>
                <td>{{ $embarcacao->calado ?? '-' }}</td>
                <td>{{ $embarcacao->arqueacao_bruta ?? '-' }}</td>
                <td>
                    <span class="status {{ strtolower($embarcacao->status ?? '') }}">
                        {{ $embarcacao->status ?? '-' }}
                    </span>
                </td>
                <td>{{ $embarcacao->concessionaria->nome ?? '-' }}</td>
                <td>{{ $embarcacao->created_at ? $embarcacao->created_at->format('d/m/Y') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align: center; padding: 20px; color: #666;">
                    Nenhuma embarcação encontrada com os filtros aplicados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária</p>
        <p>Gerado em {{ $data_geracao }} por {{ $usuario }}</p>
    </div>
</body>
</html>