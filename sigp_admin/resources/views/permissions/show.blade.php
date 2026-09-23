<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes da Permissão') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('permissions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
                @can('permissions.edit')
                <a href="{{ route('permissions.edit', $permission) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 focus:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Informações da Permissão</h3>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Nome</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $permission->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Guard Name</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $permission->guard_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">ID</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $permission->id }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Criado em</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ $permission->created_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Funções com esta Permissão</h3>
                        <div class="bg-gray-50 p-4 rounded-md">
                            @if ($permission->roles->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach ($permission->roles as $role)
                                        <div class="bg-white p-3 rounded-md shadow">
                                            <a href="{{ route('roles.show', $role) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">{{ $role->name }}</a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">Esta permissão não está atribuída a nenhuma função.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>