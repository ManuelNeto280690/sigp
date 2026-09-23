@extends('emails.layouts.base')

@section('title', 'Código de Verificação 2FA - ' . app('config.helper')->nomeDoSistema())

@section('email-title', 'Código de Verificação 2FA')

@push('styles')
<style>
    .code-section {
        text-align: center;
        margin: 35px 0;
    }
    
    .code-label {
        font-size: 14px;
        color: #718096;
        margin-bottom: 15px;
        text-transform: uppercase;
        font-weight: 600;
    }
    
    .code-box {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        border: 3px solid #e2e8f0;
        border-radius: 16px;
        padding: 30px 20px;
        margin: 20px auto;
        max-width: 300px;
        position: relative;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .code-box::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(135deg, #0086e1, #005bb5);
        border-radius: 18px;
        z-index: -1;
    }
    
    .code {
        font-size: 36px;
        font-weight: 800;
        color: #2d3748;
        letter-spacing: 8px;
        font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
        margin-bottom: 10px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .code-instruction {
        font-size: 13px;
        color: #718096;
        font-weight: 500;
    }
    
    @media (max-width: 600px) {
        .code {
            font-size: 28px;
            letter-spacing: 4px;
        }
        
        .code-box {
            padding: 25px 15px;
            max-width: 280px;
        }
    }
    
    @media (max-width: 480px) {
        .code {
            font-size: 24px;
            letter-spacing: 3px;
        }
    }
</style>
@endpush

@section('content')
    <div class="content-section">
        <div class="text-center mb-20">
            @if($userName)
                <h3 style="color: #2d3748; font-size: 20px; margin-bottom: 10px;">
                    Olá, <strong>{{ $userName }}</strong>! 👋
                </h3>
            @else
                <h3 style="color: #2d3748; font-size: 20px; margin-bottom: 10px;">
                    Olá! 👋
                </h3>
            @endif
        </div>
        
        <div class="alert alert-info">
            <strong>🔐 Código de Verificação Solicitado</strong><br>
            Você solicitou um código de verificação para autenticação de dois fatores. 
            Para sua segurança, use o código abaixo para completar seu login no sistema.
        </div>
    </div>

    <div class="content-section">
        <div class="code-section">
            <div class="code-label">Seu código de verificação</div>
            <div class="code-box">
                <div class="code">{{ $code }}</div>
                <div class="code-instruction">Digite este código na tela de verificação</div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="alert alert-warning">
            <strong>⚠️ Informações Importantes</strong><br><br>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                    <span style="position: absolute; left: 0; color: #f59e0b; font-weight: bold;">•</span>
                    Este código expira em <strong>{{ $expiresIn }}</strong>
                </li>
                <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                    <span style="position: absolute; left: 0; color: #f59e0b; font-weight: bold;">•</span>
                    Pode ser usado apenas uma vez
                </li>
                <li style="margin-bottom: 8px; padding-left: 20px; position: relative;">
                    <span style="position: absolute; left: 0; color: #f59e0b; font-weight: bold;">•</span>
                    Se você não solicitou este código, ignore este email
                </li>
            </ul>
        </div>
    </div>

    <div class="content-section">
        <div class="info-card text-center">
            <strong style="color: #2d3748;">Precisa de ajuda? 🆘</strong><br><br>
            Se você está tendo problemas para fazer login ou não reconhece esta atividade, 
            entre em contato com nosso suporte técnico imediatamente.
            
            @php
                $emailContato = \App\Models\Configuracao::emailContato();
            @endphp
            @if($emailContato)
                <br><br>
                <a href="mailto:{{ $emailContato }}" class="btn btn-secondary">
                    📧 Contatar Suporte
                </a>
            @endif
        </div>
    </div>
@endsection