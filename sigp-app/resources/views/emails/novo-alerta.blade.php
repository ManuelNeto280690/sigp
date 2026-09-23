@extends('emails.layouts.base')

@section('title', app('config.helper')->nomeDoSistema() . ' - Novo Alerta')

@section('content')
<div class="content-section">
    <h2 class="section-title">
        <span class="icon">⚠️</span>
        Novo Alerta Criado
    </h2>
    
    <div class="alert-info">
        <div class="alert-level alert-level-{{ $alerta->nivel }}">
            <strong>Nível:</strong> {{ ucfirst($alerta->nivel) }}
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <strong>Título:</strong>
                <span>{{ $alerta->titulo }}</span>
            </div>
            
            <div class="info-item">
                <strong>Tipo:</strong>
                <span>{{ ucfirst($alerta->tipo) }}</span>
            </div>
            
            <div class="info-item">
                <strong>Status:</strong>
                <span class="status-badge status-{{ $alerta->status }}">{{ ucfirst($alerta->status) }}</span>
            </div>
            
            <div class="info-item">
                <strong>Data de Início:</strong>
                <span>{{ $alerta->data_inicio ? $alerta->data_inicio->format('d/m/Y H:i') : 'Não definida' }}</span>
            </div>
            
            @if($alerta->data_fim)
            <div class="info-item">
                <strong>Data de Fim:</strong>
                <span>{{ $alerta->data_fim->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>
    
    @if($alerta->descricao)
    <div class="description-section">
        <h3>Descrição</h3>
        <p>{{ $alerta->descricao }}</p>
    </div>
    @endif
    
    @if($alerta->areas_afetadas && count($alerta->areas_afetadas) > 0)
    <div class="areas-section">
        <h3>Áreas Afetadas</h3>
        <ul class="areas-list">
            @foreach($alerta->areas_afetadas as $area)
                <li>{{ $area }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    @if($alerta->acoes_tomadas)
    <div class="actions-section">
        <h3>Ações Tomadas</h3>
        <p>{{ $alerta->acoes_tomadas }}</p>
    </div>
    @endif
    
    @if(in_array($alerta->nivel, ['emergencia', 'critico']))
    <div class="urgent-notice">
        <div class="urgent-icon">🚨</div>
        <div class="urgent-text">
            <strong>ATENÇÃO:</strong> Este é um alerta de alta prioridade que requer ação imediata.
            @if($alerta->nivel === 'emergencia')
                Considere notificar as autoridades competentes.
            @endif
        </div>
    </div>
    @endif
    
    <div class="action-section">
        <a href="{{ route('alertas.show', $alerta->id) }}" class="btn-primary">
            Ver Detalhes do Alerta
        </a>
    </div>
</div>

<style>
    .alert-level {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        margin-bottom: 20px;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1px;
    }
    
    .alert-level-emergencia {
        background-color: #dc2626;
        color: white;
    }
    
    .alert-level-critico {
        background-color: #ea580c;
        color: white;
    }
    
    .alert-level-aviso {
        background-color: #d97706;
        color: white;
    }
    
    .alert-level-info {
        background-color: #0284c7;
        color: white;
    }
    
    .areas-list {
        list-style: none;
        padding: 0;
    }
    
    .areas-list li {
        background-color: #f1f5f9;
        padding: 8px 12px;
        margin: 4px 0;
        border-radius: 6px;
        border-left: 4px solid #0086e1;
    }
    
    .urgent-notice {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 2px solid #dc2626;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .urgent-icon {
        font-size: 24px;
        animation: pulse 2s infinite;
    }
    
    .urgent-text {
        color: #7f1d1d;
        font-weight: 500;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .status-ativo {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .status-resolvido {
        background-color: #e0e7ff;
        color: #3730a3;
    }
    
    .status-cancelado {
        background-color: #fef2f2;
        color: #991b1b;
    }
</style>
@endsection