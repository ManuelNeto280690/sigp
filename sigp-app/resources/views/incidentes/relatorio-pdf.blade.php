<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Incidentes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #007bff;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }
        
        .header h2 {
            color: #666;
            font-size: 16px;
            margin: 5px 0;
            font-weight: normal;
        }
        
        .header h3 {
            color: #007bff;
            font-size: 18px;
            margin: 15px 0 5px 0;
            font-weight: bold;
        }
        
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .info-section h4 {
            color: #007bff;
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .info-item {
            font-size: 11px;
        }
        
        .info-item strong {
            color: #374151;
        }
        
        .filters {
            background-color: #f1f5f9;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .filters h4 {
            color: #1e40af;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        
        .filter-item {
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-aberto {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .status-investigando {
            background-color: #fed7aa;
            color: #c2410c;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .status-resolvido {
            background-color: #dcfce7;
            color: #166534;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .status-fechado {
            background-color: #f3f4f6;
            color: #374151;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .gravidade-critica {
            background-color: #fecaca;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .gravidade-alta {
            background-color: #fed7aa;
            color: #c2410c;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .gravidade-media {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .gravidade-baixa {
            background-color: #dcfce7;
            color: #166534;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1>{{ $configuracoes['cabecalho']['titulo'] ?? 'PORTO DE SOYO' }}</h1>
        <h2>{{ $configuracoes['cabecalho']['subtitulo'] ?? 'Sistema Integrado de Gestão Portuária' }}</h2>
        <h3>Relatório de Incidentes</h3>
    </div>

    <!-- Informações do Relatório -->
    <div class="info-section">
        <h4>📊 Informações do Relatório</h4>
        <div class="info-grid">
            <div class="info-item">
                <strong>Data de Geração:</strong><br>
                {{ now()->format('d/m/Y H:i:s') }}
            </div>
            <div class="info-item">
                <strong>Total de Incidentes:</strong><br>
                {{ $incidentes->count() }}
            </div>
            <div class="info-item">
                <strong>Gerado por:</strong><br>
                {{ Auth::user()->name }}
            </div>
            <div class="info-item">
                <strong>Período:</strong><br>
                @if($filtros['data_inicio'] && $filtros['data_fim'])
                    {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') }}
                @else
                    Todos os períodos
                @endif
            </div>
        </div>
    </div>

   

    <!-- Tabela de Incidentes -->
    @if($incidentes->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Número</th>
                    <th style="width: 20%;">Título</th>
                    <th style="width: 10%;">Tipo</th>
                    <th style="width: 10%;">Gravidade</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 15%;">Local</th>
                    <th style="width: 12%;">Data</th>
                    <th style="width: 13%;">Responsável</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incidentes as $incidente)
                <tr>
                    <td>{{ $incidente->numero_incidente }}</td>
                    <td>{{ $incidente->titulo }}</td>
                    <td>{{ ucfirst($incidente->tipo) }}</td>
                    <td>
                        <span class="gravidade-{{ $incidente->gravidade }}">
                            {{ ucfirst($incidente->gravidade) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-{{ $incidente->status }}">
                            @if($incidente->status == 'aberto') Aberto
                            @elseif($incidente->status == 'investigando') Investigando
                            @elseif($incidente->status == 'resolvido') Resolvido
                            @else Fechado
                            @endif
                        </span>
                    </td>
                    <td>{{ $incidente->local_ocorrencia }}</td>
                    <td>{{ $incidente->data_ocorrencia->format('d/m/Y H:i') }}</td>
                    <td>{{ $incidente->user->name ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; background-color: #f9f9f9; border: 1px solid #ddd;">
            <h3 style="color: #666;">Nenhum incidente encontrado</h3>
            <p style="color: #999;">Não há incidentes que correspondam aos filtros aplicados.</p>
        </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária</p>
        <p>{{ $configuracoes['cabecalho']['titulo'] ?? 'PORTO DE SOYO' }} | {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>