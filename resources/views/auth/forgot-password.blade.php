<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-bold text-slate-900">Recuperar contraseña</h1>
        <p class="text-sm text-slate-600 mt-2 leading-relaxed">
            Captura el correo de tu usuario interno. Si el correo existe y el servicio de correo está configurado, recibirás un enlace para crear una nueva contraseña.
        </p>
        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Si no recibes el correo, solicita a Sistemas o Administración que restablezca tu contraseña desde el módulo de Usuarios.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Correo de usuario interno" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-3 mt-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Volver al login</a>
            <x-primary-button>
                Enviar enlace
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
