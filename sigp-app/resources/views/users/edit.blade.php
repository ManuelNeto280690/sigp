<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Usuário') }}
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

                    <form action="{{ route('users.update', $user) }}" method="POST" id="editUserForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nome -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome Completo <span class="text-red-500">*</span></label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                                <input type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Telefone -->
                            <div>
                                <label for="telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('telefone') border-red-500 @enderror" 
                                       id="telefone" name="telefone" value="{{ old('telefone', $user->telefone) }}" placeholder="(11) 99999-9999">
                                @error('telefone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cargo -->
                            <div>
                                <label for="cargo" class="block text-sm font-medium text-gray-700">Cargo</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('cargo') border-red-500 @enderror" 
                                       id="cargo" name="cargo" value="{{ old('cargo', $user->cargo) }}">
                                @error('cargo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Departamento -->
                            <div>
                                <label for="departamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('departamento') border-red-500 @enderror" 
                                       id="departamento" name="departamento" value="{{ old('departamento', $user->departamento) }}">
                                @error('departamento')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nova Senha -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                                <input type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror" 
                                       id="password" name="password" placeholder="Deixe em branco para manter a senha atual">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Mínimo 8 caracteres</p>
                            </div>

                            <!-- Confirmar Nova Senha -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                                <input type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                       id="password_confirmation" name="password_confirmation" placeholder="Confirme a nova senha">
                            </div>

                            <!-- Status do Usuário -->
                            <div class="md:col-span-2">
                                <div class="flex items-center space-x-6">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                               name="is_active" value="1" 
                                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Usuário Ativo</span>
                                    </label>
                                </div>
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
                                                   {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}>
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
                                        <input type="hidden" name="two_factor_enabled" value="0">
                                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                               id="two_factor_enabled" name="two_factor_enabled" value="1" 
                                               {{ old('two_factor_enabled', $user->two_factor_enabled) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Habilitar Autenticação de Dois Fatores</span>
                                    </label>
                                </div>
                                
                                <div id="two_factor_method_group" class="mt-2" style="{{ old('two_factor_enabled', $user->two_factor_enabled) ? 'display: block;' : 'display: none;' }}">
                                    <label for="two_factor_method" class="block text-sm font-medium text-gray-700">Método de 2FA</label>
                                    <select name="two_factor_method" id="two_factor_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Selecione um método</option>
                                        @foreach(App\Models\User::getTwoFactorMethods() as $key => $method)
                                            <option value="{{ $key }}" {{ old('two_factor_method', $user->two_factor_method) == $key ? 'selected' : '' }}>{{ $method }}</option>
                                        @endforeach
                                    </select>
                                    @error('two_factor_method')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-2">
                            <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save"></i> Atualizar Usuário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
    $(document).ready(function() {
        console.log('Script carregado'); // Debug
        
        // Máscara para telefone
        $('#telefone').mask('(00) 00000-0000');
        
        // Verificar estado inicial do 2FA
        function toggleTwoFactorMethod() {
            const isChecked = $('#two_factor_enabled').is(':checked');
            console.log('2FA checkbox estado:', isChecked); // Debug
            
            if (isChecked) {
                $('#two_factor_method_group').show();
            } else {
                $('#two_factor_method_group').hide();
                $('#two_factor_method').val('');
            }
        }
        
        // Executar na inicialização
        toggleTwoFactorMethod();
        
        // Toggle do método 2FA
        $('#two_factor_enabled').on('change', function() {
            console.log('Checkbox mudou:', $(this).is(':checked')); // Debug
            toggleTwoFactorMethod();
        });
        
        // Validação do formulário
        $('#editUserForm').on('submit', function(e) {
            let password = $('#password').val();
            let passwordConfirmation = $('#password_confirmation').val();
            
            if (password && password !== passwordConfirmation) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return false;
            }
            
            if ($('#two_factor_enabled').is(':checked') && !$('#two_factor_method').val()) {
                e.preventDefault();
                alert('Selecione um método para a autenticação de dois fatores!');
                return false;
            }
        });
    });
    </script>
    @endpush
</x-app-layout>