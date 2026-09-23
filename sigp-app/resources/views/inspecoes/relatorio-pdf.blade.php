<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Inspeções de Navios</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
            padding: 0;
        }

        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2563eb;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            color: #374151;
            margin-bottom: 8px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 5px;
        }

        .info-item {
            font-size: 9px;
        }

        .info-label {
            font-weight: bold;
            color: #374151;
        }

        .filters-section {
            margin-bottom: 15px;
            background-color: #f1f5f9;
            padding: 10px;
            border-radius: 5px;
        }

        .filters-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
        }

        .filter-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
            font-size: 9px;
        }

        .filter-label {
            font-weight: bold;
            color: #374151;
        }

        .stats-section {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .stat-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 8px;
            text-align: center;
            min-width: 80px;
            flex: 1;
        }

        .stat-number {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
        }

        .stat-label {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }

        .table-container {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        th {
            background-color: #1e40af;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e40af;
        }

        td {
            padding: 5px 4px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tr:hover {
            background-color: #f3f4f6;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-agendada { background-color: #fef3c7; color: #92400e; }
        .badge-em_andamento { background-color: #dbeafe; color: #1e40af; }
        .badge-concluida { background-color: #d1fae5; color: #065f46; }
        .badge-cancelada { background-color: #fee2e2; color: #991b1b; }

        .badge-aprovada { background-color: #d1fae5; color: #065f46; }
        .badge-reprovada { background-color: #fee2e2; color: #991b1b; }
        .badge-pendente { background-color: #fef3c7; color: #92400e; }

        .footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }

        .page-break {
            page-break-before: always;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1>{{ $cabecalho_documentos }}</h1>
        <h2>RELATÓRIO DE INSPEÇÕES DE NAVIOS</h2>
    </div>

    <!-- Informações do Relatório -->
    <div class="info-section">
        <div class="info-item">
            <span class="info-label">Data de Geração:</span> {{ $data_geracao }}
        </div>
        <div class="info-item">
            <span class="info-label">Usuário:</span> {{ $usuario }}
        </div>
        <div class="info-item">
            <span class="info-label">Total de Registros:</span> {{ $estatisticas['total'] }}
        </div>
    </div>

  
  

    <!-- Tabela de Inspeções -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Número</th>
                    <th style="width: 12%;">Data</th>
                    <th style="width: 12%;">Tipo</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Resultado</th>
                    <th style="width: 15%;">Embarcação</th>
                    <th style="width: 12%;">Inspetor</th>
                    <th style="width: 12%;">Local</th>
                    <th style="width: 7%;">Observações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspecoes as $index => $inspecao)
                    @if($index > 0 && $index % 25 == 0)
                        </tbody>
                        </table>
                        </div>
                        <div class="page-break"></div>
                        <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Número</th>
                                    <th style="width: 12%;">Data</th>
                                    <th style="width: 12%;">Tipo</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;">Resultado</th>
                                    <th style="width: 15%;">Embarcação</th>
                                    <th style="width: 12%;">Inspetor</th>
                                    <th style="width: 12%;">Local</th>
                                    <th style="width: 7%;">Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                    @endif
                    <tr>
                        <td>{{ $inspecao->numero_inspecao ?? 'N/A' }}</td>
                        <td>{{ $inspecao->data_inspecao ? \Carbon\Carbon::parse($inspecao->data_inspecao)->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>{{ $inspecao->tipo_inspecao ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $inspecao->status }}">
                                {{ $inspecao->status ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @if($inspecao->resultado)
                                <span class="badge badge-{{ $inspecao->resultado }}">
                                    {{ $inspecao->resultado }}
                                </span>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $inspecao->embarcacao->nome ?? 'N/A' }}</td>
                        <td>{{ $inspecao->inspetor->name ?? 'N/A' }}</td>
                        <td>{{ $inspecao->local_inspecao ?? 'N/A' }}</td>
                        <td>{{ Str::limit($inspecao->observacoes ?? '', 50) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Nenhuma inspeção encontrada com os filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <div>{{ $cabecalho_documentos }} - Relatório de Inspeções de Navios</div>
        <div>Gerado em {{ $data_geracao }} por {{ $usuario }}</div>
    </div>
</body>
</html>