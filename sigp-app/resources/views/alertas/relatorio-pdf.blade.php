<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Alertas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        
        .filters h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .filter-item {
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        
        .status-ativo {
            color: #007bff;
            font-weight: bold;
        }
        
        .status-resolvido {
            color: #28a745;
            font-weight: bold;
        }
        
        .nivel-emergencia {
            color: #dc3545;
            font-weight: bold;
        }
        
        .nivel-critico {
            color: #fd7e14;
            font-weight: bold;
        }
        
        .nivel-aviso {
            color: #ffc107;
            font-weight: bold;
        }
        
        .nivel-info {
            color: #17a2b8;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
    
    <div class="header">
        <div class="subtitle">Relatório de Alertas</div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <strong>Data de Geração:</strong>
            <span>{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <strong>Total de Alertas:</strong>
            <span>{{ $alertas->count() }}</span>
        </div>
        <div class="info-row">
            <strong>Gerado por:</strong>
            <span>{{ auth()->user()->name }}</span>
        </div>
    </div>
    
    @if(array_filter($filtros))
    <div class="filters">
        <h4>Filtros Aplicados:</h4>
        @if($filtros['search'])
            <div class="filter-item"><strong>Busca:</strong> {{ $filtros['search'] }}</div>
        @endif
        @if($filtros['status'])
            <div class="filter-item"><strong>Status:</strong> {{ ucfirst($filtros['status']) }}</div>
        @endif
        @if($filtros['nivel'])
            <div class="filter-item"><strong>Nível:</strong> {{ ucfirst($filtros['nivel']) }}</div>
        @endif
        @if($filtros['tipo'])
            <div class="filter-item"><strong>Tipo:</strong> {{ ucfirst($filtros['tipo']) }}</div>
        @endif
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Nível</th>
                <th>Status</th>
                <th>Criado por</th>
                <th>Data Criação</th>
                <th>Resolvido por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alertas as $alerta)
                <tr>
                    <td>{{ $alerta->id }}</td>
                    <td>{{ $alerta->titulo }}</td>
                    <td>{{ ucfirst($alerta->tipo) }}</td>
                    <td class="nivel-{{ $alerta->nivel }}">{{ ucfirst($alerta->nivel) }}</td>
                    <td class="status-{{ $alerta->status }}">{{ ucfirst($alerta->status) }}</td>
                    <td>{{ $alerta->user->name ?? 'N/A' }}</td>
                    <td>{{ $alerta->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $alerta->resolvidoPor->name ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        Nenhum alerta encontrado com os filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Relatório gerado automaticamente pelo Sistema Integrado de Gestão Portuária</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>