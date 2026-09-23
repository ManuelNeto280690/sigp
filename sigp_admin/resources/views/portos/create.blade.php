<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 shadow-lg" style="background: linear-gradient(135deg, #0086e1 0%, #01043d 100%);">
                    <i class="fas fa-plus text-white text-sm"></i>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Novo Porto') }}
                </h2>
            </div>
            <a href="{{ route('portos.index') }}" class="text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center" style="background: #3395da; border: 2px solid #3395da;">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-lg">
                <div class="px-8 py-6 border-b border-gray-100" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Cadastro de Porto</h3>
                        <div class="text-xs text-gray-600">Preencha as informações do porto</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('portos.store') }}" class="space-y-8">
                    @csrf

                    <!-- Identificação -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                <i class="fas fa-anchor text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Identificação</h3>
                                <p class="text-gray-600 text-sm">Dados básicos do porto</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="nome" :value="__('Nome *')" />
                                <x-text-input
                                    id="nome"
                                    name="nome"
                                    type="text"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('nome')"
                                    required
                                    maxlength="255"
                                    placeholder="Digite o nome do porto"
                                />
                            </div>

                            <!--div class="space-y-2">
                                <x-input-label for="slug" :value="__('Slug')" />
                                <x-text-input id="slug" name="slug" type="text" class="block w-full" :value="old('slug')" maxlength="255"/>
                                <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                            </div-->

                            <!--div class="space-y-2">
                                <x-input-label for="dominio" :value="__('Domínio *')" />
                                <x-text-input id="dominio" name="dominio" type="text" class="block w-full" :value="old('dominio')" required maxlength="255"/>
                                <x-input-error :messages="$errors->get('dominio')" class="mt-1" />
                            </div-->
                        </div>
                    </div>

                    <!-- Contato -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="fas fa-id-card text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Contato</h3>
                                <p class="text-gray-600 text-sm">Informações de contato do porto</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('email')"
                                    maxlength="255"
                                    placeholder="porto@exemplo.com"
                                />
                                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="telefone" :value="__('Telefone')" />
                                <x-text-input
                                    id="telefone"
                                    name="telefone"
                                    type="text"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('telefone')"
                                    maxlength="50"
                                    placeholder="Ex.: (11) 99999-0000"
                                />
                                <x-input-error :messages="$errors->get('telefone')" class="mt-1" />
                            </div>
                            <div class="space-y-2 lg:col-span-1 md:col-span-2">
                                <x-input-label for="endereco" :value="__('Endereço')" />
                                <x-text-input
                                    id="endereco"
                                    name="endereco"
                                    type="text"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('endereco')"
                                    maxlength="255"
                                    placeholder="Rua, número, cidade/UF"
                                />
                                <x-input-error :messages="$errors->get('endereco')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="responsavel" :value="__('Responsável')" />
                                <x-text-input
                                    id="responsavel"
                                    name="responsavel"
                                    type="text"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('responsavel')"
                                    maxlength="255"
                                    placeholder="Nome do responsável"
                                />
                                <x-input-error :messages="$errors->get('responsavel')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="telefone_responsavel" :value="__('Telefone do Responsável')" />
                                <x-text-input
                                    id="telefone_responsavel"
                                    name="telefone_responsavel"
                                    type="text"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('telefone_responsavel')"
                                    maxlength="50"
                                    placeholder="Ex.: (11) 99999-0000"
                                />
                                <x-input-error :messages="$errors->get('telefone_responsavel')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Operacional -->
                    <div class="p-8 border-b border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <i class="fas fa-tools text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Operacional</h3>
                                <p class="text-gray-600 text-sm">Configurações operacionais</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="num_funcionarios" :value="__('Número de Funcionários')" />
                                <x-text-input
                                    id="num_funcionarios"
                                    name="num_funcionarios"
                                    type="number"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('num_funcionarios')"
                                    min="0"
                                    placeholder="Quantidade de funcionários"
                                />
                                <x-input-error :messages="$errors->get('num_funcionarios')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="path" :value="__('Path em public_html')" />
                                <x-text-input
                                    id="path"
                                    name="path"
                                    type="text"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('path')"
                                    maxlength="255"
                                    placeholder="Ex.: public_html/portos/meu-porto"
                                />
                                <x-input-error :messages="$errors->get('path')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="worker_status" :value="__('Status do Worker *')" />
                                <select id="worker_status" name="worker_status" class="block w-full" required>
                                    @foreach(['inactive','active'] as $s)
                                        <option value="{{ $s }}" {{ old('worker_status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('worker_status')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Banco de Dados (Sensível) -->
                    <div class="p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 shadow-lg" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                                <i class="fas fa-database text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Banco de Dados</h3>
                                <p class="text-gray-600 text-sm">Credenciais criptografadas — não serão exibidas em listas/detalhes</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="db_nome" :value="__('Nome do Banco')" />
                                <x-text-input
                                    id="db_nome"
                                    name="db_nome"
                                    type="text"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('db_nome')"
                                    maxlength="255"
                                    placeholder="Nome do banco de dados"
                                />
                                <x-input-error :messages="$errors->get('db_nome')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="db_usuario" :value="__('Usuário do Banco')" />
                                <x-text-input
                                    id="db_usuario"
                                    name="db_usuario"
                                    type="text"
                                    class="block w-full text-base px-4 py-3"
                                    :value="old('db_usuario')"
                                    maxlength="255"
                                    placeholder="Usuário do banco de dados"
                                />
                                <x-input-error :messages="$errors->get('db_usuario')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="db_senha" :value="__('Senha do Banco')" />
                                <input
                                    id="db_senha"
                                    name="db_senha"
                                    type="password"
                                    class="block w-full border-gray-300 rounded-md text-base px-4 py-3"
                                    autocomplete="new-password"
                                    placeholder="Senha do banco de dados"
                                />
                                <x-input-error :messages="$errors->get('db_senha')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="text-white font-bold px-6 py-2 rounded-lg shadow-lg transition duration-200" style="background: #3395da;">
                            <i class="fas fa-save mr-2"></i>Salvar Porto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>