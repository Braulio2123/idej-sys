<x-guest-layout>
    <div class="mb-7">
        <span class="idej-badge-info">
            <i class="bx bx-lock-alt"></i>
            Acceso interno
        </span>
        <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Bienvenido a IDEJ-SYS</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Ingresa con las credenciales asignadas para tu área.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Correo institucional" />
            <div class="relative">
                <i class="bx bx-envelope pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <x-text-input id="email"
                              class="pl-11"
                              type="email"
                              name="email"
                              :value="old('email')"
                              required
                              autofocus
                              autocomplete="username"
                              placeholder="usuario@idej.edu.mx" />
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div x-data="{ mostrar: false }">
            <div class="flex items-center justify-between gap-3">
                <x-input-label for="password" value="Contraseña" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-blue-700 transition hover:text-blue-900" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>
            <div class="relative">
                <i class="bx bx-key pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-lg text-slate-400"></i>
                <input id="password"
                       class="idej-input pl-11 pr-11"
                       :type="mostrar ? 'text' : 'password'"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Escribe tu contraseña">
                <button type="button"
                        @click="mostrar = !mostrar"
                        class="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        :aria-label="mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                    <i class="bx text-lg" :class="mostrar ? 'bx-hide' : 'bx-show'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <div class="flex items-start gap-3">
                <i class="bx bx-info-circle mt-0.5 text-lg text-slate-400"></i>
                <p class="text-xs leading-5 text-slate-500">
                    La sesión no permanecerá abierta al cerrar el navegador. En equipos compartidos, finaliza siempre con <strong class="font-semibold text-slate-700">Cerrar sesión</strong>.
                </p>
            </div>
        </div>

        <x-primary-button class="w-full">
            <span>Ingresar al sistema</span>
            <i class="bx bx-right-arrow-alt text-lg"></i>
        </x-primary-button>
    </form>
</x-guest-layout>
