@extends('layouts.app')

@section('title', 'Dashboard')
@section('content_shell', 'plain')

@section('content')
<div class="space-y-6 sm:space-y-8">
    <x-page-header
        eyebrow="Centro operativo"
        :title="'Hola, '.(Auth::user()?->nombre ?? 'Usuario')"
        :description="$panelRol['descripcion']"
    >
        <x-slot:actions>
            <span class="idej-role-chip">
                <i class="bx bx-briefcase-alt-2"></i>
                {{ $rol }}
            </span>
            <a href="{{ route('notificaciones.index') }}" class="idej-btn-secondary">
                <i class="bx bx-bell text-lg"></i>
                Notificaciones
            </a>
        </x-slot:actions>
    </x-page-header>

    <section class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 px-5 py-6 text-white shadow-[0_24px_65px_-35px_rgba(15,23,42,0.9)] sm:px-7 sm:py-8 lg:px-9">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_85%_10%,rgba(59,130,246,0.32),transparent_22rem),linear-gradient(120deg,rgba(37,99,235,0.12),transparent_52%)]"></div>
        <div class="absolute -bottom-24 right-4 h-56 w-56 rounded-full border border-white/10 bg-blue-500/10 blur-2xl"></div>

        <div class="relative z-10 flex flex-col gap-7 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-blue-300">Panel de {{ $rol }}</p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">{{ $panelRol['titulo'] }}</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Consulta pendientes, indicadores y accesos frecuentes de tu área desde un solo lugar.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($panelRol['acciones'] as $accion)
                    <a href="{{ $accion['route'] }}"
                       class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur transition hover:border-white/30 hover:bg-white/15"
                       title="{{ $accion['hint'] }}">
                        {{ $accion['label'] }}
                        <i class="bx bx-right-arrow-alt text-lg"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @php
        $cardIcons = ['bx-pulse', 'bx-calendar-check', 'bx-user-check', 'bx-line-chart'];
        $cardTones = ['blue', 'emerald', 'amber', 'violet'];
    @endphp

    <section aria-label="Indicadores principales" class="grid idej-grid-auto gap-4">
        @foreach($panelRol['cards'] as $card)
            <x-stat-card
                :label="$card['label']"
                :value="$card['value']"
                :hint="$card['hint'] ?? null"
                :href="$card['route'] ?? null"
                :icon="$cardIcons[$loop->index % count($cardIcons)]"
                :tone="$cardTones[$loop->index % count($cardTones)]"
            />
        @endforeach
    </section>

    <div class="grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
        <x-section-card title="Acciones rápidas" description="Tareas frecuentes disponibles para tu rol.">
            <div class="flex flex-wrap gap-2">
                @foreach($panelRol['acciones'] as $accion)
                    <a href="{{ $accion['route'] }}" class="idej-btn-primary" title="{{ $accion['hint'] }}">
                        {{ $accion['label'] }}
                    </a>
                @endforeach
            </div>
        </x-section-card>

        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm sm:p-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-amber-700 shadow-sm ring-1 ring-amber-200">
                    <i class="bx bx-bulb text-xl"></i>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-amber-950">Prioridades del área</h2>
                    <ul class="mt-2 space-y-2 text-sm leading-5 text-amber-900">
                        @foreach($panelRol['alertas'] as $alerta)
                            <li class="flex items-start gap-2">
                                <i class="bx bx-check-circle mt-0.5 text-base text-amber-700"></i>
                                <span>{{ $alerta }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    </div>

    {{-- ====================== --}}
    {{-- SEGUIMIENTOS OPERATIVOS --}}
    {{-- ====================== --}}
    @can('puede-ver-alumnos')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Seguimientos abiertos</h2>
            <p class="text-4xl font-bold text-purple-700">{{ $seguimientosAbiertos }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Pendientes o en proceso</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Seguimientos vencidos</h2>
            <p class="text-4xl font-bold text-red-600">{{ $seguimientosVencidos }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Requieren atención</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Alumnos con adeudo</h2>
            <p class="text-4xl font-bold text-amber-600">{{ $alumnosConAdeudo }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Para seguimiento financiero</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Docs. pendientes</h2>
            <p class="text-4xl font-bold text-red-600">{{ $documentosPendientes }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Pendientes o rechazados</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Docs. en revisión</h2>
            <p class="text-4xl font-bold text-cyan-700">{{ $documentosRevision }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Para validar expediente</p>
        </div>
    </div>

    <div class="idej-panel">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Próximos seguimientos</h2>
            <span class="text-xs text-slate-500">Siguientes 7 días</span>
        </div>

        @forelse($seguimientosProximos as $seguimiento)
            <div class="py-3 border-b last:border-b-0 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ $seguimiento->asunto }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $seguimiento->alumno->nombre_completo ?? 'Alumno no disponible' }} · {{ $seguimiento->tipo }} · {{ $seguimiento->prioridad }}
                    </p>
                </div>
                <div class="text-sm text-slate-600 md:text-right">
                    <p>{{ optional($seguimiento->fecha_proximo_contacto)->format('d/m/Y H:i') }}</p>
                    @if($seguimiento->alumno)
                        <a href="{{ route('alumnos.show', $seguimiento->alumno) }}" class="text-purple-700 hover:underline font-semibold">Ver expediente</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No hay seguimientos próximos registrados.</p>
        @endforelse
    </div>


    <div class="idej-panel">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Documentos recientes</h2>
            <span class="text-xs text-slate-500">Últimos movimientos documentales</span>
        </div>

        @forelse($documentosRecientes as $documento)
            <div class="py-3 border-b last:border-b-0 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ $documento->tipo_documento }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $documento->alumno->nombre_completo ?? 'Alumno no disponible' }} · {{ $documento->estatus }}
                    </p>
                </div>
                <div class="text-sm text-slate-600 md:text-right">
                    <p>{{ optional($documento->updated_at)->format('d/m/Y H:i') }}</p>
                    @if($documento->alumno)
                        <a href="{{ route('alumnos.documentos.index', $documento->alumno) }}" class="text-cyan-700 hover:underline font-semibold">Ver documentos</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No hay documentos registrados.</p>
        @endforelse
    </div>
    @endcan


    {{-- ====================== --}}
    {{-- PROSPECTOS / RELACIONES PÚBLICAS --}}
    {{-- ====================== --}}
    @can('puede-ver-prospectos')
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Prospectos activos</h2>
            <p class="text-4xl font-bold text-blue-700">{{ $prospectosActivos }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Nuevo, contactado, interesado o en seguimiento</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Prospectos vencidos</h2>
            <p class="text-4xl font-bold text-red-600">{{ $prospectosVencidos }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Con próximo contacto atrasado</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Convertidos este mes</h2>
            <p class="text-4xl font-bold text-green-700">{{ $prospectosInscritosMes }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Prospectos convertidos a alumno</p>
        </div>
    </div>

    <div class="idej-panel">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Próximos prospectos a contactar</h2>
            <a href="{{ route('prospectos.index') }}" class="text-sm text-blue-700 font-semibold hover:underline">Ver prospectos</a>
        </div>

        @forelse($prospectosProximos as $prospecto)
            <div class="py-3 border-b last:border-b-0 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ $prospecto->nombre_completo }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $prospecto->programa->nombre ?? $prospecto->nivel_interes ?? 'Sin programa' }} · {{ $prospecto->medio_contacto ?? 'Sin medio' }} · {{ $prospecto->asesor->nombre ?? 'Sin asesor' }}
                    </p>
                </div>
                <div class="text-sm text-slate-600 md:text-right">
                    <p>{{ optional($prospecto->fecha_proximo_contacto)->format('d/m/Y H:i') }}</p>
                    <a href="{{ route('prospectos.show', $prospecto) }}" class="text-blue-700 hover:underline font-semibold">Ver prospecto</a>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No hay prospectos próximos a contactar.</p>
        @endforelse
    </div>
    @endcan


    {{-- ====================== --}}
    {{-- BECAS INSTITUCIONALES --}}
    {{-- ====================== --}}
    @can('puede-ver-administracion-financiera')
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Becas activas</h2>
            <p class="text-4xl font-bold text-emerald-700">{{ $becasActivas }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Vigentes actualmente</p>
        </div>
        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Becas programadas</h2>
            <p class="text-4xl font-bold text-blue-700">{{ $becasProgramadas }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Inician en fecha futura</p>
        </div>
        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Descuento por becas este mes</h2>
            <p class="text-3xl font-bold text-amber-700">${{ number_format($descuentoBecasMes, 2) }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">Aplicado en cargos generados</p>
        </div>
    </div>

    <div class="idej-panel">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Becas recientes</h2>
            <a href="{{ route('becas.index') }}" class="text-sm text-emerald-700 font-semibold hover:underline">Ver becas</a>
        </div>

        @forelse($becasRecientes as $beca)
            <div class="py-3 border-b last:border-b-0 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ $beca->alumno->nombre_completo ?? 'Alumno no disponible' }} — {{ $beca->porcentaje }}%</p>
                    <p class="text-sm text-slate-500">{{ $beca->tipo }} · {{ $beca->estatus }} · Autorizó: {{ $beca->autorizadoPor->nombre ?? 'No especificado' }}</p>
                </div>
                <div class="text-sm text-slate-600 md:text-right">
                    <p>{{ optional($beca->created_at)->format('d/m/Y H:i') }}</p>
                    @if($beca->alumno)
                        <a href="{{ route('alumnos.becas.index', $beca->alumno) }}" class="text-emerald-700 hover:underline font-semibold">Ver historial</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No hay becas registradas.</p>
        @endforelse
    </div>
    @endcan

    {{-- ====================== --}}
    {{-- CAJA / CORTES --}}
    {{-- ====================== --}}
    @can('puede-operar-caja')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Mi caja</h2>
            @if($cajaAbierta)
                <p class="text-3xl font-bold text-green-700">Abierta</p>
                <p class="mt-2 text-xs leading-5 text-slate-500">Caja #{{ $cajaAbierta->id }} · {{ optional($cajaAbierta->fecha_apertura)->format('d/m/Y H:i') }}</p>
                <a href="{{ route('cortes-caja.show', $cajaAbierta) }}" class="inline-block mt-3 text-sm text-green-700 font-semibold hover:underline">Ver caja</a>
            @else
                <p class="text-3xl font-bold text-red-600">Cerrada</p>
                <p class="mt-2 text-xs leading-5 text-slate-500">Abre caja antes de registrar pagos</p>
                <a href="{{ route('cortes-caja.create') }}" class="inline-block mt-3 text-sm text-red-700 font-semibold hover:underline">Abrir caja</a>
            @endif
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Ingresos de mi caja</h2>
            <p class="text-3xl font-bold text-indigo-700">
                ${{ number_format($ingresosCajaAbierta['total_sistema'] ?? 0, 2) }}
            </p>
            <p class="mt-2 text-xs leading-5 text-slate-500">{{ $ingresosCajaAbierta['cantidad_pagos'] ?? 0 }} pagos registrados</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Ingresos web hoy</h2>
            <p class="text-3xl font-bold text-green-700">${{ number_format($ingresosWebHoy, 2) }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">{{ $pagosWebHoy }} pagos registrados hoy</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Cortes abiertos</h2>
            <p class="text-3xl font-bold text-amber-600">{{ $cortesAbiertos }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">{{ $cortesCerradosHoy }} cortes cerrados hoy</p>
            <a href="{{ route('cortes-caja.index') }}" class="inline-block mt-3 text-sm text-amber-700 font-semibold hover:underline">Ver cortes</a>
        </div>
    </div>
    @endcan

    {{-- ====================== --}}
    {{-- RESUMEN ADMINISTRATIVO / FINANCIERO --}}
    {{-- ====================== --}}
    @can('puede-ver-administracion-financiera')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Total Alumnos</h2>
            <p class="text-4xl font-bold text-indigo-700">{{ $alumnosCount }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">+{{ $alumnosNuevosMes }} este mes</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Total Docentes</h2>
            <p class="text-4xl font-bold text-indigo-700">{{ $docentesCount }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">+{{ $docentesNuevosMes }} este mes</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Solicitudes Pendientes</h2>
            <p class="text-4xl font-bold text-amber-600">{{ $pagosPendientes }}</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Total Pagado Mes</h2>
            <p class="text-3xl font-bold text-green-600">
                ${{ number_format($montoPagadoMes, 2) }}
            </p>
        </div>
    </div>

    {{-- Últimos alumnos --}}
    <div class="idej-panel">
        <h2 class="text-xl font-semibold mb-4">Últimos alumnos registrados</h2>
        <ul>
            @forelse($ultimosAlumnos as $al)
                <li class="py-1 border-b text-gray-700">
                    {{ $al->nombre_completo }} —
                    {{ $al->created_at ? $al->created_at->format('d/m/Y') : '—' }}
                </li>
            @empty
                <p class="text-sm text-slate-500">Sin registros</p>
            @endforelse
        </ul>
    </div>

    {{-- Últimas solicitudes --}}
    <div class="idej-panel">
        <h2 class="text-xl font-semibold mb-4">Últimas solicitudes de pago</h2>
        <ul>
            @forelse($ultimasSolicitudes as $sol)
                <li class="py-1 border-b text-gray-700 flex justify-between">
                    <span>{{ $sol->docente->nombre_completo }}</span>
                    <span class="text-sm text-gray-500">{{ $sol->estatus }}</span>
                </li>
            @empty
                <p class="text-sm text-slate-500">No hay solicitudes</p>
            @endforelse
        </ul>
    </div>
    @endcan


    {{-- ====================== --}}
    {{-- RECEPCIÓN --}}
    {{-- ====================== --}}
    @can('es-recepcion')
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Alumnos con Adeudo</h2>
            <p class="text-4xl font-bold text-red-600">{{ $alumnosConAdeudo }}</p>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Bitácoras Hoy</h2>
            <p class="text-4xl font-bold text-indigo-700">{{ $bitacorasHoy }}</p>
        </div>

    </div>
    @endcan


    {{-- ====================== --}}
    {{-- ACADÉMICA --}}
    {{-- ====================== --}}
    @can('puede-ver-academica')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Grupos registrados</h2>
            <p class="text-4xl font-bold text-indigo-700">{{ $gruposActivos }}</p>
            <a href="{{ route('grupos.index') }}" class="inline-block mt-3 text-sm text-indigo-700 font-semibold hover:underline">Ver grupos</a>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Materias activas</h2>
            <p class="text-4xl font-bold text-green-700">{{ $materiasActivas }}</p>
            <a href="{{ route('materias.index') }}" class="inline-block mt-3 text-sm text-green-700 font-semibold hover:underline">Ver materias</a>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Calendarios operativos</h2>
            <p class="text-4xl font-bold text-blue-700">{{ $calendariosActivos }}</p>
            <a href="{{ route('calendarios_academicos.index') }}" class="inline-block mt-3 text-sm text-blue-700 font-semibold hover:underline">Ver calendarios</a>
        </div>

        <div class="idej-stat-card">
            <h2 class="text-sm font-medium text-slate-500">Sesiones futuras</h2>
            <p class="text-4xl font-bold text-amber-600">{{ $sesionesProgramadas }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-500">{{ $sesionesHoy }} sesión(es) hoy</p>
        </div>
    </div>

    <div class="idej-panel">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">Agenda académica de hoy</h2>
            <a href="{{ route('calendarios_academicos.index') }}" class="text-sm text-blue-700 font-semibold hover:underline">Ver calendarios</a>
        </div>

        @forelse($sesionesHoyLista as $sesion)
            <div class="py-3 border-b last:border-b-0 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ $sesion->calendarioMateria->nombre_materia ?? 'Materia no disponible' }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $sesion->calendarioMateria->calendario->grupo->nombre ?? 'Grupo no disponible' }} · {{ $sesion->calendarioMateria->nombre_docente ?? 'Docente no disponible' }} · {{ $sesion->aula ?? 'Sin aula' }}
                    </p>
                </div>
                <div class="text-sm text-slate-600 md:text-right">
                    <p>{{ $sesion->horario }}</p>
                    <a href="{{ route('calendarios_academicos.show', $sesion->calendarioMateria->calendario) }}" class="text-blue-700 hover:underline font-semibold">Ver calendario</a>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No hay sesiones programadas para hoy.</p>
        @endforelse
    </div>

    <div class="idej-panel">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-semibold tracking-tight text-slate-900">Próximas clases y sesiones</h2>
                <p class="text-sm text-slate-500">Siguientes 14 días. Útil para Académica y Sistemas: cámaras, micrófonos, aulas, ligas y preparación técnica.</p>
            </div>
            <a href="{{ route('calendarios_academicos.index') }}" class="text-sm text-blue-700 font-semibold hover:underline">Ver calendarios</a>
        </div>

        @forelse($sesionesProximasLista as $sesion)
            <div class="py-3 border-b last:border-b-0 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ $sesion->calendarioMateria->nombre_materia ?? 'Materia no disponible' }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $sesion->calendarioMateria->calendario->nombre ?? 'Calendario no disponible' }} ·
                        {{ $sesion->calendarioMateria->calendario->grupo->nombre ?? 'Grupo no disponible' }} ·
                        {{ $sesion->calendarioMateria->nombre_docente ?? 'Docente no disponible' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">{{ $sesion->aula ?? 'Sin aula/liga' }} · {{ $sesion->modalidad }}</p>
                </div>
                <div class="text-sm text-slate-600 md:text-right">
                    <p class="font-semibold">{{ $sesion->fecha->format('d/m/Y') }} · {{ $sesion->dia_semana }}</p>
                    <p>{{ $sesion->horario }}</p>
                    <a href="{{ route('calendarios_academicos.show', $sesion->calendarioMateria->calendario) }}" class="text-blue-700 hover:underline font-semibold">Ver calendario</a>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No hay sesiones próximas en los siguientes 14 días.</p>
        @endforelse
    </div>


    <div class="idej-panel">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-semibold tracking-tight text-slate-900">Próximos cursos especiales / Educación Continua</h2>
                <p class="text-sm text-slate-500">Siguientes 14 días. Incluye MASC, oratoria, masterclass, talleres y cursos por horas.</p>
            </div>
            @if(Route::has('educacion_continua.index'))
                <a href="{{ route('educacion_continua.index') }}" class="text-sm text-indigo-700 font-semibold hover:underline">Ver educación continua</a>
            @endif
        </div>

        @forelse($sesionesEducacionProximas as $sesion)
            <div class="py-3 border-b last:border-b-0 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <p class="font-semibold text-slate-800">{{ $sesion->curso->nombre ?? 'Curso no disponible' }}</p>
                    <p class="text-sm text-slate-500">
                        {{ $sesion->curso->tipo ?? 'Curso' }} · {{ $sesion->expositor }} · {{ $sesion->aula_liga ?? 'Sin aula/liga' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $sesion->modalidad }} · {{ $sesion->requiere_equipo ? 'Equipo: '.implode(', ', $sesion->equipo_requerido ?? []) : 'Sin equipo especial' }}
                    </p>
                </div>
                <div class="text-sm text-slate-600 md:text-right">
                    <p class="font-semibold">{{ $sesion->fecha->format('d/m/Y') }}</p>
                    <p>{{ $sesion->horario }} · {{ number_format($sesion->duracion_horas, 2) }}h</p>
                    <a href="{{ route('educacion_continua.show', $sesion->curso) }}" class="text-indigo-700 hover:underline font-semibold">Ver curso</a>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">No hay sesiones de educación continua próximas en los siguientes 14 días.</p>
        @endforelse
    </div>
    @endcan

</div>
@endsection
