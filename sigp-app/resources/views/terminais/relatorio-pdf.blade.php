<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Terminais</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #0084de;
        }
        
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #0084de;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 14px;
            margin: 0 0 10px 0;
            color: #666;
            font-weight: normal;
        }
        
        .header p {
            margin: 2px 0;
            font-size: 9px;
            color: #666;
        }
        
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            color: #0084de;
            text-transform: uppercase;
        }
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 9px;
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
        }
        
        .report-info div {
            flex: 1;
        }
        
        .filters-section {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #e3f2fd;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .filters-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #0084de;
        }
        
        .filter-item {
            margin-bottom: 2px;
        }
        
        .filter-label {
            font-weight: bold;
        }
        
        .filter-value {
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8px;
        }
        
        th {
            background-color: #0084de;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-ativo {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 40px;
        }
        
        .status-inativo {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 40px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
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
        @if(!empty($configuracoes['cabecalho']))
            <h1>{{ $configuracoes['cabecalho']['titulo'] ?? 'PORTO DE SOYO' }}</h1>
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

    <!-- Título do Relatório -->
    <div class="report-title">
        Relatório de Terminais
    </div>

    <!-- Informações do Relatório -->
    <div class="report-info">
        <div>
            <strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i:s') }}
        </div>
        <div>
            <strong>Total de Registros:</strong> {{ $terminais->count() }}
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

    <!-- Tabela de Terminais -->
    @if($terminais->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 18%">Nome</th>
                    <th style="width: 10%">Código</th>
                    <th style="width: 12%">Tipo</th>
                    <th style="width: 15%">Localização</th>
                    <th style="width: 15%">Concessionária</th>
                    <th style="width: 8%">Berços</th>
                    <th style="width: 8%">Calado Máx.</th>
                    <th style="width: 6%">Status</th>
                    <th style="width: 8%">Data Cadastro</th>
                </tr>
            </thead>
            <tbody>
                @foreach($terminais as $terminal)
                <tr>
                    <td><strong>{{ $terminal->nome }}</strong></td>
                    <td>{{ $terminal->codigo }}</td>
                    <td>{{ $terminal->tipo }}</td>
                    <td>{{ $terminal->localizacao }}</td>
                    <td>{{ $terminal->concessionaria ? $terminal->concessionaria->nome : 'Não definida' }}</td>
                    <td class="text-center">{{ $terminal->numero_bercos }}</td>
                    <td class="text-center">{{ $terminal->calado_maximo ? $terminal->calado_maximo . 'm' : 'N/A' }}</td>
                    <td class="text-center">
                        <span class="status-{{ $terminal->is_active ? 'ativo' : 'inativo' }}">
                            {{ $terminal->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="text-center">{{ $terminal->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Nenhum terminal encontrado com os filtros aplicados.</p>
        </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        <br>
        Página <span class="pagenum"></span>
    </div>
</body>
</html>