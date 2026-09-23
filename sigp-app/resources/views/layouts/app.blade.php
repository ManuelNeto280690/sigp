<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIGP') }} - Sistema Integrado de Gestão Portuário</title>
    
    <!-- Favicon dinâmico -->
    <link rel="icon" type="image/x-icon" href="{{ app('config.helper')->favicon() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ app('config.helper')->favicon() }}">
    
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <link href="{{asset('assets/style.css')}}" rel="stylesheet" />
    
    <!-- Scripts -->
  <script src"{{asset('assets/app.js')}}"></script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Fix para compatibilidade Alpine.js + onclick -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Aguarda o Alpine.js carregar completamente
            document.addEventListener('alpine:init', function() {
                // Força re-processamento de eventos após Alpine.js inicializar
                setTimeout(function() {
                    // Re-anexa todos os event listeners onclick
                    document.querySelectorAll('[onclick]').forEach(function(element) {
                        const onclickAttr = element.getAttribute('onclick');
                        if (onclickAttr) {
                            // Remove o onclick original
                            element.removeAttribute('onclick');
                            // Re-adiciona como event listener
                            element.addEventListener('click', function(e) {
                                // Executa o código onclick original
                                try {
                                    eval(onclickAttr);
                                } catch (error) {
                                    console.error('Erro ao executar onclick:', error);
                                }
                            });
                        }
                    });
                }, 100);
            });
        });
    </script>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 h-full overflow-hidden">
    <!-- Overlay para melhorar legibilidade -->
    <div class="dashboard-overlay"></div>

    <div class="flex h-screen content-layer" x-data="{ 
        sidebarOpen: false, 
        notificationsOpen: false, 
        profileOpen: false,
        registoExpanded: false,
        concessionariaExpanded: false,
        faturacaoExpanded: false
    }">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 lg:hidden sidebar-overlay"
             @click="sidebarOpen = false">
        </div>

        <!-- Sidebar Desktop -->
        <div class="fixed inset-y-0 left-0 z-50 w-60 gradient-bg text-white shadow-2xl transform lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out flex flex-col h-full"
             :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <!-- Logo Premium -->
            <div class="flex items-center justify-between p-3 border-b border-white/20 flex-shrink-0">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-lg overflow-hidden">
                        @if(app('config.helper')->get('logo_sistema'))
                            <img src="{{ asset('storage/' . app('config.helper')->get('logo_sistema')) }}" alt="Logo" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <i class="fas fa-anchor text-primary text-sm hidden"></i>
                        @else
                            <i class="fas fa-anchor text-primary text-sm"></i>
                        @endif
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="font-bold text-base">SIGP</h1>
                        <p class="text-white/70 text-xs">{{ app('config.helper')->nomeEmpresa() }}</p>
                    </div>
                </div>
                
                <!-- Mobile Close Button -->
                <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded-lg hover:bg-white/10">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <nav class="flex-1 mt-3 px-2 overflow-y-auto min-h-0 scrollbar-thin scrollbar-thumb-white/30 scrollbar-track-transparent">
                <div class="space-y-1 pb-4">

                @role('admin|supervisor_portuario|inspector_ambiental|inspector_cais|inspector_isps|tecnico_comercial')
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg bg-white/20 text-white shadow-lg">
                        <i class="fas fa-tachometer-alt mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Dashboard</span>
                    </a>
                    @endrole 
                     @role('admin|supervisor_portuario|inspector_ambiental|inspector_cais|inspector_isps')
                    <!-- Embarcações -->
                    <a href="/trafego" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-ship mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Trafegos</span>
                    </a>
                    @endrole
                    <!-- Equipa -->
                    <!--a href="#" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-users mr-3 text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="font-medium text-sm">Equipa</span>
                    </a-->
                     @role('admin')
                    <!-- Usuários -->
                    <a href="{{ route('users.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-user-friends mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Usuários</span>
                    </a>
                    
                    <!-- Funções -->
                    <a href="{{ route('roles.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-user-tag mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Funções</span>
                    </a>
                    
                    <!-- Permissões -->
                    <a href="{{ route('permissions.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-key mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Permissões</span>
                    </a>
                     @endrole


                     @role('admin|supervisor_portuario|inspector_ambiental|inspector_cais|inspector_isps')
                    <!-- Embarcações -->
                    <a href="{{ route('embarcacoes.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-ship mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Embarcações</span>
                    </a>
                    @endrole
                    
                    @role('admin|supervisor_portuario|inspector_cais')
                    <!-- Entrada/Saída -->
                    <a href="{{ route('entrada-saida-embarcacao.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-exchange-alt mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Entrada/Saída</span>
                    </a> 

                     @endrole
                    

                     @role('admin')
                    <!-- Concessionária (com submenu) -->
                    <div>
                        <button @click="concessionariaExpanded = !concessionariaExpanded" class="w-full flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                            <i class="fas fa-handshake mr-3 text-sm"></i>
                            <span class="font-medium text-sm flex-1 text-left">Concessionária</span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="concessionariaExpanded ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <!-- Submenu -->
                        <div class="submenu" :class="concessionariaExpanded ? 'expanded' : ''">
                            <a href="{{ route('concessionarias.embarcacoes.index') }}" class="submenu-item flex items-center px-6 py-3 rounded-lg text-white/70 hover:bg-white/10 transition-all duration-200 group">
                                <i class="fas fa-ship mr-3 text-xs"></i>
                                <span class="font-medium text-sm">Embarcações</span>
                            </a>
                            <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.index') }}" class="submenu-item flex items-center px-6 py-3 rounded-lg text-white/70 hover:bg-white/10 transition-all duration-200 group">
                                <i class="fas fa-exchange-alt mr-3 text-xs"></i>
                                <span class="font-medium text-sm">Entradas/Saídas</span>
                            </a>
                        </div>
                    </div>
                    @endrole
                    
                     @role('admin')
                    <!-- Consórcio -->
                    <a href="{{ route('concessionarias.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-handshake mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Cadastro de Concessionárias</span>
                    </a>
                    
                    <!-- Terminais -->
                    <a href="{{ route('terminais.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-building mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Terminais</span>
                    </a>
                    <!-- Berços -->
                    <a href="{{ route('bercos.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-anchor mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Berços</span>
                    </a>
                    <a href="{{ route('guindastes.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-industry mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Guindastes</span>
                    </a>
                    <a href="{{ route('contratos.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-file-contract mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Contratos</span>
                    </a>
                    <!-- Faturação e Impostos AGT -->
                    <div>
                        <button @click="faturacaoExpanded = !faturacaoExpanded" class="w-full flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                            <i class="fas fa-file-invoice-dollar mr-3 text-sm"></i>
                            <span class="font-medium text-sm flex-1 text-left">Faturação</span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="faturacaoExpanded ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <!-- Submenu Faturação -->
                        <div class="submenu" :class="faturacaoExpanded ? 'expanded' : ''">
                            <a href="{{ route('facturas.index') }}" class="submenu-item flex items-center px-6 py-3 rounded-lg text-white/70 hover:bg-white/10 transition-all duration-200 group">
                                <i class="fas fa-file-invoice mr-3 text-xs"></i>
                                <span class="font-medium text-sm">Documentos Comerciais</span>
                            </a>
                            <a href="{{ route('impostos.index') }}" class="submenu-item flex items-center px-6 py-3 rounded-lg text-white/70 hover:bg-white/10 transition-all duration-200 group {{ request()->routeIs('impostos.*') ? 'bg-white/10 text-white font-semibold' : '' }}">
                                <i class="fas fa-cogs mr-3 text-xs {{ request()->routeIs('impostos.*') ? 'text-blue-400' : 'text-white/50 group-hover:text-blue-400' }} transition-colors"></i>
                                <span class="font-medium text-sm">Configurações</span>
                            </a>
                            <a href="{{ route('saft.index') }}" class="submenu-item flex items-center px-6 py-3 rounded-lg text-white/70 hover:bg-white/10 transition-all duration-200 group">
                                <i class="fas fa-file-code mr-3 text-xs"></i>
                                <span class="font-medium text-sm">Exportar SAF-T (AO)</span>
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('movimentos-carga.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-boxes mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Movimentos de Carga</span>
                    </a>
                     @endrole

                    @role('admin|supervisor_portuario|inspector_isps')
                    <!-- Alertas -->
                    <a href="{{ route('alertas.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-exclamation-triangle mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Alertas</span>
                      
                    </a>

                    <!-- Incidentes -->
                    <a href="{{ route('incidentes.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-exclamation-circle mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Incidentes</span>
                    </a>
                    
                     @endrole

                     @role('admin|supervisor_portuario|agente_navio')
                    <!-- Pedidos -->
                    <a href="{{ route('pedidos.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-file-invoice mr-3 text-sm"></i>
                      <span class="font-medium text-sm">Pedidos</span>
                    </a>
                    @endrole
                  
                    
                     @role('admin|supervisor_portuario|inspector_ambiental')
                    <!-- Infrações Ambientais -->
                    <a href="{{ route('infracoes-ambientais.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-leaf mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Infrações Ambientais</span>
                    </a>
                    @endrole
                    
                    @role('admin|supervisor_portuario|inspector_cais')
                    <!-- Inspeção do Navio -->
                    <a href="{{ route('inspecoes.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-search mr-3 text-sm"></i>
                        <span class="font-medium text-sm">Inspeção do Navio</span>
                    </a>
                    @endrole
                    <!-- Registo de Movimentos (com submenu) -->
                    <!--div>
                        <button @click="registoExpanded = !registoExpanded" class="w-full flex items-center px-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                            <i class="fas fa-clipboard-list mr-3 text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="font-medium text-sm flex-1 text-left">Registo de Movimentos</span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="registoExpanded ? 'rotate-180' : ''"></i>
                        </button>
                        
                        <!- Submenu ->
                        <div class="submenu" :class="registoExpanded ? 'expanded' : ''">
                            <a href="#" class="submenu-item flex items-center px-3 py-2 rounded-lg text-white/70 hover:bg-white/10 transition-all duration-200 group">
                                <i class="fas fa-sign-in-alt mr-3 text-xs group-hover:scale-110 transition-transform"></i>
                                <span class="font-medium text-sm">Entrada</span>
                            </a>
                            <a href="#" class="submenu-item flex items-center px-3 py-2 rounded-lg text-white/70 hover:bg-white/10 transition-all duration-200 group">
                                <i class="fas fa-sign-out-alt mr-3 text-xs group-hover:scale-110 transition-transform"></i>
                                <span class="font-medium text-sm">Saídas</span>
                            </a>
                        </div>
                    </div-->
                </div>

                 @role('concessionaria')  
                      <!-- cocessionaria -->
                         <a href="{{ route('concessionarias.embarcacoes.index') }}" class="flex items-center px-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 grou">
                                <i class="fas fa-ship mr-3 text-xs group-hover:scale-110 transition-transform"></i>
                                <span class="font-medium text-sm">Embarcações</span>
                            </a>
                            <a href="{{ route('concessionarias.entrada-saida-embarcacao-concessionaria.index') }}" class="flex items-center px-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 grou">
                                <i class="fas fa-exchange-alt mr-3 text-xs group-hover:scale-110 transition-transform"></i>
                                <span class="font-medium text-sm">Entradas/Saídas</span>
                            </a>
                 @endrole
                
                <!-- Seção inferior -->
                <div class="mt-auto pt-3 border-t border-white/20 pb-4">

                 @role('admin')  
                    <!-- Definições -->
                    <a href="{{ route('configuracoes.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-cog mr-3 text-sm  transition-transform"></i>
                        <span class="font-medium text-sm">Definições</span>
                    </a>
                   @endrole 
                    
                  @role('admin')  
                    <!-- Auditoria -->
                    <a href="{{ route('audit-logs.dashboard') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-shield-alt mr-3 text-sm  transition-transform"></i>
                        <span class="font-medium text-sm">Auditoria</span>
                    </a>
                    
                    <!-- Logs da Aplicação -->
                    <a href="{{ route('audit-logs.index') }}" class="flex items-center px-3 py-3 lg:py-3 rounded-lg text-white/80 hover:bg-white/10 transition-all duration-200 group">
                        <i class="fas fa-list-ul mr-3 text-sm  transition-transform"></i>
                        <span class="font-medium text-sm">Logs da Aplicação</span>
                    </a>
                @endrole

               

                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-0 h-full overflow-hidden">
            <!-- TopBar Premium Responsiva -->
            <header class="bg-white/95 backdrop-blur-md shadow-sm border-b border-gray-200 relative z-30">
                <div class="flex items-center justify-between px-4 py-2">
                    <!-- Mobile Menu + Title -->
                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                        <!-- Mobile Menu Button -->
                        <button @click="sidebarOpen = true" class="lg:hidden p-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-bars text-gray-600 text-sm"></i>
                        </button>
                        
                        <div class="min-w-0 flex-1">
                            <h1 class="text-lg font-bold text-dark truncate">Dashboard Operacional</h1>
                            <div class="hidden md:flex space-x-2">
                                <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded-full text-xs font-medium">
                                    <span class="status-indicator bg-green-500 pulse"></span>
                                    Sistema Online
                                </span>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full text-xs font-medium">
                                    <i class="fas fa-clock mr-1"></i>
                                    Atualizado agora
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- TopBar Actions Responsivas -->
                    <div class="flex items-center space-x-2">
                        <!-- Search - Hidden on mobile -->
                        <!--div class="hidden md:block relative">
                            <input type="text" placeholder="Buscar..." 
                                   class="pl-8 pr-3 py-1.5 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent w-48 text-sm">
                            <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
                        </div>
                        
                        <!- Mobile Search Button ->
                        <button class="md:hidden p-1.5 bg-white/80 backdrop-blur-sm rounded-lg hover:bg-gray-100 transition-colors">
                            <i class="fas fa-search text-gray-600 text-sm"></i>
                        </button-->
                        
                        <!-- Notifications -->
                        <div class="relative" x-data="{ 
                            unreadCount: 0,
                            notifications: [],
                            async fetchUnreadCount() {
                                try {
                                    const response = await fetch('/notifications/unread-count');
                                    const data = await response.json();
                                    this.unreadCount = data.count;
                                } catch (error) {
                                    console.error('Erro ao buscar notificações:', error);
                                }
                            },
                            async fetchNotifications() {
                                try {
                                    const response = await fetch('/notifications');
                                    const data = await response.json();
                                    this.notifications = data.notifications || [];
                                } catch (error) {
                                    console.error('Erro ao buscar notificações:', error);
                                }
                            },
                            async markAsRead(notificationId) {
                                try {
                                    await fetch(`/notifications/${notificationId}/mark-as-read`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            'Content-Type': 'application/json'
                                        }
                                    });
                                    await this.fetchUnreadCount();
                                    await this.fetchNotifications();
                                } catch (error) {
                                    console.error('Erro ao marcar como lida:', error);
                                }
                            }
                        }" x-init="fetchUnreadCount(); fetchNotifications(); setInterval(() => { fetchUnreadCount(); fetchNotifications(); }, 30000)">
                            <button @click="notificationsOpen = !notificationsOpen; if(notificationsOpen) fetchNotifications()" class="relative p-1.5 bg-white/80 backdrop-blur-sm rounded-lg hover:bg-primary hover:text-white transition-all duration-200">
                                <i class="fas fa-bell text-sm"></i>
                                <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"></span>
                            </button>
                            
                            <!-- Notifications Dropdown -->
                            <div x-show="notificationsOpen" 
                                 @click.away="notificationsOpen = false" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-72 lg:w-80 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-gray-100 z-50">
                                <div class="p-4 border-b border-gray-100">
                                    <h3 class="font-semibold text-dark text-sm lg:text-base">Notificações</h3>
                                    <p class="text-xs lg:text-sm text-gray-500" x-text="unreadCount > 0 ? `${unreadCount} novas notificações` : 'Nenhuma notificação nova'"></p>
                                </div>
                                <div class="max-h-64 lg:max-h-80 overflow-y-auto">
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div @click="markAsRead(notification.id)" class="p-3 lg:p-4 border-b border-gray-50 hover:bg-gray-50 cursor-pointer">
                                            <div class="flex items-start space-x-3">
                                                <div class="w-6 h-6 lg:w-8 lg:h-8 rounded-full flex items-center justify-center flex-shrink-0"
                                                     :class="notification.tipo === 'alerta' ? 'bg-red-500' : 'bg-blue-500'">
                                                    <i class="text-white text-xs" 
                                                       :class="notification.tipo === 'alerta' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle'"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs lg:text-sm font-medium text-dark truncate" x-text="notification.titulo"></p>
                                                    <p class="text-xs text-gray-500" x-text="notification.mensagem"></p>
                                                    <p class="text-xs text-gray-400 mt-1" x-text="notification.created_at_human"></p>
                                                </div>
                                                <span x-show="!notification.read_at" class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></span>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <div x-show="notifications.length === 0" class="p-4 text-center text-gray-500 text-sm">
                                        Nenhuma notificação encontrada
                                    </div>
                                </div>
                                
                                <div x-show="unreadCount > 0" class="p-3 border-t border-gray-100">
                                    <button @click="fetch('/notifications/mark-all-as-read', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}}).then(() => {fetchUnreadCount(); fetchNotifications()})" 
                                            class="w-full text-center text-sm text-primary hover:text-primary-dark font-medium">
                                        Marcar todas como lidas
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile -->
                        <div class="relative">
                            <button @click="profileOpen = !profileOpen" class="flex items-center space-x-2 p-1 rounded-lg bg-white/80 backdrop-blur-sm hover:bg-gray-50 transition-all duration-200">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                                         alt="Profile" class="w-7 h-7 rounded-full object-cover border-2 border-primary">
                                @else
                                    <div class="w-7 h-7 rounded-full bg-primary flex items-center justify-center border-2 border-primary">
                                        <span class="text-white text-xs font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="text-left hidden lg:block">
                                    <p class="text-xs font-semibold text-dark">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Auth::user()->getRoleNames()->first() ?? 'Usuário' }}</p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-xs hidden lg:block"></i>
                            </button>
                            
                            <!-- Profile Dropdown -->
                            <div x-show="profileOpen" 
                                 @click.away="profileOpen = false" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 lg:w-56 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-gray-100 z-50">
                                <!--div class="p-3 lg:p-4 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if(Auth::user()->avatar)
                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                     src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                                                     alt="{{ Auth::user()->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-lg font-medium text-gray-700">
                                                        {{ substr(Auth::user()->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ Auth::user()->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ Auth::user()->cargo ?? 'Cargo não definido' }}
                                            </p>
                                        </div>
                                    </div>
                                </div-->
                                
                                <div class="py-2">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center px-3 lg:px-4 py-2 lg:py-3 text-gray-700 hover:bg-gray-50 text-sm">
                                        <i class="fas fa-user mr-3 text-gray-400"></i>
                                        Meu Perfil
                                    </a>
                                    <!--a href="#" class="flex items-center px-3 lg:px-4 py-2 lg:py-3 text-gray-700 hover:bg-gray-50 text-sm">
                                        <i class="fas fa-cog mr-3 text-gray-400"></i>
                                        Configurações
                                    </a-->
                                    <hr class="my-2" style="color:#e6e6e6">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-3 lg:px-4 py-2 lg:py-3 text-red-600 hover:bg-red-50 text-sm">
                                            <i class="fas fa-sign-out-alt mr-3"></i>
                                            Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 relative">
               
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms x-init="setTimeout(() => show = false, 5000)" class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3 text-lg"></i>
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                            <button @click="show = false" class="text-green-500 hover:text-green-700 focus:outline-none">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-red-500 mr-3 text-lg"></i>
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                            <button @click="show = false" class="text-red-500 hover:text-red-700 focus:outline-none">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endif
                
                {{ $slot }}
               
            </main>
        </div>
    </div>
    <script>
        function markAsRead(notificationId, actionUrl) {
            fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                if (actionUrl && actionUrl !== '#') {
                    window.location.href = actionUrl;
                } else {
                    location.reload();
                }
            });
        }

        function markAllAsRead() {
            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                location.reload();
            });
        }
    </script>
</body>
</html>
