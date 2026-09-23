<x-guest-layout>
    <div class="text-center">
        <!-- Ícone de erro -->
        <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-6">
            <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
        </div>

        <!-- Título do erro -->
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Página Expirada</h1>
        
        <!-- Mensagem de erro -->
        <p class="text-gray-600 mb-6">
            Sua sessão expirou. Por favor, faça login novamente para continuar.
        </p>

        <!-- Botão Voltar para Login -->
        <a href="{{ route('login') }}" 
           class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-blue-700 transition-all duration-300 btn-hover">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar para Login
        </a>
    </div>
</x-guest-layout>