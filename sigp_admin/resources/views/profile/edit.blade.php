<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center">
                    <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-lg">
                        <i class="fas fa-user text-white text-xs"></i>
                    </div>
                    {{ __('Meu Perfil') }}
                </h2>
                <p class="text-gray-600 text-xs mt-1">Gerencie suas informações pessoais e configurações de conta</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="location.reload()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-1.5 px-3 rounded-lg transition duration-200 flex items-center text-xs">
                    <i class="fas fa-sync-alt mr-1 text-xs"></i>Atualizar
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Profile Header Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-8">
                    <div class="flex items-center space-x-4">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                                 alt="Profile" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                        @else
                            <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center border-4 border-white shadow-lg">
                                <span class="text-blue-600 text-2xl font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="text-white">
                            <h3 class="text-2xl font-bold">{{ Auth::user()->name }}</h3>
                            <p class="text-blue-100">{{ Auth::user()->email }}</p>
                            <div class="flex items-center mt-2">
                                <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-medium">
                                    {{ Auth::user()->getRoleNames()->first() ?? 'Usuário' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Forms Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Profile Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Informações do Perfil</h3>
                            <p class="text-sm text-gray-600">Atualize suas informações pessoais</p>
                        </div>
                    </div>
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Password Update -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-lock text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Alterar Senha</h3>
                            <p class="text-sm text-gray-600">Mantenha sua conta segura</p>
                        </div>
                    </div>
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="mt-6">
                <div class="bg-white rounded-2xl shadow-sm border border-red-200 p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-red-900">Zona de Perigo</h3>
                            <p class="text-sm text-red-600">Ações irreversíveis da conta</p>
                        </div>
                    </div>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
