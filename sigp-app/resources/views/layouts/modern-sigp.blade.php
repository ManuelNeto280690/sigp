<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
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
<body class="h-full font-inter antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 z-50 flex w-72 flex-col" x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" style="display: none;">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gradient-to-b from-blue-900 to-blue-800 px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10">
                            <i class="fas fa-anchor text-white text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-white font-bold text-lg">SIGP</h1>
                            <p class="text-blue-200 text-xs">Sistema Portuário</p>
                        </div>
                    </div>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <!-- Dashboard -->
                                <li>
                                    <a href="{{ route('dashboard') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold {{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white' : 'text-blue-200 hover:text-white hover:bg-blue-700' }}">
                                        <i class="fas fa-home h-6 w-6 shrink-0"></i>
                                        Dashboard
                                    </a>
                                </li>
                                
                                <!-- Embarcações -->
                                <li x-data="{ open: {{ request()->routeIs('embarcacao*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="group flex w-full items-center gap-x-3 rounded-md p-2 text-left text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-ship h-6 w-6 shrink-0"></i>
                                        Embarcações
                                        <i class="fas fa-chevron-right ml-auto h-5 w-5 shrink-0 transition-transform" :class="open ? 'rotate-90' : ''"></i>
                                    </button>
                                    <ul x-show="open" x-transition class="mt-1 px-2">
                                        <li><a href="{{ route('embarcacoes.index') }}" class="group flex gap-x-3 rounded-md py-2 pl-8 pr-2 text-sm leading-6 text-blue-200 hover:text-white hover:bg-blue-700">Listar</a></li>
                                        <li><a href="{{ route('embarcacoes.create') }}" class="group flex gap-x-3 rounded-md py-2 pl-8 pr-2 text-sm leading-6 text-blue-200 hover:text-white hover:bg-blue-700">Cadastrar</a></li>
                                    </ul>
                                </li>
                                
                                <!-- Alertas -->
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-exclamation-triangle h-6 w-6 shrink-0"></i>
                                        Alertas
                                    </a>
                                </li>
                                
                                <!-- Incidentes -->
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-exclamation-circle h-6 w-6 shrink-0"></i>
                                        Incidentes
                                    </a>
                                </li>
                                
                                <!-- Relatórios -->
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-chart-bar h-6 w-6 shrink-0"></i>
                                        Relatórios
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        @role('admin')
                        <li>
                            <div class="text-xs font-semibold leading-6 text-blue-300">Administração</div>
                            <ul role="list" class="-mx-2 mt-2 space-y-1">
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-users h-6 w-6 shrink-0"></i>
                                        Usuários
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-cog h-6 w-6 shrink-0"></i>
                                        Configurações
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endrole
                    </ul>
                </nav>
            </div>
        </div>
        
        <!-- Desktop sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gradient-to-b from-blue-900 to-blue-800 px-6 pb-4">
                <div class="flex h-16 shrink-0 items-center">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10">
                            <i class="fas fa-anchor text-white text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-white font-bold text-lg">SIGP</h1>
                            <p class="text-blue-200 text-xs">Sistema Portuário</p>
                        </div>
                    </div>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <!-- Dashboard -->
                                <li>
                                    <a href="{{ route('dashboard') }}" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold {{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white' : 'text-blue-200 hover:text-white hover:bg-blue-700' }}">
                                        <i class="fas fa-home h-6 w-6 shrink-0"></i>
                                        Dashboard
                                    </a>
                                </li>
                                
                                <!-- Embarcações -->
                                <li x-data="{ open: {{ request()->routeIs('embarcacao*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="group flex w-full items-center gap-x-3 rounded-md p-2 text-left text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-ship h-6 w-6 shrink-0"></i>
                                        Embarcações
                                        <i class="fas fa-chevron-right ml-auto h-5 w-5 shrink-0 transition-transform" :class="open ? 'rotate-90' : ''"></i>
                                    </button>
                                    <ul x-show="open" x-transition class="mt-1 px-2">
                                        <li><a href="{{ route('embarcacoes.index') }}" class="group flex gap-x-3 rounded-md py-2 pl-8 pr-2 text-sm leading-6 text-blue-200 hover:text-white hover:bg-blue-700">Listar</a></li>
                                        <li><a href="{{ route('embarcacoes.create') }}" class="group flex gap-x-3 rounded-md py-2 pl-8 pr-2 text-sm leading-6 text-blue-200 hover:text-white hover:bg-blue-700">Cadastrar</a></li>
                                    </ul>
                                </li>
                                
                                <!-- Alertas -->
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-exclamation-triangle h-6 w-6 shrink-0"></i>
                                        Alertas
                                    </a>
                                </li>
                                
                                <!-- Incidentes -->
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-exclamation-circle h-6 w-6 shrink-0"></i>
                                        Incidentes
                                    </a>
                                </li>
                                
                                <!-- Relatórios -->
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-chart-bar h-6 w-6 shrink-0"></i>
                                        Relatórios
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        @role('admin')
                        <li>
                            <div class="text-xs font-semibold leading-6 text-blue-300">Administração</div>
                            <ul role="list" class="-mx-2 mt-2 space-y-1">
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-users h-6 w-6 shrink-0"></i>
                                        Usuários
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold text-blue-200 hover:text-white hover:bg-blue-700">
                                        <i class="fas fa-cog h-6 w-6 shrink-0"></i>
                                        Configurações
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endrole
                    </ul>
                </nav>
            </div>
        </div>
        
        <div class="lg:pl-72">
            <!-- Navbar -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Abrir sidebar</span>
                    <i class="fas fa-bars h-6 w-6"></i>
                </button>
                
                <!-- Separator -->
                <div class="h-6 w-px bg-gray-200 lg:hidden"></div>
                
                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="relative flex flex-1 items-center">
                        @isset($breadcrumbs)
                        <nav class="flex" aria-label="Breadcrumb">
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
                    
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Notifications -->
                        <div x-data="{ open: false }" class="relative">
                            <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500" @click="open = !open">
                                <span class="sr-only">Ver notificações</span>
                                <i class="fas fa-bell h-6 w-6"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-400 flex items-center justify-center">
                                    <span class="text-xs font-medium text-white">{{ auth()->user()->unreadNotifications->count() }}</span>
                                </span>
                                @endif
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-10 mt-2.5 w-80 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none" style="display: none;">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <h3 class="text-sm font-semibold text-gray-900">Notificações</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <div class="px-4 py-3 hover:bg-gray-50">
                                        <p class="text-sm text-gray-900">{{ $notification->data['message'] ?? 'Nova notificação' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                    @empty
                                    <div class="px-4 py-8 text-center">
                                        <i class="fas fa-bell-slash text-gray-300 text-2xl mb-2"></i>
                                        <p class="text-sm text-gray-500">Nenhuma notificação</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        
                        <!-- Separator -->
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200"></div>
                        
                        <!-- Profile dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button type="button" class="-m-1.5 flex items-center p-1.5" @click="open = !open">
                                <span class="sr-only">Abrir menu do usuário</span>
                                <img class="h-8 w-8 rounded-full bg-gray-50" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff" alt="">
                                <span class="hidden lg:flex lg:items-center">
                                    <span class="ml-4 text-sm font-semibold leading-6 text-gray-900">{{ auth()->user()->name }}</span>
                                    <i class="fas fa-chevron-down ml-2 h-5 w-5 text-gray-400"></i>
                                </span>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none" style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="block px-3 py-1 text-sm leading-6 text-gray-900 hover:bg-gray-50">
                                    <i class="fas fa-user mr-2"></i>Perfil
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-3 py-1 text-sm leading-6 text-gray-900 hover:bg-gray-50">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Sair
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <main class="py-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    <!-- Alerts -->
                    @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="rounded-md bg-green-50 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle h-5 w-5 text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="show = false" class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100">
                                        <i class="fas fa-times h-5 w-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="rounded-md bg-red-50 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle h-5 w-5 text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="show = false" class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100">
                                        <i class="fas fa-times h-5 w-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>