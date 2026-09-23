<x-app-layout>
    <style>
        /* Reset e configurações base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .trafego-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            flex-direction: column;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Header Premium */
        .trafego-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            border-bottom: 3px solid #60a5fa;
            position: relative;
            overflow: hidden;
            min-height: 80px;
        }

        .trafego-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 4s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .header-content {
            display: flex;
            align-items: center;
            z-index: 1;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            backdrop-filter: blur(10px);
            animation: float 3s ease-in-out infinite;
        }

        .header-icon i {
            font-size: 1.5rem;
            color: white;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }

        .header-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header-text p {
            font-size: 0.95rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            z-index: 1;
        }

        .btn-header {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            font-weight: 500;
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
        }

        .btn-header:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .btn-header i {
            margin-right: 8px;
            font-size: 0.9rem;
        }

        .btn-back {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.3);
        }

        .btn-back:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        /* Container principal */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #0f172a;
            position: relative;
        }

        /* Tabs de navegação */
        .nav-tabs {
            background: rgba(30, 41, 59, 0.8);
            padding: 15px 30px;
            display: flex;
            gap: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.7);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn.active {
            background: rgba(59, 130, 246, 0.2);
            color: white;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .tab-btn:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        /* Container do conteúdo */
        .content-container {
            flex: 1;
            position: relative;
            background: #0f172a;
        }

        .tab-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Iframe container */
        .iframe-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
            background: white;
        }

        .iframe-wrapper iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Mensagem de erro/alternativa */
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: white;
            text-align: center;
            padding: 40px;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .error-icon i {
            font-size: 2rem;
            color: #ef4444;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .error-message {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 30px;
            max-width: 500px;
            line-height: 1.6;
        }

        .alternative-links {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .alt-link {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alt-link:hover {
            background: rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
        }

        /* Loading overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 10;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(59, 130, 246, 0.3);
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .trafego-header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
                min-height: auto;
            }

            .header-content {
                width: 100%;
                justify-content: center;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .nav-tabs {
                padding: 10px 20px;
                flex-wrap: wrap;
                gap: 10px;
            }

            .tab-btn {
                padding: 8px 16px;
                font-size: 0.85rem;
            }
        }
    </style>

    <div class="trafego-container">
        <!-- Header -->
        <div class="trafego-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-ship"></i>
                </div>
                <div class="header-text">
                    <h1>Tráfego Marítimo Global</h1>
                    <p>Monitoramento em Tempo Real de Embarcações</p>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="{{ route('dashboard') }}" class="btn-header btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>

        <!-- Navegação por Tabs -->
        <div class="nav-tabs">
            <button class="tab-btn active" onclick="switchTab('marinetraffic')">
                <i class="fas fa-ship"></i>
                MarineTraffic
            </button>
            <button class="tab-btn" onclick="switchTab('vesselfinder')">
                <i class="fas fa-search"></i>
                VesselFinder
            </button>
            <button class="tab-btn" onclick="switchTab('shipfinder')">
                <i class="fas fa-globe"></i>
                ShipFinder
            </button>
            <button class="tab-btn" onclick="switchTab('alternatives')">
                <i class="fas fa-external-link-alt"></i>
                Alternativas
            </button>
        </div>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <div class="content-container">
                <!-- Tab MarineTraffic -->
                <div id="marinetraffic" class="tab-content active">
                    <div class="iframe-wrapper">
                        <div class="loading-overlay" id="loading-marinetraffic">
                            <div class="loading-spinner"></div>
                            <p>Carregando MarineTraffic...</p>
                        </div>
                        <iframe 
                            src="https://www.marinetraffic.com/he/ais/home/centerx:-12.0/centery:25.0/zoom:4"
                            onload="hideLoading('marinetraffic')"
                            onerror="showError('marinetraffic')">
                        </iframe>
                    </div>
                </div>

                <!-- Tab VesselFinder -->
                <div id="vesselfinder" class="tab-content">
                    <div class="iframe-wrapper">
                        <div class="loading-overlay" id="loading-vesselfinder">
                            <div class="loading-spinner"></div>
                            <p>Carregando VesselFinder...</p>
                        </div>
                        <iframe 
                            src="https://www.vesselfinder.com/"
                            onload="hideLoading('vesselfinder')"
                            onerror="showError('vesselfinder')">
                        </iframe>
                    </div>
                </div>

                <!-- Tab ShipFinder -->
                <div id="shipfinder" class="tab-content">
                    <div class="iframe-wrapper">
                        <div class="loading-overlay" id="loading-shipfinder">
                            <div class="loading-spinner"></div>
                            <p>Carregando ShipFinder...</p>
                        </div>
                        <iframe 
                            src="https://www.shipfinder.com/"
                            onload="hideLoading('shipfinder')"
                            onerror="showError('shipfinder')">
                        </iframe>
                    </div>
                </div>

                <!-- Tab Alternativas -->
                <div id="alternatives" class="tab-content">
                    <div class="error-container">
                        <div class="error-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h2 class="error-title">Opções de Tráfego Marítimo</h2>
                        <p class="error-message">
                            Caso os mapas integrados não funcionem devido a restrições de iframe, 
                            você pode acessar diretamente os sites especializados em tráfego marítimo:
                        </p>
                        <div class="alternative-links">
                            <a href="https://www.marinetraffic.com/he/ais/home/centerx:-12.0/centery:25.0/zoom:4" 
                               target="_blank" class="alt-link">
                                <i class="fas fa-ship"></i>
                                MarineTraffic
                            </a>
                            <a href="https://www.vesselfinder.com/" target="_blank" class="alt-link">
                                <i class="fas fa-search"></i>
                                VesselFinder
                            </a>
                            <a href="https://www.shipfinder.com/" target="_blank" class="alt-link">
                                <i class="fas fa-globe"></i>
                                ShipFinder
                            </a>
                            <a href="https://www.fleetmon.com/" target="_blank" class="alt-link">
                                <i class="fas fa-chart-line"></i>
                                FleetMon
                            </a>
                            <a href="https://www.myshiptracking.com/" target="_blank" class="alt-link">
                                <i class="fas fa-map-marked-alt"></i>
                                MyShipTracking
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para alternar entre tabs
        function switchTab(tabName) {
            // Remover classe active de todos os botões e conteúdos
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Adicionar classe active ao botão e conteúdo selecionados
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }

        // Função para esconder loading
        function hideLoading(tabName) {
            setTimeout(() => {
                const loading = document.getElementById(`loading-${tabName}`);
                if (loading) {
                    loading.style.opacity = '0';
                    setTimeout(() => {
                        loading.style.display = 'none';
                    }, 300);
                }
            }, 1000);
        }

        // Função para mostrar erro
        function showError(tabName) {
            const loading = document.getElementById(`loading-${tabName}`);
            if (loading) {
                loading.innerHTML = `
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 style="margin: 15px 0 10px 0;">Erro ao Carregar</h3>
                    <p style="opacity: 0.8; margin-bottom: 20px;">
                        O site não permite ser exibido em iframe.<br>
                        Clique na aba "Alternativas" para acessar diretamente.
                    </p>
                    <button onclick="switchTab('alternatives')" class="alt-link">
                        <i class="fas fa-external-link-alt"></i>
                        Ver Alternativas
                    </button>
                `;
            }
        }

        // Prevenir saída acidental
        window.addEventListener('beforeunload', (e) => {
            e.preventDefault();
            e.returnValue = '';
        });

        // Inicializar primeira tab
        document.addEventListener('DOMContentLoaded', () => {
            // Tentar carregar MarineTraffic primeiro
            setTimeout(() => {
                const iframe = document.querySelector('#marinetraffic iframe');
                if (iframe) {
                    iframe.onload = () => hideLoading('marinetraffic');
                    iframe.onerror = () => showError('marinetraffic');
                }
            }, 500);
        });
    </script>
</x-app-layout>