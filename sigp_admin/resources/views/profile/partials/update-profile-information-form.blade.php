<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Avatar Upload -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Foto do Perfil</label>
            <div class="flex items-center space-x-4">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                         alt="Profile" class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                @else
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center border-2 border-gray-200">
                        <span class="text-blue-600 text-xl font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden" onchange="previewImage(this)">
                    <label for="avatar" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium cursor-pointer transition duration-200">
                        <i class="fas fa-camera mr-2"></i>Alterar Foto
                    </label>
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG até 2MB</p>
                </div>
            </div>
        </div>

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
            <input id="name" name="name" type="text" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('name') border-red-500 @enderror" 
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input id="email" name="email" type="email" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('email') border-red-500 @enderror" 
                   value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                        <p class="text-sm text-yellow-800">
                            Seu email não foi verificado.
                            <button form="send-verification" class="underline text-yellow-600 hover:text-yellow-800 font-medium">
                                Clique aqui para reenviar o email de verificação.
                            </button>
                        </p>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm text-green-600 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            Um novo link de verificação foi enviado para seu email.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 flex items-center">
                <i class="fas fa-save mr-2"></i>
                Salvar Alterações
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                   class="text-sm text-green-600 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Perfil atualizado com sucesso!
                </p>
            @endif
        </div>
    </form>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = input.closest('form').querySelector('img, .w-16.h-16.rounded-full');
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // Replace the div with an img
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-16 h-16 rounded-full object-cover border-2 border-gray-200';
                        img.alt = 'Profile';
                        preview.parentNode.replaceChild(img, preview);
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</section>
