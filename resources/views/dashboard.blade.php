@extends('layouts.app')

@section('content')
<div class="space-y-10">

    <h1 class="text-3xl font-bold text-gray-800">
        Dashboard — {{ $rol }}
    </h1>


    {{-- ====================== --}}
    {{-- SEGUIMIENTOS OPERATIVOS --}}
    {{-- ====================== --}}
    @can('puede-ver-alumnos')
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Seguimientos abiertos</h2>
            <p class="text-4xl font-bold text-purple-700">{{ $seguimientosAbiertos }}</p>
            <p class="text-xs text-gray-400 mt-2">Pendientes o en proceso</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Seguimientos vencidos</h2>
            <p class="text-4xl font-bold text-red-600">{{ $seguimientosVencidos }}</p>
            <p class="text-xs text-gray-400 mt-2">Requieren atención</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Alumnos con adeudo</h2>
            <p class="text-4xl font-bold text-amber-600">{{ $alumnosConAdeudo }}</p>
            <p class="text-xs text-gray-400 mt-2">Para seguimiento financiero</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Docs. pendientes</h2>
            <p class="text-4xl font-bold text-red-600">{{ $documentosPendientes }}</p>
            <p class="text-xs text-gray-400 mt-2">Pendientes o rechazados</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Docs. en revisión</h2>
            <p class="text-4xl font-bold text-cyan-700">{{ $documentosRevision }}</p>
            <p class="text-xs text-gray-400 mt-2">Para validar expediente</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow border border-slate-100">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-xl font-semibold">Próximos seguimientos</h2>
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
            <p class="text-gray-500">No hay seguimientos próximos registrados.</p>
        @endforelse
    </div>


    <div class="bg-white p-6 rounded-xl shadow border border-slate-100">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-xl font-semibold">Documentos recientes</h2>
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
            <p class="text-gray-500">No hay documentos registrados.</p>
        @endforelse
    </div>
    @endcan


    {{-- ====================== --}}
    {{-- PROSPECTOS / RELACIONES PÚBLICAS --}}
    {{-- ====================== --}}
    @can('puede-ver-prospectos')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Prospectos activos</h2>
            <p class="text-4xl font-bold text-blue-700">{{ $prospectosActivos }}</p>
            <p class="text-xs text-gray-400 mt-2">Nuevo, contactado, interesado o en seguimiento</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Prospectos vencidos</h2>
            <p class="text-4xl font-bold text-red-600">{{ $prospectosVencidos }}</p>
            <p class="text-xs text-gray-400 mt-2">Con próximo contacto atrasado</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Convertidos este mes</h2>
            <p class="text-4xl font-bold text-green-700">{{ $prospectosInscritosMes }}</p>
            <p class="text-xs text-gray-400 mt-2">Prospectos convertidos a alumno</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow border border-slate-100">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-xl font-semibold">Próximos prospectos a contactar</h2>
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
            <p class="text-gray-500">No hay prospectos próximos a contactar.</p>
        @endforelse
    </div>
    @endcan


    {{-- ====================== --}}
    {{-- BECAS INSTITUCIONALES --}}
    {{-- ====================== --}}
    @can('puede-ver-finanzas')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Becas activas</h2>
            <p class="text-4xl font-bold text-emerald-700">{{ $becasActivas }}</p>
            <p class="text-xs text-gray-400 mt-2">Vigentes actualmente</p>
        </div>
        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Becas programadas</h2>
            <p class="text-4xl font-bold text-blue-700">{{ $becasProgramadas }}</p>
            <p class="text-xs text-gray-400 mt-2">Inician en fecha futura</p>
        </div>
        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Descuento por becas este mes</h2>
            <p class="text-3xl font-bold text-amber-700">${{ number_format($descuentoBecasMes, 2) }}</p>
            <p class="text-xs text-gray-400 mt-2">Aplicado en cargos generados</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow border border-slate-100">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-xl font-semibold">Becas recientes</h2>
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
            <p class="text-gray-500">No hay becas registradas.</p>
        @endforelse
    </div>
    @endcan

    {{-- ====================== --}}
    {{-- CAJA / CORTES --}}
    {{-- ====================== --}}
    @can('puede-operar-caja')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Mi caja</h2>
            @if($cajaAbierta)
                <p class="text-3xl font-bold text-green-700">Abierta</p>
                <p class="text-xs text-gray-400 mt-2">Caja #{{ $cajaAbierta->id }} · {{ optional($cajaAbierta->fecha_apertura)->format('d/m/Y H:i') }}</p>
                <a href="{{ route('cortes-caja.show', $cajaAbierta) }}" class="inline-block mt-3 text-sm text-green-700 font-semibold hover:underline">Ver caja</a>
            @else
                <p class="text-3xl font-bold text-red-600">Cerrada</p>
                <p class="text-xs text-gray-400 mt-2">Abre caja antes de registrar pagos</p>
                <a href="{{ route('cortes-caja.create') }}" class="inline-block mt-3 text-sm text-red-700 font-semibold hover:underline">Abrir caja</a>
            @endif
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Ingresos de mi caja</h2>
            <p class="text-3xl font-bold text-indigo-700">
                ${{ number_format($ingresosCajaAbierta['total_sistema'] ?? 0, 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-2">{{ $ingresosCajaAbierta['cantidad_pagos'] ?? 0 }} pagos registrados</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Ingresos web hoy</h2>
            <p class="text-3xl font-bold text-green-700">${{ number_format($ingresosWebHoy, 2) }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ $pagosWebHoy }} pagos registrados hoy</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Cortes abiertos</h2>
            <p class="text-3xl font-bold text-amber-600">{{ $cortesAbiertos }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ $cortesCerradosHoy }} cortes cerrados hoy</p>
            <a href="{{ route('cortes-caja.index') }}" class="inline-block mt-3 text-sm text-amber-700 font-semibold hover:underline">Ver cortes</a>
        </div>
    </div>
    @endcan

    {{-- ====================== --}}
    {{-- DESCARGAR APK --}}
    {{-- ====================== --}}
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-3">Descargar IDEJ Mobile</h2>

        <a href="{{ asset('apks/IDEJMobile-v1.apk') }}"
           download
           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg shadow hover:bg-indigo-700 transition">

            <svg xmlns="http://www.w3.org/2000/svg" 
                 fill="none" 
                 viewBox="0 0 24 24" 
                 stroke-width="1.5" 
                 stroke="currentColor" 
                 class="w-6 h-6 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" 
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12l4.5 4.5m0 0L16.5 12m-4.5 4.5V3" />
            </svg>

            Descargar APK
        </a>

        <p class="text-sm text-gray-500 mt-2">
            Última actualización: {{ now()->format('d/m/Y') }}
        </p>
    </div>


    {{-- ====================== --}}
    {{-- RESUMEN ADMINISTRATIVO / FINANCIERO --}}
    {{-- ====================== --}}
    @can('puede-ver-finanzas')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        <div class="p-6 bg-white shadow rounded-xl">
            <h2 class="text-gray-600">Total Alumnos</h2>
            <p class="text-4xl font-bold text-indigo-700">{{ $alumnosCount }}</p>
            <p class="text-xs text-gray-400 mt-2">+{{ $alumnosNuevosMes }} este mes</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl">
            <h2 class="text-gray-600">Total Docentes</h2>
            <p class="text-4xl font-bold text-indigo-700">{{ $docentesCount }}</p>
            <p class="text-xs text-gray-400 mt-2">+{{ $docentesNuevosMes }} este mes</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl">
            <h2 class="text-gray-600">Solicitudes Pendientes</h2>
            <p class="text-4xl font-bold text-amber-600">{{ $pagosPendientes }}</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl">
            <h2 class="text-gray-600">Total Pagado Mes</h2>
            <p class="text-3xl font-bold text-green-600">
                ${{ number_format($montoPagadoMes, 2) }}
            </p>
        </div>
    </div>

    {{-- Últimos alumnos --}}
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">Últimos alumnos registrados</h2>
        <ul>
            @forelse($ultimosAlumnos as $al)
                <li class="py-1 border-b text-gray-700">
                    {{ $al->nombre_completo }} —
                    {{ $al->created_at ? $al->created_at->format('d/m/Y') : '—' }}
                </li>
            @empty
                <p class="text-gray-500">Sin registros</p>
            @endforelse
        </ul>
    </div>

    {{-- Últimas solicitudes --}}
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">Últimas solicitudes de pago</h2>
        <ul>
            @forelse($ultimasSolicitudes as $sol)
                <li class="py-1 border-b text-gray-700 flex justify-between">
                    <span>{{ $sol->docente->nombre_completo }}</span>
                    <span class="text-sm text-gray-500">{{ $sol->estatus }}</span>
                </li>
            @empty
                <p class="text-gray-500">No hay solicitudes</p>
            @endforelse
        </ul>
    </div>
    @endcan


    {{-- ====================== --}}
    {{-- RECEPCIÓN --}}
    {{-- ====================== --}}
    @can('es-recepcion')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="p-6 bg-white shadow rounded-xl">
            <h2 class="text-gray-600">Alumnos con Adeudo</h2>
            <p class="text-4xl font-bold text-red-600">{{ $alumnosConAdeudo }}</p>
        </div>

        <div class="p-6 bg-white shadow rounded-xl">
            <h2 class="text-gray-600">Bitácoras Hoy</h2>
            <p class="text-4xl font-bold text-indigo-700">{{ $bitacorasHoy }}</p>
        </div>

    </div>
    @endcan


    {{-- ====================== --}}
    {{-- ACADÉMICA --}}
    {{-- ====================== --}}
    @can('puede-ver-academica')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Grupos registrados</h2>
            <p class="text-4xl font-bold text-indigo-700">{{ $gruposActivos }}</p>
            <a href="{{ route('grupos.index') }}" class="inline-block mt-3 text-sm text-indigo-700 font-semibold hover:underline">Ver grupos</a>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Materias activas</h2>
            <p class="text-4xl font-bold text-green-700">{{ $materiasActivas }}</p>
            <a href="{{ route('materias.index') }}" class="inline-block mt-3 text-sm text-green-700 font-semibold hover:underline">Ver materias</a>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Calendarios operativos</h2>
            <p class="text-4xl font-bold text-blue-700">{{ $calendariosActivos }}</p>
            <a href="{{ route('calendarios_academicos.index') }}" class="inline-block mt-3 text-sm text-blue-700 font-semibold hover:underline">Ver calendarios</a>
        </div>

        <div class="p-6 bg-white shadow rounded-xl border border-slate-100">
            <h2 class="text-gray-600">Sesiones futuras</h2>
            <p class="text-4xl font-bold text-amber-600">{{ $sesionesProgramadas }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ $sesionesHoy }} sesión(es) hoy</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow border border-slate-100">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-xl font-semibold">Agenda académica de hoy</h2>
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
            <p class="text-gray-500">No hay sesiones programadas para hoy.</p>
        @endforelse
    </div>

    <div class="bg-white p-6 rounded-xl shadow border border-slate-100">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold">Próximas clases y sesiones</h2>
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
            <p class="text-gray-500">No hay sesiones próximas en los siguientes 14 días.</p>
        @endforelse
    </div>


    <div class="bg-white p-6 rounded-xl shadow border border-slate-100">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold">Próximos cursos especiales / Educación Continua</h2>
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
            <p class="text-gray-500">No hay sesiones de educación continua próximas en los siguientes 14 días.</p>
        @endforelse
    </div>
    @endcan

</div>
@endsection
