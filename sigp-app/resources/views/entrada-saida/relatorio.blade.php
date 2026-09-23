@extends('layouts.app')

@section('title', 'Relatório de Entrada/Saída de Embarcações')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-ship mr-2"></i>
                        Relatório de Entrada/Saída de Embarcações
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-success btn-sm ml-2" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm ml-2" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('entrada-saida-embarcacao.relatorio') }}" class="d-flex flex-wrap align-items-end">
                                <div class="form-group mr-3">
                                    <label for="data_inicio" class="mr-2">Data Início:</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                           value="{{ $dataInicio }}">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="data_fim" class="mr-2">Data Fim:</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                           value="{{ $dataFim }}">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="terminal_id" class="mr-2">Terminal:</label>
                                    <select class="form-control" id="terminal_id" name="terminal_id">
                                        <option value="">Todos</option>
                                        @foreach($terminais as $terminal)
                                            <option value="{{ $terminal->id }}" {{ $terminalId == $terminal->id ? 'selected' : '' }}>
                                                {{ $terminal->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="tipo_movimento" class="mr-2">Tipo:</label>
                                    <select class="form-control" id="tipo_movimento" name="tipo_movimento">
                                        <option value="">Todos</option>
                                        <option value="entrada" {{ $tipoMovimento == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                        <option value="saida" {{ $tipoMovimento == 'saida' ? 'selected' : '' }}>Saída</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-ship"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Movimentos</span>
                                    <span class="info-box-number">{{ $estatisticasPeriodo['total_movimentos'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-arrow-down"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Entradas</span>
                                    <span class="info-box-number">{{ $estatisticasPeriodo['entradas'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Saídas</span>
                                    <span class="info-box-number">{{ $estatisticasPeriodo['saidas'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Concluídos</span>
                                    <span class="info-box-number">{{ $estatisticasPeriodo['concluidos'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-times-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cancelados</span>
                                    <span class="info-box-number">{{ $estatisticasPeriodo['cancelados'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tempo Médio</span>
                                    <span class="info-box-number">{{ number_format($estatisticasPeriodo['tempo_medio_duracao'] ?? 0, 1) }}h</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    @if($movimentos->count() > 0)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Movimentos por Tipo</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="tipoChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Status dos Movimentos</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Tabela de Movimentos -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nº Movimento</th>
                                    <th>Embarcação</th>
                                    <th>Terminal</th>
                                    <th>Tipo</th>
                                    <th>Data Programada</th>
                                    <th>Data Efetiva</th>
                                    <th>Status</th>
                                    <th>Agente Marítimo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movimentos as $movimento)
                                <tr>
                                    <td>{{ $movimento->numero_movimento }}</td>
                                    <td>{{ $movimento->embarcacao->nome ?? 'N/A' }}</td>
                                    <td>{{ $movimento->terminal->nome ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $movimento->tipo_movimento === 'entrada' ? 'success' : 'warning' }}">
                                            {{ ucfirst($movimento->tipo_movimento) }}
                                        </span>
                                    </td>
                                    <td>{{ $movimento->data_programada ? $movimento->data_programada->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>{{ $movimento->data_efetiva ? $movimento->data_efetiva->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $movimento->status === 'concluido' ? 'success' : 
                                            ($movimento->status === 'cancelado' ? 'danger' : 
                                            ($movimento->status === 'em_andamento' ? 'warning' : 'secondary')) 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $movimento->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $movimento->agente_maritimo ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum movimento encontrado no período selecionado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($movimentos->count() > 0)
// Gráfico de Tipos
const tipoCtx = document.getElementById('tipoChart').getContext('2d');
const tipoChart = new Chart(tipoCtx, {
    type: 'doughnut',
    data: {
        labels: ['Entradas', 'Saídas'],
        datasets: [{
            data: [{{ $estatisticasPeriodo['entradas'] }}, {{ $estatisticasPeriodo['saidas'] }}],
            backgroundColor: [
                '#28a745',
                '#ffc107'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Gráfico de Status
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'bar',
    data: {
        labels: ['Concluídos', 'Cancelados', 'Em Andamento', 'Programados'],
        datasets: [{
            label: 'Quantidade',
            data: [
                {{ $estatisticasPeriodo['concluidos'] }}, 
                {{ $estatisticasPeriodo['cancelados'] }}, 
                {{ $movimentos->where('status', 'em_andamento')->count() }},
                {{ $movimentos->where('status', 'programado')->count() }}
            ],
            backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#6c757d']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
@endif

function exportToExcel() {
    // Implementar exportação para Excel
    alert('Funcionalidade de exportação Excel em desenvolvimento');
}

function exportToPDF() {
    // Implementar exportação para PDF
    alert('Funcionalidade de exportação PDF em desenvolvimento');
}
</script>
@endsection