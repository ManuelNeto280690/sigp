@extends('layouts.app')

@section('title', 'Relatório de Embarcações')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-ship mr-2"></i>
                        Relatório de Embarcações
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
                            <form method="GET" action="{{ route('embarcacoes.relatorio') }}" class="form-inline">
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
                                        <option value="esperado" {{ request('status') == 'esperado' ? 'selected' : '' }}>Esperado</option>
                                        <option value="atracado" {{ request('status') == 'atracado' ? 'selected' : '' }}>Atracado</option>
                                        <option value="desatracado" {{ request('status') == 'desatracado' ? 'selected' : '' }}>Desatracado</option>
                                        <option value="em_transito" {{ request('status') == 'em_transito' ? 'selected' : '' }}>Em Trânsito</option>
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="tipo_embarcacao" class="mr-2">Tipo:</label>
                                    <select class="form-control" id="tipo_embarcacao" name="tipo_embarcacao">
                                        <option value="">Todos</option>
                                        <option value="container" {{ request('tipo_embarcacao') == 'container' ? 'selected' : '' }}>Container</option>
                                        <option value="graneis_solidos" {{ request('tipo_embarcacao') == 'graneis_solidos' ? 'selected' : '' }}>Granéis Sólidos</option>
                                        <option value="graneis_liquidos" {{ request('tipo_embarcacao') == 'graneis_liquidos' ? 'selected' : '' }}>Granéis Líquidos</option>
                                        <option value="carga_geral" {{ request('tipo_embarcacao') == 'carga_geral' ? 'selected' : '' }}>Carga Geral</option>
                                        <option value="passageiros" {{ request('tipo_embarcacao') == 'passageiros' ? 'selected' : '' }}>Passageiros</option>
                                        <option value="tanque" {{ request('tipo_embarcacao') == 'tanque' ? 'selected' : '' }}>Tanque</option>
                                        <option value="ro_ro" {{ request('tipo_embarcacao') == 'ro_ro' ? 'selected' : '' }}>Ro-Ro</option>
                                        <option value="frigorifico" {{ request('tipo_embarcacao') == 'frigorifico' ? 'selected' : '' }}>Frigorífico</option>
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="bandeira" class="mr-2">Bandeira:</label>
                                    <input type="text" class="form-control" id="bandeira" name="bandeira" 
                                           value="{{ request('bandeira') }}" placeholder="Digite a bandeira">
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
                                    <p>Total de Embarcações</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-ship"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $estatisticas['ativas'] }}</h3>
                                    <p>Ativas</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($estatisticas['capacidade_total'], 0, ',', '.') }}</h3>
                                    <p>Capacidade Total (GT)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-weight-hanging"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ number_format($estatisticas['comprimento_medio'], 1) }}m</h3>
                                    <p>Comprimento Médio</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-ruler-horizontal"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Embarcações por Tipo</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="tipoChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Embarcações por Status</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Embarcações -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>IMO</th>
                                    <th>MMSI</th>
                                    <th>Bandeira</th>
                                    <th>Tipo</th>
                                    <th>Comprimento</th>
                                    <th>Arqueação Bruta</th>
                                    <th>Status</th>
                                    <th>Data Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($embarcacoes as $embarcacao)
                                <tr>
                                    <td>{{ $embarcacao->nome }}</td>
                                    <td>{{ $embarcacao->imo ?? 'N/A' }}</td>
                                    <td>{{ $embarcacao->mmsi ?? 'N/A' }}</td>
                                    <td>{{ $embarcacao->bandeira }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst(str_replace('_', ' ', $embarcacao->tipo_embarcacao)) }}
                                        </span>
                                    </td>
                                    <td>{{ $embarcacao->comprimento ? $embarcacao->comprimento . 'm' : 'N/A' }}</td>
                                    <td>{{ $embarcacao->arqueacao_bruta ? number_format($embarcacao->arqueacao_bruta, 0, ',', '.') . ' GT' : 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $embarcacao->status == 'atracado' ? 'success' : 
                                            ($embarcacao->status == 'esperado' ? 'warning' : 
                                            ($embarcacao->status == 'em_transito' ? 'info' : 'secondary')) 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $embarcacao->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $embarcacao->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('embarcacoes.show', $embarcacao) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Nenhuma embarcação encontrada no período selecionado.</td>
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
// Gráfico de Tipos
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
                '#6f42c1',
                '#fd7e14',
                '#20c997',
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
        labels: {!! json_encode($estatisticas['por_status']->keys()) !!},
        datasets: [{
            label: 'Embarcações por Status',
            data: {!! json_encode($estatisticas['por_status']->values()) !!},
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#17a2b8',
                '#6c757d'
            ]
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
    window.location.href = '{{ route("embarcacoes.relatorio") }}?' + new URLSearchParams({
        ...Object.fromEntries(new URLSearchParams(window.location.search)),
        formato: 'excel'
    });
}

function exportToPDF() {
    window.location.href = '{{ route("embarcacoes.relatorio") }}?' + new URLSearchParams({
        ...Object.fromEntries(new URLSearchParams(window.location.search)),
        formato: 'pdf'
    });
}
</script>
@endpush
@endsection