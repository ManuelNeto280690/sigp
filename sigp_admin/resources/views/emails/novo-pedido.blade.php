@extends('emails.layouts.base')

@section('title', 'Novo Pedido - ' . app('config.helper')->nomeEmpresa())

@section('email-title', 'Novo Pedido Criado')

@section('content')
    <div class="content-section">
        <div class="alert alert-info">
            <strong>📋 Novo Pedido Recebido</strong><br>
            Um novo pedido foi criado no sistema e requer sua atenção imediata.
        </div>
    </div>

    <!-- Informações do Navio -->
    <div class="content-section">
        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            🚢 Informações da Embarcação
        </h3>
        
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Nome do Navio:</span>
                <span class="info-value font-semibold">{{ $pedido->nome_navio }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Número IMO:</span>
                <span class="info-value">{{ $pedido->numero_imo }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Indicativo de Chamada:</span>
                <span class="info-value">{{ $pedido->indicativo_chamada }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Bandeira:</span>
                <span class="info-value">{{ $pedido->bandeira_navio }}</span>
            </div>
        </div>
    </div>

    <!-- Cronograma -->
    <div class="content-section">
        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            📅 Cronograma de Atracação
        </h3>
        
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Data de Chegada:</span>
                <span class="info-value font-semibold" style="color: #059669;">
                    {{ \Carbon\Carbon::parse($pedido->data_chegada)->format('d/m/Y') }} às {{ $pedido->hora_chegada }}
                </span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Data de Partida:</span>
                <span class="info-value font-semibold" style="color: #dc2626;">
                    {{ \Carbon\Carbon::parse($pedido->data_partida)->format('d/m/Y') }} às {{ $pedido->hora_partida }}
                </span>
            </div>
        </div>
    </div>

    <!-- Informações do Agente -->
    <div class="content-section">
        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            👤 Agente Marítimo
        </h3>
        
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Nome do Agente:</span>
                <span class="info-value font-semibold">{{ $pedido->nome_agente }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Contato:</span>
                <span class="info-value">{{ $pedido->contato_agente }}</span>
            </div>
        </div>
    </div>

    <!-- Tripulação e Passageiros -->
    <div class="content-section">
        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            👥 Pessoas a Bordo
        </h3>
        
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Tripulantes:</span>
                <span class="info-value font-semibold">{{ $pedido->numero_tripulantes }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Passageiros:</span>
                <span class="info-value font-semibold">{{ $pedido->numero_passageiros }}</span>
            </div>
        </div>
    </div>

    @if($pedido->observacoes_operacao)
    <!-- Observações -->
    <div class="content-section">
        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            📝 Observações
        </h3>
        
        <div class="info-card">
            <p style="color: #4a5568; line-height: 1.6;">{{ $pedido->observacoes_operacao }}</p>
        </div>
    </div>
    @endif

    @php
        $totalDocumentos = 0;
        $categoriasDocumentos = [
            'declaracoes_cargas' => 'Declarações de Cargas',
            'declaracao_provisoes_bordo' => 'Declaração de Provisões de Bordo',
            'declaracao_pertences_tripulacao' => 'Declaração de Pertences da Tripulação',
            'documentos_tripulantes' => 'Documentos de Tripulantes',
            'documentos_passageiros' => 'Documentos de Passageiros',
            'declaracao_mercadorias_perigosas' => 'Declaração de Mercadorias Perigosas'
        ];
        
        // Contar documentos
        foreach ($categoriasDocumentos as $campo => $nome) {
            if (is_array($pedido->$campo) && !empty($pedido->$campo)) {
                $totalDocumentos += count($pedido->$campo);
            }
        }
    @endphp

    @if($totalDocumentos > 0)
    <!-- Documentos Anexados -->
    <div class="content-section">
        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            📎 Documentos Anexados ({{ $totalDocumentos }})
        </h3>
        
        <div class="alert alert-info">
            <strong>💡 Como acessar os documentos:</strong><br>
            • Clique nos links abaixo para baixar diretamente<br>
            • Os arquivos também estão anexados a este e-mail<br>
            • Ou acesse o sistema para visualizar online
        </div>
        
        @foreach($categoriasDocumentos as $campo => $nomeCategoria)
            @if(is_array($pedido->$campo) && !empty($pedido->$campo))
                <div style="margin-bottom: 25px;">
                    <h4 style="color: #4a5568; font-size: 16px; font-weight: 600; margin-bottom: 15px; padding: 10px; background: #f1f5f9; border-radius: 8px;">
                        {{ $nomeCategoria }}
                    </h4>
                    
                    @foreach($pedido->$campo as $index => $documento)
                        <div style="margin-bottom: 10px; padding: 12px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                            <span style="color: #4a5568; font-size: 14px;">
                                📄 {{ basename(is_string($documento) ? $documento : (is_array($documento) ? $documento['path'] ?? 'Documento' : 'Documento')) }}
                            </span>
                            <a href="{{ route('pedidos.download-arquivo', ['pedido' => $pedido->id, 'tipo' => $campo, 'index' => $index]) }}" 
                               style="background: linear-gradient(135deg, #0086e1 0%, #005bb5 100%); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;"
                               target="_blank">
                                ⬇️ Baixar
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
        
        @if($totalDocumentos > 0)
            <div class="alert alert-warning">
                <strong>⚠️ Importante:</strong> Este e-mail contém {{ $totalDocumentos }} anexo(s). 
                Verifique todos os documentos antes de processar o pedido.
            </div>
        @endif
    </div>
    @endif

    <!-- Ações -->
    <div class="content-section text-center">
        <a href="{{ route('pedidos.show', $pedido) }}" class="btn" style="margin-right: 15px;">
            👁️ Ver Pedido Completo
        </a>
        
        @if(config('app.url'))
            <a href="{{ config('app.url') }}" class="btn btn-secondary">
                🏠 Acessar Sistema
            </a>
        @endif
    </div>
@endsection