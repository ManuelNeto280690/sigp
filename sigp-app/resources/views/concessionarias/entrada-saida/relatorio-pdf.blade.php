<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Movimentos de Entrada/Saída</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0084de;
            padding-bottom: 10px;
        }
        
        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }
        
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #0084de;
            margin: 10px 0;
        }
        
        .subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-section {
            margin: 15px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border-left: 4px solid #0084de;
        }
        
        .info-row {
            margin: 3px 0;
        }
        
        .filters {
            margin: 15px 0;
            padding: 10px;
            background-color: #f1f5f9;
            border-radius: 4px;
        }
        
        .filters h4 {
            margin: 0 0 8px 0;
            color: #0084de;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #0084de;
            color: white;
            font-weight: bold;
            font-size: 8px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status.pendente { background-color: #fef3c7; color: #92400e; }
        .status.autorizado { background-color: #dbeafe; color: #1e40af; }
        .status.em_andamento { background-color: #fde68a; color: #92400e; }
        .status.concluido { background-color: #d1fae5; color: #065f46; }
        .status.cancelado { background-color: #fee2e2; color: #991b1b; }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($logo)
            <img src="data:image/png;base64,{{ $logo }}" alt="Logo" class="logo">
        @endif
        <div class="title">{{ $cabecalho }}</div>
        <div class="subtitle">Relatório de Movimentos de Entrada/Saída de Embarcações</div>
    </div>

    <div class="info-section">
        <div class="info-row"><strong>Data de Geração:</strong> {{ $data_geracao }}</div>
        <div class="info-row"><strong>Gerado por:</strong> {{ $usuario }}</div>
        <div class="info-row"><strong>Total de Movimentos:</strong> {{ $movimentos->count() }}</div>
    </div>

    @if(!empty($filtros))
    <div class="filters">
        <h4>Filtros Aplicados:</h4>
        @foreach($filtros as $key => $value)
            @if($value)
                <div class="info-row">
                    <strong>
                        @switch($key)
                            @case('search') Busca: @break
                            @case('status') Status: @break
                            @case('tipo_movimento') Tipo de Movimento: @break
                            @case('terminal_id') Terminal: @break
                            @case('concessionaria_id') Concessionária: @break
                            @case('data_inicio') Data Início: @break
                            @case('data_fim') Data Fim: @break
                            @default {{ ucfirst($key) }}: @break
                        @endswitch
                    </strong>
                    {{ $value }}
                </div>
            @endif
        @endforeach
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Nº Movimento</th>
                <th style="width: 12%;">Embarcação</th>
                <th style="width: 8%;">IMO</th>
                <th style="width: 12%;">Concessionária</th>
                <th style="width: 10%;">Terminal</th>
                <th style="width: 8%;">Tipo</th>
                <th style="width: 8%;">Data</th>
                <th style="width: 6%;">H. Prev.</th>
                <th style="width: 6%;">H. Real</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 10%;">Usuário</th>
                <th style="width: 4%;">Criado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimentos as $movimento)
                <tr>
                    <td>{{ $movimento->numero_movimento }}</td>
                    <td>{{ $movimento->embarcacaoConcessionaria->nome ?? '-' }}</td>
                    <td>{{ $movimento->embarcacaoConcessionaria->imo ?? '-' }}</td>
                    <td>{{ $movimento->concessionaria->nome ?? '-' }}</td>
                    <td>{{ $movimento->terminal->nome ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $movimento->tipo_movimento)) }}</td>
                    <td>{{ $movimento->data_movimento ? \Carbon\Carbon::parse($movimento->data_movimento)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $movimento->hora_prevista ? \Carbon\Carbon::parse($movimento->hora_prevista)->format('H:i') : '-' }}</td>
                    <td>{{ $movimento->hora_real ? \Carbon\Carbon::parse($movimento->hora_real)->format('H:i') : '-' }}</td>
                    <td>
                        <span class="status {{ $movimento->status }}">
                            {{ ucfirst(str_replace('_', ' ', $movimento->status)) }}
                        </span>
                    </td>
                    <td>{{ $movimento->usuario->name ?? '-' }}</td>
                    <td>{{ $movimento->created_at->format('d/m/y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 20px; color: #666;">
                        Nenhum movimento encontrado com os filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($rodape)
    <div class="footer">
        {{ $rodape }}
    </div>
    @endif
</body>
</html>