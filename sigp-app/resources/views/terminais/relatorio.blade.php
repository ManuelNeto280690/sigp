@extends('layouts.app')

@section('title', 'Relatório de Terminais')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-building mr-2"></i>
                        Relatório de Terminais
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
                            <form method="GET" action="{{ route('terminais.relatorio') }}" class="form-inline">
                                <div class="form-group mr-3">
                                    <label for="data_inicio" class="mr-2">Data Início:</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                           value="{{ request('data_inicio', now()->subMonth()->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="data_fim" class="mr-2">Data Fim:</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                           value="{{ request('data_fim', now()->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="status" class="mr-2">Status:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Todos</option>
                                        <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativo</option>
                                        <option value="inativo" {{ request('status') == 'inativo' ? 'selected' : '' }}>Inativo</option>
                                        <option value="manutencao" {{ request('status') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="tipo" class="mr-2">Tipo:</label>
                                    <select class="form-control" id="tipo" name="tipo">
                                        <option value="">Todos</option>
                                        <option value="container" {{ request('tipo') == 'container' ? 'selected' : '' }}>Container</option>
                                        <option value="graneis" {{ request('tipo') == 'graneis' ? 'selected' : '' }}>Granéis</option>
                                        <option value="multipropósito" {{ request('tipo') == 'multipropósito' ? 'selected' : '' }}>Multipropósito</option>
                                        <option value="passageiros" {{ request('tipo') == 'passageiros' ? 'selected' : '' }}>Passageiros</option>
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
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total</span>
                                    <span class="info-box-number">{{ $estatisticas['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Ativos</span>
                                    <span class="info-box-number">{{ $estatisticas['ativos'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-times-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Inativos</span>
                                    <span class="info-box-number">{{ $estatisticas['inativos'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-wrench"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Manutenção</span>
                                    <span class="info-box-number">{{ $estatisticas['em_manutencao'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-anchor"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Berços</span>
                                    <span class="info-box-number">{{ $estatisticas['bercos_total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary">
                                    <i class="fas fa-warehouse"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Capacidade</span>
                                    <span class="info-box-number">{{ number_format($estatisticas['capacidade_total'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico por Tipo -->
                    @if($estatisticas['por_tipo']->count() > 0)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Terminais por Tipo</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="tipoChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Status dos Terminais</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Tabela de Terminais -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Concessionária</th>
                                    <th>Status</th>
                                    <th>Berços</th>
                                    <th>Capacidade</th>
                                    <th>Calado Máx.</th>
                                    <th>Data Criação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($terminais as $terminal)
                                <tr>
                                    <td>{{ $terminal->codigo }}</td>
                                    <td>{{ $terminal->nome }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst($terminal->tipo) }}
                                        </span>
                                    </td>
                                    <td>{{ $terminal->concessionaria->nome ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $terminal->status === 'ativo' ? 'success' : ($terminal->status === 'inativo' ? 'secondary' : 'warning') }}">
                                            {{ ucfirst($terminal->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $terminal->numero_bercos }}</td>
                                    <td>{{ number_format($terminal->capacidade_armazenagem, 0, ',', '.') }} t</td>
                                    <td>{{ $terminal->calado_maximo }} m</td>
                                    <td>{{ $terminal->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Nenhum terminal encontrado</td>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Gráfico por Tipo
@if($estatisticas['por_tipo']->count() > 0)
const tipoCtx = document.getElementById('tipoChart').getContext('2d');
const tipoChart = new Chart(tipoCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($estatisticas['por_tipo']->keys()) !!},
        datasets: [{
            data: {!! json_encode($estatisticas['por_tipo']->values()) !!},
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6c757d'
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
        labels: ['Ativos', 'Inativos', 'Manutenção'],
        datasets: [{
            label: 'Quantidade',
            data: [{{ $estatisticas['ativos'] }}, {{ $estatisticas['inativos'] }}, {{ $estatisticas['em_manutencao'] }}],
            backgroundColor: ['#28a745', '#6c757d', '#ffc107']
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