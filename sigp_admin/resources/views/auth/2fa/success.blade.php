<x-guest-layout>
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <i class="fas fa-check text-green-600 text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Verificação Concluída</h2>
        <p class="text-gray-600 text-sm mb-4">{{ $message }}</p>
        <p class="text-gray-500 text-xs">Redirecionando...</p>
    </div>

    <script>
        // Aguarda um momento para garantir que a sessão foi salva
        setTimeout(function() {
            window.location.href = '{{ $redirect_url }}';
        }, 1000);
    </script>
</x-guest-layout>