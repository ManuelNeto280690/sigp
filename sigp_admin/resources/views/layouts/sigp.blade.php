<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SIGP' }} - Sistema Integrado de Gestão Portuária</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('styles')
</head>
<body class="font-inter antialiased bg-gray-50">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
             class="fixed inset-y-0 left-0 z-50 w-64 bg-blue-900 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 bg-blue-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-anchor text-white text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <h1 class="text-white font-bold text-lg">SIGP</h1>
                        <p class="text-blue-200 text-xs">Sistema Portuário</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white hover:text-blue-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mt-8 px-4">
                <div class="space-y-2">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-800 border-r-4 border-yellow-400' : '' }}">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>

                    <!-- Embarcações -->
                    <div x-data="{ open: {{ request()->routeIs('embarcacao*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('embarcacao*') ? 'bg-blue-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-ship w-5"></i>
                                <span class="ml-3">Embarcações</span>
                            </div>
                            <i class="fas fa-chevron-down transform transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('embarcacao.index') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-list w-4 mr-2"></i>Listar
                            </a>
                            @can('create', App\Models\Embarcacao::class)
                            <a href="{{ route('embarcacao.create') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-plus w-4 mr-2"></i>Cadastrar
                            </a>
                            @endcan
                        </div>
                    </div>

                    <!-- Concessionárias -->
                    @canany(['viewAny', 'create'], App\Models\Concessionaria::class)
                    <div x-data="{ open: {{ request()->routeIs('concessionaria*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('concessionaria*') ? 'bg-blue-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-building w-5"></i>
                                <span class="ml-3">Concessionárias</span>
                            </div>
                            <i class="fas fa-chevron-down transform transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('concessionaria.index') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-list w-4 mr-2"></i>Listar
                            </a>
                            @can('create', App\Models\Concessionaria::class)
                            <a href="{{ route('concessionaria.create') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-plus w-4 mr-2"></i>Cadastrar
                            </a>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    <!-- Movimentação -->
                    <div x-data="{ open: {{ request()->routeIs('movimento*') || request()->routeIs('entrada-saida*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('movimento*') || request()->routeIs('entrada-saida*') ? 'bg-blue-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-exchange-alt w-5"></i>
                                <span class="ml-3">Movimentação</span>
                            </div>
                            <i class="fas fa-chevron-down transform transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('movimento-terminal.index') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-warehouse w-4 mr-2"></i>Terminal
                            </a>
                            <a href="{{ route('entrada-saida-embarcacao.index') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-ship w-4 mr-2"></i>Embarcações
                            </a>
                        </div>
                    </div>

                    <!-- Alertas -->
                    <a href="{{ route('alerta.index') }}" 
                       class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('alerta*') ? 'bg-blue-800 border-r-4 border-yellow-400' : '' }}">
                        <i class="fas fa-exclamation-triangle w-5"></i>
                        <span class="ml-3">Alertas</span>
                        @if(auth()->user()->unreadNotifications->where('type', 'App\Notifications\AlertaNotification')->count() > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">
                            {{ auth()->user()->unreadNotifications->where('type', 'App\Notifications\AlertaNotification')->count() }}
                        </span>
                        @endif
                    </a>

                    <!-- Incidentes -->
                    <a href="{{ route('incidente.index') }}" 
                       class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('incidente*') ? 'bg-blue-800 border-r-4 border-yellow-400' : '' }}">
                        <i class="fas fa-exclamation-circle w-5"></i>
                        <span class="ml-3">Incidentes</span>
                    </a>

                    <!-- Inspeções -->
                    <a href="{{ route('inspecao.index') }}" 
                       class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('inspecao*') ? 'bg-blue-800 border-r-4 border-yellow-400' : '' }}">
                        <i class="fas fa-clipboard-check w-5"></i>
                        <span class="ml-3">Inspeções</span>
                    </a>

                    <!-- Infrações Ambientais -->
                    <a href="{{ route('infracao-ambiental.index') }}" 
                       class="flex items-center px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('infracao-ambiental*') ? 'bg-blue-800 border-r-4 border-yellow-400' : '' }}">
                        <i class="fas fa-leaf w-5"></i>
                        <span class="ml-3">Infrações Ambientais</span>
                    </a>

                    <!-- Relatórios -->
                    @canany(['viewAny'], [App\Models\AuditLog::class])
                    <div x-data="{ open: {{ request()->routeIs('relatorio*') || request()->routeIs('audit-log*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('relatorio*') || request()->routeIs('audit-log*') ? 'bg-blue-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-chart-bar w-5"></i>
                                <span class="ml-3">Relatórios</span>
                            </div>
                            <i class="fas fa-chevron-down transform transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('relatorio.dashboard') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-chart-line w-4 mr-2"></i>Dashboard
                            </a>
                            @can('viewAny', App\Models\AuditLog::class)
                            <a href="{{ route('audit-log.index') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-history w-4 mr-2"></i>Logs de Auditoria
                            </a>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    <!-- Configurações -->
                    @role('admin')
                    <div x-data="{ open: {{ request()->routeIs('configuracao*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" 
                                class="flex items-center justify-between w-full px-4 py-3 text-white rounded-lg hover:bg-blue-800 transition-colors {{ request()->routeIs('configuracao*') ? 'bg-blue-800' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-cog w-5"></i>
                                <span class="ml-3">Configurações</span>
                            </div>
                            <i class="fas fa-chevron-down transform transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                            <a href="{{ route('configuracao.index') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-sliders-h w-4 mr-2"></i>Sistema
                            </a>
                            <a href="{{ route('user.index') }}" class="block px-4 py-2 text-blue-200 hover:text-white hover:bg-blue-800 rounded transition-colors">
                                <i class="fas fa-users w-4 mr-2"></i>Usuários
                            </a>
                        </div>
                    </div>
                    @endrole
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-6">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        @isset($breadcrumbs)
                        <nav class="hidden lg:flex ml-6" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2">
                                @foreach($breadcrumbs as $breadcrumb)
                                <li class="flex items-center">
                                    @if(!$loop->first)
                                    <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                                    @endif
                                    @if($loop->last)
                                    <span class="text-gray-500 font-medium">{{ $breadcrumb['title'] }}</span>
                                    @else
                                    <a href="{{ $breadcrumb['url'] }}" class="text-blue-600 hover:text-blue-800 font-medium">{{ $breadcrumb['title'] }}</a>
                                    @endif
                                </li>
                                @endforeach
                            </ol>
                        </nav>
                        @endisset
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                <i class="fas fa-bell text-xl"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                                @endif
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition 
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="font-semibold text-gray-900">Notificações</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50">
                                        <p class="text-sm text-gray-900">{{ $notification->data['message'] ?? 'Nova notificação' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                    @empty
                                    <div class="p-4 text-center text-gray-500">
                                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                        <p class="text-sm">Nenhuma notificação</p>
                                    </div>
                                    @endforelse
                                </div>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <div class="p-4 border-t border-gray-200">
                                    <a href="{{ route('notification.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Ver todas</a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <div class="hidden lg:block text-left">
                                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Auth::user()->getRoleNames()->first() }}</p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition 
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="p-2">
                                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                        <i class="fas fa-user w-4 mr-3"></i>
                                        Perfil
                                    </a>
                                    <a href="{{ route('profile.2fa') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                        <i class="fas fa-shield-alt w-4 mr-3"></i>
                                        Autenticação 2FA
                                    </a>
                                    <hr class="my-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 rounded">
                                            <i class="fas fa-sign-out-alt w-4 mr-3"></i>
                                            Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition 
                     class="bg-green-50 border-l-4 border-green-400 p-4 m-6">
                    <div class="flex items-center justify-between">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <p class="text-green-700">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false" class="text-green-400 hover:text-green-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition 
                     class="bg-red-50 border-l-4 border-red-400 p-4 m-6">
                    <div class="flex items-center justify-between">
                        <div class="flex">
                            <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                            <p class="text-red-700">{{ session('error') }}</p>
                        </div>
                        <button @click="show = false" class="text-red-400 hover:text-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div x-data="{ show: true }" x-show="show" x-transition 
                     class="bg-red-50 border-l-4 border-red-400 p-4 m-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex">
                                <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-1"></i>
                                <div>
                                    <p class="text-red-700 font-medium mb-2">Corrija os seguintes erros:</p>
                                    <ul class="text-red-600 text-sm list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <button @click="show = false" class="text-red-400 hover:text-red-600 ml-4">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    @stack('scripts')
</body>
</html>