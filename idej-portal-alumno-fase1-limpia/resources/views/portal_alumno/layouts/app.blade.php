<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f2a5f">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Alumno') | IDEJ</title>

    {{-- Assets aislados del Portal Alumno: no dependen de @vite ni del layout administrativo. --}}
    <link rel="manifest" href="{{ asset('portal-alumno/manifest.json') }}">
    <link rel="icon" href="{{ asset('portal-alumno/icons/icon.svg') }}" type="image/svg+xml">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .portal-bg {
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, .16), transparent 26rem),
                linear-gradient(135deg, #eef4ff 0%, #f8fafc 42%, #ffffff 100%);
        }
        .portal-card { box-shadow: 0 18px 45px rgba(15, 42, 95, .09); }
        .portal-bottom-safe { padding-bottom: 5.75rem; }
        @media (min-width: 768px) { .portal-bottom-safe { padding-bottom: 0; } }
    </style>
</head>
<body class="portal-bg min-h-screen text-slate-800">
@php
    $portalAlumno = auth('portal_alumno')->user();

    $navItems = [
        ['route' => 'portal.alumno.dashboard', 'icon' => 'bx-home-alt-2', 'label' => 'Inicio'],
        ['route' => 'portal.alumno.horario', 'icon' => 'bx-calendar', 'label' => 'Horario'],
        ['route' => 'portal.alumno.calificaciones', 'icon' => 'bx-bar-chart-alt-2', 'label' => 'Calificaciones'],
        ['route' => 'portal.alumno.avisos', 'icon' => 'bx-bell', 'label' => 'Avisos'],
        ['route' => 'portal.alumno.perfil', 'icon' => 'bx-user', 'label' => 'Perfil'],
    ];
@endphp

<div class="min-h-screen md:flex">
    {{-- Menu escritorio: exclusivo para Portal Alumno. --}}
    <aside class="hidden md:flex md:w-72 md:flex-col md:fixed md:inset-y-0 bg-[#0f2a5f] text-white">
        <div class="p-7 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center ring-1 ring-white/20">
                    <i class='bx bxs-graduation text-2xl text-amber-300'></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[.25em] text-blue-100">IDEJ</p>
                    <h1 class="text-lg font-bold leading-tight">Portal Alumno</h1>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs($item['route']) ? 'bg-white text-[#0f2a5f] shadow-lg' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                    <i class='bx {{ $item['icon'] }} text-xl'></i>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <a href="{{ route('portal.alumno.materias') }}"
               class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('portal.alumno.materias') ? 'bg-white text-[#0f2a5f] shadow-lg' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                <i class='bx bx-book-open text-xl'></i>
                Materias
            </a>

            <a href="{{ route('portal.alumno.ubicacion') }}"
               class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('portal.alumno.ubicacion') ? 'bg-white text-[#0f2a5f] shadow-lg' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                <i class='bx bx-map text-xl'></i>
                Ubicacion
            </a>
        </nav>

        <div class="p-4 border-t border-white/10">
            <div class="rounded-2xl bg-white/10 p-4 mb-3">
                <p class="text-sm font-bold">{{ $portalAlumno->nombre_corto ?? 'Alumno' }}</p>
                <p class="text-xs text-blue-100">{{ $portalAlumno->matricula ?? 'Sin matricula' }}</p>
            </div>
            <form method="POST" action="{{ route('portal.alumno.logout') }}">
                @csrf
                <button class="w-full rounded-2xl bg-white/10 px-4 py-3 text-sm font-bold text-white hover:bg-white/20 transition">
                    <i class='bx bx-log-out mr-1'></i> Cerrar sesion
                </button>
            </form>
        </div>
    </aside>

    <main class="portal-bottom-safe flex-1 md:ml-72">
        {{-- Encabezado movil. --}}
        <header class="md:hidden sticky top-0 z-30 bg-white/85 backdrop-blur border-b border-slate-200 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.22em] text-[#0f2a5f]">IDEJ</p>
                    <h1 class="text-lg font-extrabold text-slate-900">@yield('mobile_title', 'Portal Alumno')</h1>
                </div>
                <div class="h-11 w-11 rounded-2xl bg-[#0f2a5f] text-white flex items-center justify-center">
                    <i class='bx bxs-graduation text-xl'></i>
                </div>
            </div>
        </header>

        <section class="px-4 py-6 md:px-10 md:py-8 max-w-7xl mx-auto">
            @yield('content')
        </section>
    </main>
</div>

{{-- Navegacion movil tipo app. --}}
<nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-slate-200 px-2 py-2 shadow-[0_-10px_30px_rgba(15,42,95,.12)]">
    <div class="grid grid-cols-5 gap-1">
        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="flex flex-col items-center justify-center rounded-2xl py-2 text-[11px] font-bold transition {{ request()->routeIs($item['route']) ? 'bg-[#0f2a5f] text-white' : 'text-slate-500' }}">
                <i class='bx {{ $item['icon'] }} text-xl mb-0.5'></i>
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>

<script>
    // Service worker aislado del Portal Alumno.
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('{{ asset('portal-alumno/sw.js') }}').catch(() => {});
        });
    }
</script>

@stack('scripts')
</body>
</html>
