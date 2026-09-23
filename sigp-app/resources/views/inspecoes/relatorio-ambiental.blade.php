@extends('layouts.app')

@section('title', 'Relatório de Inspeções Ambientais')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-leaf mr-2"></i>
                        Relatório de Inspeções Ambientais
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="GET" action="{{ route('inspecoes-ambientais.relatorio') }}" class="form-inline">
                                <div class="form-group mr-3">
                                    <label for="data_inicio" class="mr-2">Data Início:</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                           value="{{ request('data_inicio', $periodo['inicio']) }}">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="data_fim" class="mr-2">Data Fim:</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                           value="{{ request('data_fim', $periodo['fim']) }}">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="status" class="mr-2">Status:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Todos</option>
                                        <option value="agendada" {{ request('status') == 'agendada' ? 'selected' : '' }}>Agendada</option>
                                        <option value="em andamento" {{ request('status') == 'em andamento' ? 'selected' : '' }}>Em Andamento</option>
                                        <option value="concluída" {{ request('status') == 'concluída' ? 'selected' : '' }}>Concluída</option>
                                        <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="resultado" class="mr-2">Resultado:</label>
                                    <select class="form-control" id="resultado" name="resultado">
                                        <option value="">Todos</option>
                                        <option value="aprovado" {{ request('resultado') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                                        <option value="reprovado" {{ request('resultado') == 'reprovado' ? 'selected' : '' }}>Reprovado</option>
                                        <option value="aprovado_com_restricoes" {{ request('resultado') == 'aprovado_com_restricoes' ? 'selected' : '' }}>Aprovado com Restrições</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $estatisticas['total'] }}</h3>
                                    <p>Total de Inspeções</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-leaf"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $estatisticas['concluidas'] }}</h3>
                                    <p>Concluídas</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $estatisticas['em_andamento'] }}</h3>
                                    <p>Em Andamento</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $estatisticas['com_infracoes'] }}</h3>
                                    <p>Com Infrações</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Inspeções por Resultado</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="resultadoChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Inspeções por Mês</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="mesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Inspeções -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Embarcação</th>
                                    <th>Data</th>
                                    <th>Inspetor</th>
                                    <th>Status</th>
                                    <th>Resultado</th>
                                    <th>Não Conformidades</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inspecoes as $inspecao)
                                <tr>
                                    <td>{{ $inspecao->numero_inspecao }}</td>
                                    <td>{{ $inspecao->embarcacao->nome ?? 'N/A' }}</td>
                                    <td>{{ $inspecao->data_inspecao ? $inspecao->data_inspecao->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $inspecao->inspetor->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $inspecao->status == 'concluída' ? 'success' : 
                                            ($inspecao->status == 'em andamento' ? 'warning' : 
                                            ($inspecao->status == 'cancelada' ? 'danger' : 'info')) 
                                        }}">
                                            {{ ucfirst($inspecao->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($inspecao->resultado)
                                        <span class="badge badge-{{ 
                                            $inspecao->resultado == 'aprovado' ? 'success' : 
                                            ($inspecao->resultado == 'reprovado' ? 'danger' : 'warning') 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $inspecao->resultado)) }}
                                        </span>
                                        @else
                                        <span class="text-muted">Pendente</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inspecao->nao_conformidades && count($inspecao->nao_conformidades) > 0)
                                        <span class="badge badge-danger">{{ count($inspecao->nao_conformidades) }}</span>
                                        @else
                                        <span class="text-success">Nenhuma</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('inspecoes.show', $inspecao) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Nenhuma inspeção ambiental encontrada no período selecionado.</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Resultados
const resultadoCtx = document.getElementById('resultadoChart').getContext('2d');
const resultadoChart = new Chart(resultadoCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($estatisticas['por_resultado']->keys()) !!},
        datasets: [{
            data: {!! json_encode($estatisticas['por_resultado']->values()) !!},
            backgroundColor: [
                '#28a745',
                '#dc3545',
                '#ffc107',
                '#17a2b8'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Gráfico por Mês
const mesCtx = document.getElementById('mesChart').getContext('2d');
const mesChart = new Chart(mesCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($estatisticas['por_mes']->keys()) !!},
        datasets: [{
            label: 'Inspeções por Mês',
            data: {!! json_encode($estatisticas['por_mes']->values()) !!},
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.1
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

function exportToExcel() {
    alert('Exportação para Excel em desenvolvimento');
}

function exportToPDF() {
    alert('Exportação para PDF em desenvolvimento');
}
</script>
@endpush
@endsection