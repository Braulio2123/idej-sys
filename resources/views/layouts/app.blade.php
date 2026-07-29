<!DOCTYPE html>
<html lang="es" x-data="{
    sidebarOpen: JSON.parse(localStorage.getItem('idej_sidebar_open') ?? (window.innerWidth >= 768 ? 'true' : 'false')),
    userMenu: false,
    notificacionesMenu: false,
    toggleSidebar() {
        this.sidebarOpen = ! this.sidebarOpen;
        localStorage.setItem('idej_sidebar_open', JSON.stringify(this.sidebarOpen));
    }
}" class="bg-slate-100">
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
        .idej-table-responsive { width: 100%; max-width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .idej-table-responsive > table { min-width: 760px; }
        @keyframes idejNotificationPulse { 0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220,38,38,.45); } 70% { transform: scale(1.08); box-shadow: 0 0 0 10px rgba(220,38,38,0); } 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220,38,38,0); } }
        .idej-notification-pulse { animation: idejNotificationPulse .9s ease-out 1; }
    </style>
</head>

<body class="min-h-screen flex bg-slate-100 overflow-x-hidden">
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

    $puedeVerAlumnos = usuarioTienePermiso('alumnos.ver');
    $puedeProspectos = usuarioTienePermiso('prospectos.ver');
    $puedeOfertaAcademica = usuarioTienePermiso('oferta_academica.ver');
    $puedeAcademica = usuarioTienePermiso('academica.ver');
    $puedeCalendarios = usuarioTienePermiso('calendarios.ver');
    $puedeHorarios = usuarioTienePermiso('horarios.ver');
    $puedeRequisitosDocumentales = usuarioTienePermiso('requisitos_documentales.gestionar');
    $puedeEducacionContinua = usuarioTienePermiso('educacion_continua.ver');
    $puedeSolicitudes = usuarioTienePermiso('solicitudes_pago.ver');
    $puedeAdministracionFinanciera = usuarioTienePermiso('conceptos.gestionar');
    $puedeCaja = usuarioTienePermiso('caja.ver');
    $puedeBecas = usuarioTienePermiso('becas.ver');
    $puedeReportes = usuarioTienePermiso('reportes.ver');
    $puedeReporteEjecutivo = usuarioTienePermiso('reportes.ejecutivos');
    $puedeUsuarios = usuarioTienePermiso('usuarios.ver');
    $puedeConfiguracion = usuarioTienePermiso('configuracion.editar');
    $puedePanelPermisos = usuarioTienePermiso('seguridad.permisos.ver');
    $puedeMantenimiento = usuarioTienePermiso('mantenimiento.ver');
    $puedeAdministracion = $puedeUsuarios || $puedeConfiguracion || $puedePanelPermisos || $puedeMantenimiento;
    $puedeBitacora = in_array($rolClave, [Rol::ADMIN, Rol::SISTEMAS, Rol::DIRECCION], true);
    $puedeAgendaOperativa = in_array($rolClave, [Rol::ADMIN, Rol::SISTEMAS, Rol::ACADEMICA, Rol::CADMIN, Rol::DIRECCION, Rol::RECEPCION], true);
    $puedeCentroControlOperativo = usuarioTienePermiso('centro_control.ver');
    $resumenNotificacionesInternas = resumenNotificacionesInternas($usuarioActual);
    $notificacionesInternasRecientes = notificacionesInternasRecientes($usuarioActual, 5);
@endphp

