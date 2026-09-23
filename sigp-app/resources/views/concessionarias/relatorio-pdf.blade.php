<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Concessionárias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #0084de;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #0084de;
            margin: 0 0 5px 0;
        }
        
        .header h2 {
            font-size: 14px;
            color: #666;
            margin: 0 0 10px 0;
        }
        
        .header p {
            font-size: 9px;
            color: #666;
            margin: 2px 0;
        }
        
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #0084de;
            margin: 20px 0;
            text-transform: uppercase;
        }
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 9px;
            color: #666;
        }
        
        .filters-section {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #0084de;
        }
        
        .filters-title {
            font-weight: bold;
            font-size: 10px;
            color: #0084de;
            margin-bottom: 5px;
        }
        
        .filter-item {
            font-size: 9px;
            margin-bottom: 3px;
        }
        
        .filter-label {
            font-weight: bold;
            color: #333;
        }
        
        .filter-value {
            color: #666;
        }
        
        .summary-section {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .summary-item {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            min-width: 80px;
        }
        
        .summary-number {
            font-size: 16px;
            font-weight: bold;
            color: #0084de;
        }
        
        .summary-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 8px;
        }
        
        th {
            background-color: #0084de;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }
        
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .status-ativa {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
        }
        
        .status-inativa {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
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
        
        @page {
            margin: 1cm;
            size: A4 portrait;
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
        Relatório de Concessionárias
    </div>

    <!-- Informações do Relatório -->
    <div class="report-info">
        <div>
            <strong>Data de Geração:</strong> {{ now()->format('d/m/Y H:i:s') }}
        </div>
        <div>
            <strong>Total de Registros:</strong> {{ $concessionarias->count() }}
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

  

    <!-- Tabela de Concessionárias -->
    @if($concessionarias->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 20%">Nome</th>
                    <th style="width: 12%">NIF</th>
                    <th style="width: 18%">Email</th>
                    <th style="width: 12%">Telefone</th>
                    <th style="width: 8%">Status</th>
                    <th style="width: 15%">Usuário Principal</th>
                    <th style="width: 8%">Embarcações</th>
                    <th style="width: 12%">Data Cadastro</th>
                </tr>
            </thead>
            <tbody>
                @foreach($concessionarias as $concessionaria)
                <tr>
                    <td><strong>{{ $concessionaria->nome }}</strong></td>
                    <td>{{ $concessionaria->nif }}</td>
                    <td>{{ $concessionaria->email }}</td>
                    <td>{{ $concessionaria->telefone }}</td>
                    <td>
                        <span class="status-{{ $concessionaria->is_active ? 'ativa' : 'inativa' }}">
                            {{ $concessionaria->is_active ? 'Ativa' : 'Inativa' }}
                        </span>
                    </td>
                    <td>{{ $concessionaria->user ? $concessionaria->user->name : 'Não definido' }}</td>
                    <td class="text-center">{{ $concessionaria->embarcacoes->count() }}</td>
                    <td>{{ $concessionaria->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Nenhuma concessionária encontrada com os filtros aplicados.</p>
        </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        {{ $configuracoes['rodape'] ?? 'Este documento foi gerado automaticamente pelo SIGP - Sistema Integrado de Gestão Portuária' }}
        <br>
        Página <span class="pagenum"></span>
    </div>
</body>
</html>