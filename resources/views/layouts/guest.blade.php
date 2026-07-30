<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ configInstitucional('nombre_corto', 'IDEJ') }}-SYS</title>

    <link rel="icon" type="image/png" href="{{ logoInstitucionalUrl() }}">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950">
    <main class="grid min-h-screen lg:grid-cols-[1.08fr_0.92fr]">
        <section class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(59,130,246,0.34),transparent_28rem),linear-gradient(145deg,#0f2138_0%,#081426_68%,#020617_100%)]"></div>
            <div class="absolute -bottom-48 -right-40 h-[34rem] w-[34rem] rounded-full border border-white/10 bg-blue-500/10 blur-3xl"></div>
            <div class="absolute right-16 top-24 grid grid-cols-3 gap-3 opacity-30">
                @for($i = 0; $i < 18; $i++)
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-300"></span>
                @endfor
            </div>

            <div class="relative z-10 flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/15 bg-white/10 shadow-2xl backdrop-blur">
                    <img src="{{ logoInstitucionalUrl() }}" alt="Logo institucional" class="max-h-10 max-w-10 object-contain">
                </span>
                <div>
                    <p class="text-lg font-semibold tracking-wide text-white">{{ configInstitucional('nombre_corto', 'IDEJ') }}-SYS</p>
                    <p class="text-sm text-slate-300">Plataforma institucional</p>
                </div>
            </div>

            <div class="relative z-10 max-w-2xl py-16">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-300">Gestión académica y administrativa</p>
                <h1 class="mt-5 text-4xl font-bold leading-tight tracking-tight text-white xl:text-6xl">
                    Información clara para tomar mejores decisiones.
                </h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-slate-300 xl:text-lg">
                    Centraliza alumnos, operación académica, cobranza, caja y seguimiento institucional en un entorno seguro y ordenado.
                </p>

                <div class="mt-10 grid max-w-xl gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <i class="bx bx-shield-quarter text-2xl text-blue-300"></i>
                        <p class="mt-3 text-sm font-semibold text-white">Acceso protegido</p>
                        <p class="mt-1 text-xs leading-5 text-slate-400">Permisos por área y trazabilidad.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <i class="bx bx-grid-alt text-2xl text-blue-300"></i>
                        <p class="mt-3 text-sm font-semibold text-white">Trabajo centralizado</p>
                        <p class="mt-1 text-xs leading-5 text-slate-400">Módulos conectados en un solo lugar.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <i class="bx bx-bell text-2xl text-blue-300"></i>
                        <p class="mt-3 text-sm font-semibold text-white">Seguimiento oportuno</p>
                        <p class="mt-1 text-xs leading-5 text-slate-400">Pendientes y movimientos visibles.</p>
                    </div>
                </div>
            </div>

            <p class="relative z-10 text-xs text-slate-500">
                © {{ date('Y') }} {{ configInstitucional('nombre_corto', 'IDEJ') }} · Uso interno institucional
            </p>
        </section>

        <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 py-10 sm:px-8 lg:px-12">
            <div class="absolute inset-x-0 top-0 h-56 bg-gradient-to-b from-blue-50 to-transparent lg:hidden"></div>

            <div class="relative z-10 w-full max-w-md">
                <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 shadow-lg">
                        <img src="{{ logoInstitucionalUrl() }}" alt="Logo institucional" class="max-h-9 max-w-9 object-contain">
                    </span>
                    <div>
                        <p class="font-semibold text-slate-950">{{ configInstitucional('nombre_corto', 'IDEJ') }}-SYS</p>
                        <p class="text-xs text-slate-500">Plataforma institucional</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_28px_70px_-36px_rgba(15,23,42,0.45)] sm:p-9">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs leading-5 text-slate-500">
                    El acceso se registra por seguridad. Usa únicamente tu cuenta institucional.
                </p>
            </div>
        </section>
    </main>

    @include('partials.idempotent-forms')
</body>
</html>