<aside class="fixed inset-y-0 left-0 z-40 h-screen bg-gradient-to-b from-[#1E3A8A] via-[#162860] to-[#0D133A] text-slate-50 border-r border-blue-900/60 transition-all duration-300 ease-in-out flex flex-col shadow-2xl md:sticky md:top-0 md:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0 w-72' : '-translate-x-full w-72 md:translate-x-0 md:w-24'">

    <div class="h-24 flex items-center justify-between px-5 border-b border-white/10">
        <div class="flex items-center gap-3 overflow-hidden">
            <img src="{{ $logoSistema }}" class="h-12 w-auto flex-shrink-0">
            <div x-show="sidebarOpen" x-transition class="flex flex-col">
                <span class="text-base font-semibold tracking-wide uppercase">{{ $nombreCortoSistema }}</span>
                <span class="text-[12px] text-amber-200/90 leading-tight">{{ \Illuminate\Support\Str::limit($configuracionSistema->nombre_institucion, 32) }}</span>
            </div>
        </div>

        <button @click="toggleSidebar()"
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


        @if($puedeAgendaOperativa)
            <a href="{{ route('agenda-operativa.index') }}" class="{{ $linkBase }} {{ request()->routeIs('agenda-operativa.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-calendar-star text-2xl'></i></span>
                <span x-show="sidebarOpen">Agenda Operativa</span>
            </a>
        @endif

        @if($puedeCentroControlOperativo)
            <a href="{{ route('centro-control.index') }}" class="{{ $linkBase }} {{ request()->routeIs('centro-control.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-radar text-2xl'></i></span>
                <span x-show="sidebarOpen">Centro de Control</span>
            </a>
        @endif

        @if($puedeAdministracion)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-amber-200/90">
                Administración
            </p>

            @if($puedeUsuarios)
                <a href="{{ route('usuarios.index') }}" class="{{ $linkBase }} {{ request()->routeIs('usuarios.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-user-circle text-2xl'></i></span>
                    <span x-show="sidebarOpen">Usuarios</span>
                </a>
            @endif

            @if($puedeConfiguracion)
                <a href="{{ route('configuracion.institucional.edit') }}" class="{{ $linkBase }} {{ request()->routeIs('configuracion.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-cog text-2xl'></i></span>
                    <span x-show="sidebarOpen">Configuración</span>
                </a>
            @endif

            @if($puedePanelPermisos)
                <a href="{{ route('seguridad.permisos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('seguridad.permisos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-shield-quarter text-2xl'></i></span>
                    <span x-show="sidebarOpen">Permisos</span>
                </a>
            @endif

            @if($puedeMantenimiento)
                <a href="{{ route('sistema.mantenimiento.index') }}" class="{{ $linkBase }} {{ request()->routeIs('sistema.mantenimiento.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-wrench text-2xl'></i></span>
                    <span x-show="sidebarOpen">Mantenimiento</span>
                </a>
            @endif
        @endif

        @if($puedeVerAlumnos || $puedeProspectos)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-blue-200/90">
                Atención y seguimiento
            </p>

            @if($puedeVerAlumnos)
                <a href="{{ route('alumnos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('alumnos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-user text-2xl'></i></span>
                    <span x-show="sidebarOpen">Alumnos</span>
                </a>
            @endif

            @if($puedeProspectos)
                <a href="{{ route('prospectos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('prospectos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-user-voice text-2xl'></i></span>
                    <span x-show="sidebarOpen">Prospectos</span>
                </a>
            @endif
        @endif

        @if($puedeOfertaAcademica || $puedeAcademica || $puedeCalendarios || $puedeHorarios || $puedeRequisitosDocumentales || $puedeEducacionContinua)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-green-200/90">
                Área Académica
            </p>

            @if($puedeOfertaAcademica)
                <a href="{{ route('ciclos_escolares.index') }}" class="{{ $linkBase }} {{ request()->routeIs('ciclos_escolares.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-calendar text-2xl'></i></span>
                    <span x-show="sidebarOpen">Ciclos Escolares</span>
                </a>
                <a href="{{ route('grupos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('grupos.*') || request()->routeIs('academica.grupos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-group text-2xl'></i></span>
                    <span x-show="sidebarOpen">Grupos</span>
                </a>
                <a href="{{ route('programas.index') }}" class="{{ $linkBase }} {{ request()->routeIs('programas.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-book text-2xl'></i></span>
                    <span x-show="sidebarOpen">Programas</span>
                </a>
            @endif

            @if($puedeAcademica)
                <a href="{{ route('materias.index') }}" class="{{ $linkBase }} {{ request()->routeIs('materias.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-book-content text-2xl'></i></span>
                    <span x-show="sidebarOpen">Materias</span>
                </a>
                <a href="{{ route('dias_no_laborales.index') }}" class="{{ $linkBase }} {{ request()->routeIs('dias_no_laborales.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-calendar-x text-2xl'></i></span>
                    <span x-show="sidebarOpen">Días no laborales</span>
                </a>
                <a href="{{ route('docentes.index') }}" class="{{ $linkBase }} {{ request()->routeIs('docentes.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-chalkboard text-2xl'></i></span>
                    <span x-show="sidebarOpen">Docentes</span>
                </a>
            @endif

            @if($puedeCalendarios)
                <a href="{{ route('calendarios_academicos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('calendarios_academicos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-calendar-event text-2xl'></i></span>
                    <span x-show="sidebarOpen">Calendarios</span>
                </a>
            @endif

            @if($puedeHorarios)
                <a href="{{ route('horarios_academicos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('horarios_academicos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-time-five text-2xl'></i></span>
                    <span x-show="sidebarOpen">Horarios</span>
                </a>
            @endif

            @if($puedeRequisitosDocumentales)
                <a href="{{ route('requisitos_documentales.index') }}" class="{{ $linkBase }} {{ request()->routeIs('requisitos_documentales.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-file text-2xl'></i></span>
                    <span x-show="sidebarOpen">Requisitos Documentales</span>
                </a>
            @endif

            @if($puedeEducacionContinua)
                <a href="{{ route('educacion_continua.index') }}" class="{{ $linkBase }} {{ request()->routeIs('educacion_continua.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-extension text-2xl'></i></span>
                    <span x-show="sidebarOpen">Educación Continua</span>
                </a>
            @endif
        @endif

        @if($puedeSolicitudes)
            <a href="{{ route('solicitudes_pago.index') }}" class="{{ $linkBase }} {{ request()->routeIs('solicitudes_pago.*') ? $active : $inactive }}">
                <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-money-withdraw text-2xl'></i></span>
                <span x-show="sidebarOpen">Solicitudes de Pago</span>
            </a>
        @endif

        @if($puedeAdministracionFinanciera || $puedeBecas || $puedeReportes || $puedeReporteEjecutivo || $puedeCaja)
            <p x-show="sidebarOpen" class="mt-8 mb-3 px-4 text-[11px] font-semibold uppercase tracking-[0.25em] text-yellow-200/90">
                Administración financiera
            </p>


            @if($puedeCaja)
                <a href="{{ route('cortes-caja.index') }}" class="{{ $linkBase }} {{ request()->routeIs('cortes-caja.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-wallet text-2xl'></i></span>
                    <span x-show="sidebarOpen">Cortes de Caja</span>
                </a>
            @endif

            @if($puedeAdministracionFinanciera)
                <a href="{{ route('conceptos.index') }}" class="{{ $linkBase }} {{ request()->routeIs('conceptos.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-coin-stack text-2xl'></i></span>
                    <span x-show="sidebarOpen">Conceptos</span>
                </a>

                <a href="{{ route('cargos.masivo.index') }}" class="{{ $linkBase }} {{ request()->routeIs('cargos.masivo.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-layer-plus text-2xl'></i></span>
                    <span x-show="sidebarOpen">Cargos Masivos</span>
                </a>

                <a href="{{ route('cargos.recurrentes.index') }}" class="{{ $linkBase }} {{ request()->routeIs('cargos.recurrentes.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-refresh text-2xl'></i></span>
                    <span x-show="sidebarOpen">Cargos recurrentes</span>
                </a>

            @endif

            @if($puedeBecas)
                <a href="{{ route('becas.index') }}" class="{{ $linkBase }} {{ request()->routeIs('becas.*') || request()->routeIs('alumnos.becas.*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-purchase-tag-alt text-2xl'></i></span>
                    <span x-show="sidebarOpen">Becas</span>
                </a>
            @endif

            @if($puedeReportes)
                <a href="{{ route('reportes.index') }}" class="{{ $linkBase }} {{ request()->routeIs('reportes.index') || request()->routeIs('reportes.export-*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-line-chart text-2xl'></i></span>
                    <span x-show="sidebarOpen">Reportes</span>
                </a>
            @endif

            @if($puedeReporteEjecutivo)
                <a href="{{ route('reportes.ejecutivo') }}" class="{{ $linkBase }} {{ request()->routeIs('reportes.ejecutivo*') ? $active : $inactive }}">
                    <span class="flex items-center justify-center h-10 w-10 rounded-lg bg-white/10"><i class='bx bx-bar-chart-alt-2 text-2xl'></i></span>
                    <span x-show="sidebarOpen">Reporte Ejecutivo</span>
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

            <a href="{{ route('profile.edit') }}" x-show="sidebarOpen" x-transition class="flex-1 min-w-0 rounded-lg px-2 py-1 hover:bg-white/10 transition" title="Mi perfil">
                <p class="text-xs font-semibold truncate">{{ $nombreUsuario }}</p>
                <p class="text-[11px] text-slate-100/70 truncate">{{ $usuarioActual?->rol?->nombre ?? 'Sin rol' }}</p>
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="h-10 w-10 bg-white/10 hover:bg-red-500 text-white rounded-xl flex items-center justify-center transition" title="Cerrar sesión">
                    <i class='bx bx-log-out text-xl'></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-slate-900/50 md:hidden" @click="toggleSidebar()"></div>

