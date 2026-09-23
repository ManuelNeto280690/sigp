@extends('layouts.app')

@section('title', 'Relatório de Alertas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Relatório de Alertas
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <button type="button" class="btn btn-success btn-sm ml-2" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-bell"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total</span>
                                    <span class="info-box-number">{{ $estatisticas['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Críticos</span>
                                    <span class="info-box-number">{{ $estatisticas['criticos'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Ativos</span>
                                    <span class="info-box-number">{{ $estatisticas['ativos'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Resolvidos</span>
                                    <span class="info-box-number">{{ $estatisticas['resolvidos'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Alertas -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Nível</th>
                                    <th>Status</th>
                                    <th>Criado por</th>
                                    <th>Data Criação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alertas as $alerta)
                                <tr>
                                    <td>{{ $alerta->id }}</td>
                                    <td>{{ $alerta->titulo }}</td>
                                    <td>{{ $alerta->tipo }}</td>
                                    <td>
                                        <span class="badge badge-{{ $alerta->nivel === 'crítico' ? 'danger' : ($alerta->nivel === 'alto' ? 'warning' : 'info') }}">
                                            {{ ucfirst($alerta->nivel) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $alerta->status === 'ativo' ? 'warning' : 'success' }}">
                                            {{ ucfirst($alerta->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $alerta->user->name ?? 'Sistema' }}</td>
                                    <td>{{ $alerta->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nenhum alerta encontrado</td>
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

<script>
function exportToExcel() {
    // Implementar exportação para Excel
    alert('Funcionalidade de exportação em desenvolvimento');
}
</script>
@endsection