<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'SIGP')</title>
    
    <style>
        /* Reset e Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2d3748;
            background-color: #f8fafc;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        
        /* Container Principal */
        .email-wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 20px 0;
            min-height: 100vh;
        }
        
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #0086e1 0%, #005bb5 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .email-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        .logo-container {
            margin-bottom: 20px;
        }
        
        .logo {
            max-height: 80px;
            max-width: 200px;
            height: auto;
            width: auto;
            filter: brightness(0) invert(1);
            margin-bottom: 15px;
        }
        
        .system-name {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }
        
        .system-subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
            margin-bottom: 10px;
        }
        
        .email-title {
            font-size: 24px;
            font-weight: 600;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Conteúdo */
        .email-content {
            padding: 40px 30px;
            background: #ffffff;
        }
        
        .content-section {
            margin-bottom: 30px;
        }
        
        .content-section:last-child {
            margin-bottom: 0;
        }
        
        /* Botões */
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #0086e1 0%, #005bb5 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 134, 225, 0.3);
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 134, 225, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        }
        
        /* Cards e Seções */
        .info-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
            flex: 0 0 40%;
        }
        
        .info-value {
            color: #2d3748;
            font-size: 14px;
            text-align: right;
            flex: 1;
        }
        
        /* Alertas e Avisos */
        .alert {
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 4px solid;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left-color: #3b82f6;
            color: #1e40af;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left-color: #f59e0b;
            color: #92400e;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-left-color: #10b981;
            color: #065f46;
        }
        
        /* Footer */
        .email-footer {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: #9ca3af;
            padding: 30px;
            text-align: center;
        }
        
        .footer-content {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .footer-text {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .footer-links {
            margin: 20px 0;
        }
        
        .footer-link {
            color: #60a5fa;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
        }
        
        .footer-link:hover {
            color: #93c5fd;
        }
        
        .copyright {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #374151;
        }
        
        /* Responsividade */
        @media (max-width: 600px) {
            .email-wrapper {
                padding: 10px;
            }
            
            .email-container {
                border-radius: 12px;
                margin: 0 10px;
            }
            
            .email-header {
                padding: 30px 20px;
            }
            
            .system-name {
                font-size: 28px;
            }
            
            .email-title {
                font-size: 20px;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .info-label {
                flex: none;
            }
            
            .info-value {
                text-align: left;
            }
            
            .btn {
                display: block;
                text-align: center;
                margin: 10px 0;
            }
        }
        
        /* Utilitários */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .mb-0 { margin-bottom: 0; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .text-sm { font-size: 14px; }
        .text-xs { font-size: 12px; }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="header-content">
                    <div class="logo-container">
                      
                        
                  
                            <!--img src="{{ asset('storage/' . app('config.helper')->get('logo_sistema')) }}" class="logo"-->
                       
                    </div>
                    
                    <h1 class="system-name">{{ app('config.helper')->nomeEmpresa() }}</h1>
                    <p class="system-subtitle">{{ app('config.helper')->nomeDoSistema() }}</p>
                    
                    <h2 class="email-title">@yield('email-title', 'Notificação do Sistema')</h2>
                </div>
            </div>
            
            <!-- Conteúdo -->
            <div class="email-content">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-content">
                    <p class="footer-text">
                        Este é um e-mail automático enviado pelo {{ app('config.helper')->nomeEmpresa() ?? 'SIGP SOYO' }}.
                        <br>Por favor, não responda a esta mensagem.
                    </p>
                    
                    <div class="footer-links">
                        @if(config('app.url'))
                            <a href="{{ config('app.url') }}" class="footer-link">Acessar Sistema</a>
                        @endif
                        @php
                            $emailContato = \App\Models\Configuracao::emailContato();
                        @endphp
                        @if($emailContato)
                            <a href="mailto:{{ $emailContato }}" class="footer-link">Suporte</a>
                        @endif
                    </div>
                    
                    <div class="copyright">
                        <p>© {{ date('Y') }} {{ app('config.helper')->nomeEmpresa() ?? 'SIGP SOYO' }}. Todos os direitos reservados.</p>
                        <p>Enviado em {{ now()->format('d/m/Y \à\s H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>