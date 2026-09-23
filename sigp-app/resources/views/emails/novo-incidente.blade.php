@extends('emails.layouts.base')

@section('title', 'Novo Incidente Registrado')

@section('content')
<div class="incident-header" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; text-align: center;">
    <div style="font-size: 48px; margin-bottom: 15px;">🚨</div>
    <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Novo Incidente Registrado</h1>
    <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">
        Incidente #{{ $incidente->numero_incidente }}
    </p>
</div>

<div class="incident-details">
    <!-- Informações Principais -->
    <div class="info-section" style="background: #f8fafc; border-left: 4px solid #dc2626; padding: 25px; margin-bottom: 25px; border-radius: 8px;">
        <h2 style="color: #1f2937; margin: 0 0 20px 0; font-size: 20px; font-weight: 600;">
            📋 Informações do Incidente
        </h2>
        
        <div class="info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <strong style="color: #374151;">Título:</strong><br>
                <span style="color: #1f2937;">{{ $incidente->titulo }}</span>
            </div>
            <div>
                <strong style="color: #374151;">Tipo:</strong><br>
                <span class="badge" style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                    {{ ucfirst($incidente->tipo) }}
                </span>
            </div>
        </div>

        <div class="info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <strong style="color: #374151;">Gravidade:</strong><br>
                @php
                    $gravidadeColors = [
                        'baixa' => ['bg' => '#dcfce7', 'text' => '#166534'],
                        'media' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                        'alta' => ['bg' => '#fed7aa', 'text' => '#c2410c'],
                        'critica' => ['bg' => '#fecaca', 'text' => '#dc2626']
                    ];
                    $colors = $gravidadeColors[$incidente->gravidade] ?? $gravidadeColors['media'];
                @endphp
                <span class="badge" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                    {{ ucfirst($incidente->gravidade) }}
                </span>
            </div>
            <div>
                <strong style="color: #374151;">Status:</strong><br>
                <span class="badge" style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                    {{ ucfirst($incidente->status) }}
                </span>
            </div>
        </div>

        <div class="info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <strong style="color: #374151;">Data da Ocorrência:</strong><br>
                <span style="color: #1f2937;">{{ $incidente->data_ocorrencia->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <strong style="color: #374151;">Local:</strong><br>
                <span style="color: #1f2937;">{{ $incidente->local_ocorrencia }}</span>
            </div>
        </div>
    </div>

    <!-- Descrição -->
    <div class="description-section" style="background: white; border: 1px solid #e5e7eb; padding: 25px; margin-bottom: 25px; border-radius: 8px;">
        <h3 style="color: #1f2937; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">
            📝 Descrição
        </h3>
        <p style="color: #4b5563; line-height: 1.6; margin: 0;">
            {{ $incidente->descricao }}
        </p>
    </div>

    <!-- Informações Adicionais -->
    @if($incidente->embarcacao || $incidente->terminal)
    <div class="additional-info" style="background: #f0f9ff; border: 1px solid #0ea5e9; padding: 25px; margin-bottom: 25px; border-radius: 8px;">
        <h3 style="color: #0c4a6e; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">
            🚢 Informações Relacionadas
        </h3>
        
        @if($incidente->embarcacao)
        <div style="margin-bottom: 15px;">
            <strong style="color: #0c4a6e;">Embarcação:</strong><br>
            <span style="color: #1e40af;">{{ $incidente->embarcacao->nome ?? 'N/A' }}</span>
        </div>
        @endif

        @if($incidente->terminal)
        <div>
            <strong style="color: #0c4a6e;">Terminal:</strong><br>
            <span style="color: #1e40af;">{{ $incidente->terminal->nome ?? 'N/A' }}</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Pessoas Envolvidas -->
    @if($incidente->pessoas_envolvidas && count($incidente->pessoas_envolvidas) > 0)
    <div class="people-section" style="background: #fefce8; border: 1px solid #eab308; padding: 25px; margin-bottom: 25px; border-radius: 8px;">
        <h3 style="color: #713f12; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">
            👥 Pessoas Envolvidas
        </h3>
        
        @foreach($incidente->pessoas_envolvidas as $pessoa)
        <div style="background: white; padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 3px solid #eab308;">
            <strong style="color: #713f12;">{{ $pessoa['nome'] ?? 'Nome não informado' }}</strong>
            @if(isset($pessoa['cargo']) && $pessoa['cargo'])
                <br><span style="color: #a16207; font-size: 14px;">Cargo: {{ $pessoa['cargo'] }}</span>
            @endif
            @if(isset($pessoa['empresa']) && $pessoa['empresa'])
                <br><span style="color: #a16207; font-size: 14px;">Empresa: {{ $pessoa['empresa'] }}</span>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- Ações -->
    @if($incidente->acoes_imediatas || $incidente->acoes_corretivas)
    <div class="actions-section" style="background: #f0fdf4; border: 1px solid #22c55e; padding: 25px; margin-bottom: 25px; border-radius: 8px;">
        <h3 style="color: #14532d; margin: 0 0 15px 0; font-size: 18px; font-weight: 600;">
            ⚡ Ações Tomadas
        </h3>
        
        @if($incidente->acoes_imediatas)
        <div style="margin-bottom: 15px;">
            <strong style="color: #14532d;">Ações Imediatas:</strong><br>
            <span style="color: #166534;">{{ $incidente->acoes_imediatas }}</span>
        </div>
        @endif

        @if($incidente->acoes_corretivas)
        <div>
            <strong style="color: #14532d;">Ações Corretivas:</strong><br>
            <span style="color: #166534;">{{ $incidente->acoes_corretivas }}</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Reportado por -->
    <div class="reporter-section" style="background: #f8fafc; border: 1px solid #64748b; padding: 20px; margin-bottom: 25px; border-radius: 8px;">
        <h3 style="color: #334155; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">
            👤 Reportado por
        </h3>
        <span style="color: #475569;">{{ $incidente->user->name ?? 'Usuário não identificado' }}</span>
        <br>
        <small style="color: #64748b;">{{ $incidente->created_at->format('d/m/Y H:i:s') }}</small>
    </div>

    <!-- Alerta de Notificação de Autoridades -->
    @if($incidente->requer_notificacao_autoridades)
    <div class="alert-section" style="background: #fef2f2; border: 2px solid #ef4444; padding: 20px; margin-bottom: 25px; border-radius: 8px; text-align: center;">
        <div style="font-size: 32px; margin-bottom: 10px;">⚠️</div>
        <h3 style="color: #dc2626; margin: 0 0 10px 0; font-size: 18px; font-weight: 700;">
            REQUER NOTIFICAÇÃO ÀS AUTORIDADES
        </h3>
        <p style="color: #b91c1c; margin: 0; font-weight: 600;">
            Este incidente requer notificação imediata às autoridades competentes.
        </p>
    </div>
    @endif
</div>

<!-- Botão de Ação -->
<div style="text-align: center; margin: 40px 0;">
    <a href="{{ route('incidentes.show', $incidente->id) }}" 
       style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); 
              color: white; 
              padding: 15px 30px; 
              text-decoration: none; 
              border-radius: 8px; 
              font-weight: 600; 
              display: inline-block;
              box-shadow: 0 4px 6px rgba(220, 38, 38, 0.2);">
        Ver Detalhes do Incidente
    </a>
</div>

<!-- Instruções -->
<div style="background: #f1f5f9; border-left: 4px solid #3b82f6; padding: 20px; margin-top: 30px; border-radius: 8px;">
    <h4 style="color: #1e40af; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">
        📌 Próximos Passos
    </h4>
    <ul style="color: #475569; margin: 0; padding-left: 20px;">
        <li>Acesse o sistema para revisar todos os detalhes do incidente</li>
        <li>Avalie a necessidade de investigação adicional</li>
        @if($incidente->requer_notificacao_autoridades)
        <li style="color: #dc2626; font-weight: 600;">⚠️ Notifique imediatamente as autoridades competentes</li>
        @endif
        <li>Documente quaisquer ações adicionais tomadas</li>
    </ul>
</div>
@endsection