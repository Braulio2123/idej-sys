<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: true, userMenu: false }" class="bg-slate-100">
<head>
    <link rel="icon" type="image/png" href="{{ logoInstitucionalUrl() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', configInstitucional('nombre_corto', 'IDEJ').' - SYS')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .sidebar-scroll::-webkit-scrollbar { width: 6px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.25); border-radius: 999px; }
    </style>
</head>

<body class="min-h-screen flex bg-slate-100">
@php
    use App\Models\Rol;

    $configuracionSistema = configuracionInstitucional();
    $logoSistema = $configuracionSistema->logoUrl();
    $nombreCortoSistema = $configuracionSistema->nombre_corto ?: 'IDEJ';
    $lemaSistema = $configuracionSistema->lema ?: 'Gestión académica y administrativa';

    $usuarioActual = Auth::user();
    $rolClave = $usuarioActual?->rolClave();
    $nombreUsuario = $usuarioActual?->nombre ?? 'Usuario';

    $linkBase = 'group relative flex items-center gap-4 px-4 py-3 text-[14px] rounded-xl font-medium transition-all duration-200';
    $active = 'bg-amber-400 text-slate-900 shadow-lg shadow-amber-500/30';
    $inactive = 'text-slate-100/80 hover:bg-white/10 hover:text-white';

    $puedeVerAlumnos = in_array($rolClave, [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::RRPP, Rol::ACADEMICA, Rol::DIRECCION], true);
    $puedeProspectos = in_array($rolClave, [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::RRPP, Rol::DIRECCION], true);
    $puedeAcademica = in_array($rolClave, [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::SISTEMAS], true);
    $puedeEducacionContinua = in_array($rolClave, [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::SISTEMAS, Rol::DIRECCION], true);
    $puedeSolicitudes = in_array($rolClave, [Rol::ADMIN, Rol::CADMIN, Rol::ACADEMICA, Rol::RECEPCION, Rol::FINANZAS], true);
    $puedeFinanzas = in_array($rolClave, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true);
    $puedeCaja = in_array($rolClave, [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS], true);
    $puedeReportes = in_array($rolClave, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS, Rol::DIRECCION], true);
    $puedeUsuarios = in_array($rolClave, [Rol::ADMIN, Rol::SISTEMAS], true);
    $puedeConfiguracion = in_array($rolClave, [Rol::ADMIN, Rol::SISTEMAS], true);
    $puedeMantenimiento = in_array($rolClave, [Rol::ADMIN, Rol::SISTEMAS], true);
    $puedeBitacora = in_array($rolClave, [Rol::ADMIN, Rol::SISTEMAS, Rol::DIRECCION], true);
@endphp

