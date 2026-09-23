<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-anchor mr-2 text-blue-600"></i>
                    {{ __('Editar Porto') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $porto->nome }} - Domínio: {{ $porto->dominio }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('portos.show', $porto) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-eye mr-2"></i>Visualizar
                </a>
                <a href="{{ route('portos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md shadow-sm transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <form method="POST" action="{{ route('portos.update', $porto) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Informações -->
                    <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-info-circle text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Informações do Porto</h3>
                                <p class="text-sm text-gray-600">Dados fundamentais</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="nome" :value="__('Nome')" />
                                <x-text-input id="nome" name="nome" type="text" class="block w-full text-base px-4 py-3" :value="old('nome', $porto->nome)" required maxlength="255"/>
                                <x-input-error :messages="$errors->get('nome')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="slug" :value="__('Slug')" />
                                <x-text-input id="slug" name="slug" type="text" class="block w-full text-base px-4 py-3" :value="old('slug', $porto->slug)" readonly maxlength="255"/>
                                <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <x-input-label for="dominio" :value="__('Domínio')" />
                                <x-text-input id="dominio" name="dominio" type="text" class="block w-full text-base px-4 py-3" :value="old('dominio', $porto->dominio)" readonly maxlength="255"/>
                                <x-input-error :messages="$errors->get('dominio')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Contato e Operação -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="block w-full text-base px-4 py-3" :value="old('email', $porto->email)" maxlength="255"/>
                                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="telefone" :value="__('Telefone')" />
                                <x-text-input id="telefone" name="telefone" type="text" class="block w-full text-base px-4 py-3" :value="old('telefone', $porto->telefone)" maxlength="50"/>
                                <x-input-error :messages="$errors->get('telefone')" class="mt-1" />
                            </div>
                            <div class="space-y-2 lg:col-span-1 md:col-span-2">
                                <x-input-label for="endereco" :value="__('Endereço')" />
                                <x-text-input id="endereco" name="endereco" type="text" class="block w-full text-base px-4 py-3" :value="old('endereco', $porto->endereco)" maxlength="255"/>
                                <x-input-error :messages="$errors->get('endereco')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="responsavel" :value="__('Responsável')" />
                                <x-text-input id="responsavel" name="responsavel" type="text" class="block w-full text-base px-4 py-3" :value="old('responsavel', $porto->responsavel)" maxlength="255"/>
                                <x-input-error :messages="$errors->get('responsavel')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="telefone_responsavel" :value="__('Telefone do Responsável')" />
                                <x-text-input id="telefone_responsavel" name="telefone_responsavel" type="text" class="block w-full text-base px-4 py-3" :value="old('telefone_responsavel', $porto->telefone_responsavel)" maxlength="50"/>
                                <x-input-error :messages="$errors->get('telefone_responsavel')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="num_funcionarios" :value="__('Nº de Funcionários')" />
                                <x-text-input id="num_funcionarios" name="num_funcionarios" type="number" class="block w-full text-base px-4 py-3" :value="old('num_funcionarios', $porto->num_funcionarios)" min="0"/>
                                <x-input-error :messages="$errors->get('num_funcionarios')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="path" :value="__('Path em public_html')" />
                                <x-text-input id="path" name="path" type="text" class="block w-full text-base px-4 py-3" :value="old('path', $porto->path)" maxlength="255"/>
                                <x-input-error :messages="$errors->get('path')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="worker_status" :value="__('Status do Worker *')" />
                                <select id="worker_status" name="worker_status" class="block w-full text-base px-4 py-3" required>
                                    @foreach(['running','idle','stopped','error'] as $s)
                                        <option value="{{ $s }}" {{ old('worker_status', $porto->worker_status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('worker_status')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Banco de Dados (Sensível) -->
                    <div class="p-6 bg-gray-50 border-t">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <x-input-label for="db_nome" :value="__('Nome do Banco')" />
                                <x-text-input id="db_nome" name="db_nome" type="text" class="block w-full text-base px-4 py-3" :value="old('db_nome', $porto->db_nome)" maxlength="255"/>
                                <x-input-error :messages="$errors->get('db_nome')" class="mt-1" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="db_usuario" :value="__('Usuário do Banco')" />
                                <x-text-input id="db_usuario" name="db_usuario" type="text" class="block w-full text-base px-4 py-3" :value="old('db_usuario', '')" maxlength="255"/>
                                <x-input-error :messages="$errors->get('db_usuario')" class="mt-1" />
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <x-input-label for="db_senha" :value="__('Senha do Banco')" />
                                <input id="db_senha" name="db_senha" type="password" class="block w-full border-gray-300 rounded-md text-base px-4 py-3" autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('db_senha')" class="mt-1" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Por segurança, credenciais não são exibidas. Informe novos valores apenas se desejar alterá-los.</p>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <button type="submit" class="text-white font-bold px-6 py-2 rounded-lg shadow-lg transition duration-200" style="background: #3395da;">
                            <i class="fas fa-save mr-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>