<div class="flex-1 flex flex-col min-w-0">
    <header class="h-16 bg-white/80 backdrop-blur border-b border-slate-200 flex items-center justify-between gap-3 px-3 sm:px-6 shadow-sm min-w-0">
        <div class="flex items-center gap-3 min-w-0">
            <button @click="toggleSidebar()" class="md:hidden flex items-center justify-center h-9 w-9 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                <i class='bx bx-menu text-2xl'></i>
            </button>

            <div class="min-w-0">
                <h1 class="truncate text-lg md:text-2xl font-semibold text-slate-800">@yield('title', 'Panel de Control')</h1>
                <p class="text-xs text-slate-500 hidden sm:block">{{ $nombreCortoSistema }}-SYS · {{ $lemaSistema }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            <div class="relative" @click.away="notificacionesMenu = false">
                <button @click="notificacionesMenu = !notificacionesMenu" class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200" title="Notificaciones internas">
                    <i class='bx bx-bell text-xl'></i>
                    <span id="notificaciones-pendientes-badge" class="absolute -right-1 -top-1 min-w-[20px] rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white {{ ($resumenNotificacionesInternas['pendientes'] ?? 0) > 0 ? '' : 'hidden' }}">
                        {{ ($resumenNotificacionesInternas['pendientes'] ?? 0) > 99 ? '99+' : ($resumenNotificacionesInternas['pendientes'] ?? 0) }}
                    </span>
                </button>

                <div x-show="notificacionesMenu" x-transition class="absolute right-0 z-30 mt-2 w-[calc(100vw-2rem)] sm:w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white text-sm shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <div>
                            <p class="font-semibold text-slate-800">Notificaciones</p>
                            <p class="text-xs text-slate-500"><span id="notificaciones-pendientes-text">{{ $resumenNotificacionesInternas['pendientes'] ?? 0 }}</span> pendientes</p>
                        </div>
                        <a href="{{ route('notificaciones.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Ver todas</a>
                    </div>

                    <div id="notificaciones-recientes-lista" class="max-h-80 overflow-y-auto">
                        @forelse($notificacionesInternasRecientes as $notificacionInterna)
                            <a href="{{ $notificacionInterna->urlSegura() }}" class="block border-b border-slate-100 px-4 py-3 hover:bg-slate-50">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 h-2.5 w-2.5 flex-shrink-0 rounded-full {{ in_array($notificacionInterna->severidad, ['critica', 'alta'], true) ? 'bg-red-500' : 'bg-amber-400' }}"></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800">{{ $notificacionInterna->titulo }}</p>
                                        <p class="line-clamp-2 text-xs text-slate-500">{{ $notificacionInterna->mensaje }}</p>
                                        <p class="mt-1 text-[11px] text-slate-400">{{ $notificacionInterna->created_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-6 text-center text-sm text-slate-500">
                                <i class='bx bx-bell-off mb-2 block text-3xl text-slate-300'></i>
                                Sin notificaciones pendientes.
                            </div>
                        @endforelse
                    </div>

                    @if(($resumenNotificacionesInternas['pendientes'] ?? 0) > 0)
                        <form action="{{ route('notificaciones.leer-todas') }}" method="POST" class="border-t border-slate-100 px-4 py-3">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full rounded-xl bg-slate-800 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-900">
                                Marcar todas como leídas
                            </button>
                        </form>
                    @endif
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
        </div>
    </header>

    <main class="min-w-0 overflow-x-hidden p-3 md:p-6">
        @php
            $statusGlobal = session('info') ?? session('status');
            $statusTecnicosOcultos = ['profile-updated', 'password-updated', 'verification-link-sent'];

            if (in_array($statusGlobal, $statusTecnicosOcultos, true)) {
                $statusGlobal = null;
            }
        @endphp

        @if(session('success') || session('error') || $statusGlobal)
            <div class="mb-4 space-y-2">
                @if(session('success'))
                    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @if($statusGlobal)
                    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-800">
                        {{ $statusGlobal }}
                    </div>
                @endif
            </div>
        @endif

        <div class="w-full max-w-full overflow-hidden bg-white rounded-2xl shadow-md border border-slate-100 p-3 md:p-6">
            @yield('content')
        </div>
    </main>
</div>

@include('partials.idempotent-forms')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('main table').forEach(function (table) {
            if (table.closest('.idej-table-responsive') || table.closest('.overflow-x-auto') || table.closest('[data-no-responsive-table]')) {
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'idej-table-responsive rounded-xl border border-slate-200';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
            table.classList.add('min-w-full');
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const badge = document.getElementById('notificaciones-pendientes-badge');
        const text = document.getElementById('notificaciones-pendientes-text');
        const list = document.getElementById('notificaciones-recientes-lista');
        const endpoint = @json(route('notificaciones.resumen-json'));
        const intervalo = Math.max(3000, Number(@json(config('idej_recordatorios.notificaciones.polling_milisegundos', 5000))) || 5000);
        let ultimaNotificacionId = 0;
        let primeraCarga = true;
        let solicitudEnCurso = false;

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, function (char) {
                return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'})[char];
            });
        }

        function mostrarToastNotificacion(item) {
            if (! item) return;
            const toast = document.createElement('a');
            toast.href = item.url || endpoint;
            toast.className = 'fixed right-4 top-4 z-[100] w-[min(92vw,380px)] rounded-2xl border border-blue-200 bg-white p-4 shadow-2xl transition';
            toast.innerHTML = `<div class="flex items-start gap-3">
                <span class="mt-1 h-3 w-3 flex-shrink-0 rounded-full bg-blue-600"></span>
                <div class="min-w-0"><p class="font-bold text-slate-800">${escapeHtml(item.titulo)}</p>
                <p class="mt-1 text-sm text-slate-600">${escapeHtml(item.mensaje)}</p>
                <p class="mt-2 text-xs font-semibold text-blue-700">Abrir movimiento</p></div></div>`;
            document.body.appendChild(toast);
            window.setTimeout(() => toast.remove(), 8000);
        }

        function renderNotificaciones(items) {
            if (! list) return;
            if (! items || items.length === 0) {
                list.innerHTML = `<div class="px-4 py-6 text-center text-sm text-slate-500">
                    <i class='bx bx-bell-off mb-2 block text-3xl text-slate-300'></i>
                    Sin notificaciones pendientes.
                </div>`;
                return;
            }

            list.innerHTML = items.map(function (item) {
                const punto = ['critica', 'alta'].includes(item.severidad) ? 'bg-red-500' : 'bg-amber-400';
                return `<a href="${escapeHtml(item.url)}" class="block border-b border-slate-100 px-4 py-3 hover:bg-slate-50">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 flex-shrink-0 rounded-full ${punto}"></span>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-800">${escapeHtml(item.titulo)}</p>
                            <p class="line-clamp-2 text-xs text-slate-500">${escapeHtml(item.mensaje)}</p>
                            <p class="mt-1 text-[11px] text-slate-400">${escapeHtml(item.fecha)}</p>
                        </div>
                    </div>
                </a>`;
            }).join('');
        }

        function actualizarNotificaciones() {
            if (solicitudEnCurso || document.visibilityState === 'hidden') return;
            solicitudEnCurso = true;

            fetch(endpoint, {
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.ok ? response.json() : null)
                .then(data => {
                    if (! data) return;
                    const pendientes = Number(data.resumen?.pendientes ?? 0);
                    const nuevaUltimaId = Number(data.ultima_id ?? 0);

                    if (text) text.textContent = pendientes;
                    if (badge) {
                        badge.textContent = pendientes > 99 ? '99+' : pendientes;
                        badge.classList.toggle('hidden', pendientes <= 0);

                        if (! primeraCarga && nuevaUltimaId > ultimaNotificacionId) {
                            badge.classList.remove('idej-notification-pulse');
                            void badge.offsetWidth;
                            badge.classList.add('idej-notification-pulse');
                            mostrarToastNotificacion((data.recientes ?? [])[0]);
                        }
                    }

                    renderNotificaciones(data.recientes ?? []);
                    ultimaNotificacionId = Math.max(ultimaNotificacionId, nuevaUltimaId);
                    primeraCarga = false;
                })
                .catch(() => {})
                .finally(() => {
                    solicitudEnCurso = false;
                });
        }

        actualizarNotificaciones();
        window.setInterval(actualizarNotificaciones, intervalo);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                actualizarNotificaciones();
            }
        });
    });
</script>

@stack('scripts')
</body>
</html>