<aside class="bg-gradient-to-b from-[#1E3A8A] via-[#162860] to-[#0D133A] text-slate-50 border-r border-blue-900/60 transition-all duration-300 ease-in-out flex flex-col shadow-2xl"
       :class="sidebarOpen ? 'w-72' : 'w-24'">

    <div class="h-24 flex items-center justify-between px-5 border-b border-white/10">
        <div class="flex items-center gap-3 overflow-hidden">
            <img src="{{ $logoSistema }}" class="h-12 w-auto flex-shrink-0">
            <div x-show="sidebarOpen" x-transition class="flex flex-col">
                <span class="text-base font-semibold tracking-wide uppercase">{{ $nombreCortoSistema }}</span>
                <span class="text-[12px] text-amber-200/90 leading-tight">{{ \Illuminate\Support\Str::limit($configuracionSistema->nombre_institucion, 32) }}</span>
            </div>
        </div>

        <button @click="sidebarOpen = !sidebarOpen"
                class="flex items-center justify-center h-10 w-10 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white transition">
            <i class='bx text-2xl' :class="sidebarOpen ? 'bx-chevron-left' : 'bx-chevron-right'"></i>
        </button>
    </div>

    <nav class="p-4 pt-5 flex-1 overflow-y-auto sidebar-scroll">
        <a href="{{ route('dashboard') }}" class="{{ $linkBase }} {{ request()->routeIs('dashboard') ? $active : $inactive }}">
            <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10">
                <i class='bx bx-home-alt-2 text-2xl'></i>
            </span>
            <span x-show="sidebarOpen" x-transition>Dashboard</span>
        </a>

        @if($puedeUsuarios)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-amber-200/90">
                Administración
            </p>

            <a href="{{ route('usuarios.index') }}" class="{{ $linkBase }} {{ request()->routeIs('usuarios.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-user-circle text-2xl'></i></span>
                <span x-show="sidebarOpen">Usuarios</span>
            </a>

            @if($puedeConfiguracion)
                <a href="{{ route('configuracion.institucional.edit') }}" class="{{ $linkBase }} {{ request()->routeIs('configuracion.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-cog text-2xl'></i></span>
                    <span x-show="sidebarOpen">Configuración</span>
                </a>
            @endif

            @if($puedeMantenimiento)
                <a href="{{ route('sistema.mantenimiento.index') }}" class="{{ $linkBase }} {{ request()->routeIs('sistema.mantenimiento.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-wrench text-2xl'></i></span>
                    <span x-show="sidebarOpen">Mantenimiento</span>
                </a>
            @endif
        @endif

        @if($puedeVerAlumnos)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-blue-200/90">
                Alumnos y Recepción
            </p>

            <a href="{{ route('alumnos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('alumnos.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-user text-2xl'></i></span>
                <span x-show="sidebarOpen">Alumnos</span>
            </a>

            @if($puedeProspectos)
                <a href="{{ route('prospectos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('prospectos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-user-voice text-2xl'></i></span>
                    <span x-show="sidebarOpen">Prospectos</span>
                </a>
            @endif
        @endif

        @if($puedeAcademica)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-green-200/90">
                Área Académica
            </p>

            <a href="{{ route('ciclos_escolares.index') }}" class="{{ $linkBase }} {{ request()->routeIs('ciclos_escolares.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-calendar text-2xl'></i></span>
                <span x-show="sidebarOpen">Ciclos Escolares</span>
            </a>

            <a href="{{ route('grupos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('grupos.*') || request()->routeIs('academica.grupos.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-group text-2xl'></i></span>
                <span x-show="sidebarOpen">Grupos</span>
            </a>

            <a href="{{ route('materias.index') }}" class="{{ $linkBase }} {{ request()->routeIs('materias.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-book-content text-2xl'></i></span>
                <span x-show="sidebarOpen">Materias</span>
            </a>

            <a href="{{ route('calendarios_academicos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('calendarios_academicos.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-calendar-event text-2xl'></i></span>
                <span x-show="sidebarOpen">Calendarios</span>
            </a>

            <a href="{{ route('dias_no_laborales.index') }}" class="{{ $linkBase }} {{ request()->routeIs('dias_no_laborales.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-calendar-x text-2xl'></i></span>
                <span x-show="sidebarOpen">Días no laborales</span>
            </a>

            @if($puedeEducacionContinua)
                <a href="{{ route('educacion_continua.index') }}" class="{{ $linkBase }} {{ request()->routeIs('educacion_continua.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-extension text-2xl'></i></span>
                    <span x-show="sidebarOpen">Educación Continua</span>
                </a>
            @endif

            <a href="{{ route('programas.index') }}" class="{{ $linkBase }} {{ request()->routeIs('programas.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-book text-2xl'></i></span>
                <span x-show="sidebarOpen">Programas</span>
            </a>

            <a href="{{ route('requisitos_documentales.index') }}" class="{{ $linkBase }} {{ request()->routeIs('requisitos_documentales.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-file text-2xl'></i></span>
                <span x-show="sidebarOpen">Requisitos Documentales</span>
            </a>

            <a href="{{ route('docentes.index') }}" class="{{ $linkBase }} {{ request()->routeIs('docentes.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-chalkboard text-2xl'></i></span>
                <span x-show="sidebarOpen">Docentes</span>
            </a>
        @endif

        @if($puedeSolicitudes)
            <a href="{{ route('solicitudes_pago.index') }}" class="{{ $linkBase }} {{ request()->routeIs('solicitudes_pago.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-money-withdraw text-2xl'></i></span>
                <span x-show="sidebarOpen">Solicitudes de Pago</span>
            </a>
        @endif

        @if($puedeFinanzas || $puedeReportes || $puedeCaja)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-yellow-200/90">
                Finanzas
            </p>


            @if($puedeCaja)
                <a href="{{ route('cortes-caja.index') }}" class="{{ $linkBase }} {{ request()->routeIs('cortes-caja.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-wallet text-2xl'></i></span>
                    <span x-show="sidebarOpen">Cortes de Caja</span>
                </a>
            @endif

            @if($puedeFinanzas)
                <a href="{{ route('conceptos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('conceptos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-coin-stack text-2xl'></i></span>
                    <span x-show="sidebarOpen">Conceptos</span>
                </a>

                <a href="{{ route('becas.index') }}" class="{{ $linkBase }} {{ request()->routeIs('becas.*') || request()->routeIs('alumnos.becas.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-purchase-tag-alt text-2xl'></i></span>
                    <span x-show="sidebarOpen">Becas</span>
                </a>

                <a href="{{ route('cargos.masivo.index') }}" class="{{ $linkBase }} {{ request()->routeIs('cargos.masivo.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-layer-plus text-2xl'></i></span>
                    <span x-show="sidebarOpen">Cargos Masivos</span>
                </a>
            @endif

            @if($puedeReportes)
                <a href="{{ route('reportes.index') }}" class="{{ $linkBase }} {{ request()->routeIs('reportes.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-line-chart text-2xl'></i></span>
                    <span x-show="sidebarOpen">Reportes</span>
                </a>
            @endif
        @endif

        @if($puedeBitacora)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-purple-200/90">
                Auditoría
            </p>

            <a href="{{ route('bitacoras.index') }}" class="{{ $linkBase }} {{ request()->routeIs('bitacoras.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-list-check text-2xl'></i></span>
                <span x-show="sidebarOpen">Bitácoras</span>
            </a>
        @endif
    </nav>

    <div class="border-t border-white/10 px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-amber-400 text-slate-900 flex items-center justify-center font-semibold">
                {{ strtoupper(mb_substr($nombreUsuario, 0, 1)) }}
            </div>

            <div x-show="sidebarOpen" x-transition class="flex-1 min-w-0">
                <p class="text-xs font-semibold truncate">{{ $nombreUsuario }}</p>
                <p class="text-[11px] text-slate-100/70 truncate">{{ $usuarioActual?->rol?->nombre ?? 'Sin rol' }}</p>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="h-10 w-10 bg-white/10 hover:bg-red-500 text-white rounded-xl flex items-center justify-center transition" title="Cerrar sesión">
                    <i class='bx bx-log-out text-xl'></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="flex-1 flex flex-col">
    <header class="h-16 bg-white/80 backdrop-blur border-b border-slate-200 flex items-center justify-between px-6 shadow-sm">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden flex items-center justify-center h-9 w-9 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                <i class='bx bx-menu text-2xl'></i>
            </button>

            <div>
                <h1 class="text-xl md:text-2xl font-semibold text-slate-800">@yield('title', 'Panel de Control')</h1>
                <p class="text-xs text-slate-500 hidden sm:block">{{ $nombreCortoSistema }}-SYS · {{ $lemaSistema }}</p>
            </div>
        </div>

        <div class="relative hidden sm:block" @click.away="userMenu = false">
            <button @click="userMenu = !userMenu" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-sm text-slate-700">
                <span class="hidden md:inline-block max-w-[180px] truncate">{{ $nombreUsuario }}</span>
                <i class='bx bx-chevron-down text-lg'></i>
            </button>

            <div x-show="userMenu" x-transition class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 shadow-lg rounded-xl overflow-hidden text-sm">
                <div class="px-3 py-2 border-b border-slate-100">
                    <p class="font-medium truncate">{{ $nombreUsuario }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $usuarioActual?->rol?->nombre ?? 'Sin rol' }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="w-full px-4 py-2.5 hover:bg-slate-100 flex items-center gap-2 text-[13px]">
                    <i class='bx bx-user text-lg'></i>
                    <span>Mi perfil</span>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="w-full text-left px-4 py-2.5 hover:bg-red-500 hover:text-white flex items-center gap-2 text-[13px]">
                        <i class='bx bx-log-out text-lg'></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="p-4 md:p-6">
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-4 md:p-6">
            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>
