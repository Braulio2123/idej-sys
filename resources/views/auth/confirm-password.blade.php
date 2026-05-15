<x-guest-layout>
    <div class="mb-6">
        <div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-4">
            <i class='bx bx-shield-quarter text-2xl'></i>
        </div>

        <h1 class="text-xl font-bold text-slate-900">Confirmación de seguridad</h1>
        <p class="text-sm text-slate-600 mt-2 leading-relaxed">
            Esta operación modifica información sensible del sistema. Confirma tu contraseña para continuar.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" value="Contraseña actual" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-2">
            <a href="{{ url()->previous() ?: route('dashboard') }}" class="inline-flex justify-center items-center px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50">
                Cancelar
            </a>
            <x-primary-button class="justify-center">
                Confirmar y continuar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
