<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Adicionar Novo Usuário') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Success Message -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Error Messages -->
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Informações Básicas -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome <span class="text-red-500">*</span></label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                                <input type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Senha <span class="text-red-500">*</span></label>
                                <input type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Senha <span class="text-red-500">*</span></label>
                                <input type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <!-- Roles -->
                            @if(isset($roles) && $roles->count() > 0)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Funções</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($roles as $role)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                                   name="roles[]" value="{{ $role->name }}" 
                                                   {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Two Factor Authentication -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Autenticação de Dois Fatores</label>
                                <div class="space-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                               name="two_factor_enabled" value="1" 
                                               {{ old('two_factor_enabled') ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Habilitar Autenticação de Dois Fatores</span>
                                    </label>
                                </div>
                                
                                <div class="mt-2">
                                    <label for="two_factor_method" class="block text-sm font-medium text-gray-700">Método de 2FA</label>
                                    <select name="two_factor_method" id="two_factor_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Selecione um método</option>
                                        @if(method_exists(App\Models\User::class, 'getTwoFactorMethods'))
                                            @foreach(App\Models\User::getTwoFactorMethods() as $key => $method)
                                                <option value="{{ $key }}" {{ old('two_factor_method') == $key ? 'selected' : '' }}>{{ $method }}</option>
                                            @endforeach
                                        @else
                                            <option value="google2fa">Google Authenticator</option>
                                            <option value="sms">SMS</option>
                                            <option value="email">Email</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-2">
                            <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save"></i> Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>