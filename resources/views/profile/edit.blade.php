@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Mi perfil</h2>
            <p class="text-sm text-slate-500">Administra tu información de acceso al sistema interno IDEJ-SYS.</p>
        </div>

        <div class="rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-600">
            <span class="font-semibold text-slate-800">Rol actual:</span>
            {{ $user->rol?->nombre ?? 'Sin rol asignado' }}
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            Tu información de perfil se actualizó correctamente.
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            Tu contraseña se actualizó correctamente.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                <div class="mb-5">
                    <h3 class="text-lg font-semibold text-slate-800">Información personal</h3>
                    <p class="text-sm text-slate-500">Puedes actualizar tu nombre y correo institucional. Tu rol solo puede ser modificado por Admin o Sistemas.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-slate-700">Nombre completo</label>
                        <input
                            id="nombre"
                            name="nombre"
                            type="text"
                            value="{{ old('nombre', $user->nombre) }}"
                            required
                            autocomplete="name"
                            class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('nombre')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Correo electrónico</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="username"
                            class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </a>
                        <button class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-800">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                <div class="mb-5">
                    <h3 class="text-lg font-semibold text-slate-800">Cambiar contraseña</h3>
                    <p class="text-sm text-slate-500">Usa una contraseña fuerte. No compartas tu acceso porque toda acción queda ligada a tu usuario en bitácora.</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="update_password_current_password" class="block text-sm font-semibold text-slate-700">Contraseña actual</label>
                        <input
                            id="update_password_current_password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @foreach ($errors->updatePassword->get('current_password') as $message)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div>
                        <label for="update_password_password" class="block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                        <input
                            id="update_password_password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @foreach ($errors->updatePassword->get('password') as $message)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="block text-sm font-semibold text-slate-700">Confirmar nueva contraseña</label>
                        <input
                            id="update_password_password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800">
                            Actualizar contraseña
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                <h3 class="text-lg font-semibold text-slate-800">Seguridad de cuenta</h3>

                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Estado</dt>
                        <dd class="mt-1">
                            @if($user->activo ?? true)
                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Activo</span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Desactivado</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="font-semibold text-slate-500">Último acceso registrado</dt>
                        <dd class="mt-1 text-slate-700">
                            {{ $user->ultimo_acceso_at?->format('d/m/Y H:i') ?? 'Sin registro previo' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="font-semibold text-slate-500">Última IP</dt>
                        <dd class="mt-1 text-slate-700 break-all">{{ $user->ultimo_login_ip ?? 'Sin registro' }}</dd>
                    </div>

                    <div>
                        <dt class="font-semibold text-slate-500">Contraseña actualizada</dt>
                        <dd class="mt-1 text-slate-700">
                            {{ $user->password_changed_at?->format('d/m/Y H:i') ?? 'Sin registro' }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
                <h3 class="font-semibold">Pendiente para fase de seguridad avanzada</h3>
                <p class="mt-2">Aquí se integrará doble factor, cierre de otras sesiones y revisión de dispositivos activos.</p>
            </section>
        </aside>
    </div>
</div>
@endsection
