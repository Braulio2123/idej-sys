@extends('layouts.app')

@section('title', 'Agenda Operativa')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-blue-700 uppercase tracking-[0.2em]">Académica · Sistemas · Recepción</p>
            <h1 class="text-3xl font-bold text-slate-900">Agenda operativa</h1>
            <p class="text-slate-500 mt-1">Clases principales y sesiones de educación continua en una sola vista.</p>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm px-5 py-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400 font-bold">Periodo consultado</p>
            <p class="text-lg font-bold text-slate-900">
                {{ $fechaInicio->format('d/m/Y') }} - {{ $fechaFin->format('d/m/Y') }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase">Eventos</p>
            <p class="text-3xl font-black text-slate-900 mt-2">{{ $resumen['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase">Clases principales</p>
            <p class="text-3xl font-black text-blue-700 mt-2">{{ $resumen['principales'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase">Educación continua</p>
            <p class="text-3xl font-black text-violet-700 mt-2">{{ $resumen['educacion_continua'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase">Con equipo</p>
            <p class="text-3xl font-black text-amber-600 mt-2">{{ $resumen['requieren_equipo'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase">Virtual/Mixta</p>
            <p class="text-3xl font-black text-emerald-700 mt-2">{{ $resumen['virtuales_mixtas'] }}</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Rango</label>
                <select name="rango" class="w-full rounded-xl border-slate-300 text-sm">
                    @foreach($rangos as $key => $label)
                        <option value="{{ $key }}" @selected($rangoSeleccionado === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Desde</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio', $fechaInicio->toDateString()) }}" class="w-full rounded-xl border-slate-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Hasta</label>
                <input type="date" name="fecha_fin" value="{{ request('fecha_fin', $fechaFin->toDateString()) }}" class="w-full rounded-xl border-slate-300 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tipo</label>
                <select name="tipo" class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="todos" @selected($tipo === 'todos')>Todos</option>
                    <option value="principal" @selected($tipo === 'principal')>Clases principales</option>
                    <option value="educacion_continua" @selected($tipo === 'educacion_continua')>Educación continua</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Modalidad</label>
                <select name="modalidad" class="w-full rounded-xl border-slate-300 text-sm">
                    @foreach($modalidades as $opcion)
                        <option value="{{ $opcion }}" @selected($modalidad === $opcion)>{{ $opcion === 'todas' ? 'Todas' : $opcion }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Búsqueda</label>
                <input type="text" name="q" value="{{ $busqueda }}" placeholder="Materia, docente, aula..." class="w-full rounded-xl border-slate-300 text-sm">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="solo_equipo" value="1" @checked($soloEquipo) class="rounded border-slate-300 text-blue-700">
                Mostrar solo actividades que requieren preparación técnica
            </label>

            <div class="flex gap-2">
                <a href="{{ route('agenda-operativa.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 font-semibold hover:bg-slate-50">Limpiar</a>
                <button class="px-5 py-2 rounded-xl bg-blue-700 text-white font-semibold shadow-sm hover:bg-blue-800">Filtrar</button>
            </div>
        </div>
    </form>

    @if($equipoResumen->isNotEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Preparación técnica requerida</h2>
                    <p class="text-sm text-slate-500">Resumen para Sistemas según las actividades filtradas.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($equipoResumen as $equipo => $cantidad)
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1.5 text-sm font-semibold">
                        <i class='bx bx-wrench'></i> {{ $equipo }} <strong>{{ $cantidad }}</strong>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="space-y-5">
        @forelse($eventosPorDia as $fecha => $eventosDia)
            @php
                $fechaCarbon = \Carbon\Carbon::parse($fecha);
            @endphp

            <section class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-900 text-white px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-300 font-bold">{{ ucfirst($fechaCarbon->translatedFormat('l')) }}</p>
                        <h2 class="text-xl font-bold">{{ $fechaCarbon->format('d/m/Y') }}</h2>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-white/10 px-3 py-1 text-sm font-semibold">
                        {{ $eventosDia->count() }} actividad{{ $eventosDia->count() === 1 ? '' : 'es' }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($eventosDia as $evento)
                        <article class="p-5 hover:bg-slate-50 transition">
                            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4">
                                <div class="flex gap-4 min-w-0">
                                    <div class="w-24 shrink-0 text-center rounded-2xl border {{ $evento['origen'] === 'principal' ? 'border-blue-200 bg-blue-50 text-blue-800' : 'border-violet-200 bg-violet-50 text-violet-800' }} p-3">
                                        <p class="text-lg font-black">{{ $evento['hora_inicio'] }}</p>
                                        <p class="text-xs font-semibold">{{ $evento['hora_fin'] }}</p>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-bold {{ $evento['origen'] === 'principal' ? 'bg-blue-100 text-blue-800' : 'bg-violet-100 text-violet-800' }}">
                                                {{ $evento['tipo_label'] }}
                                            </span>
                                            <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-bold bg-slate-100 text-slate-700">
                                                {{ $evento['badge'] }}
                                            </span>
                                            <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                {{ $evento['estatus'] }}
                                            </span>
                                        </div>

                                        <h3 class="text-lg font-bold text-slate-900">{{ $evento['titulo'] }}</h3>
                                        <p class="text-sm text-slate-500">{{ $evento['subtitulo'] }}</p>

                                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-slate-600">
                                            <p><i class='bx bx-group text-slate-400'></i> {{ $evento['grupo_curso'] }}</p>
                                            <p><i class='bx bx-user text-slate-400'></i> {{ $evento['docente'] }}</p>
                                            <p><i class='bx bx-map text-slate-400'></i> {{ $evento['lugar'] }}</p>
                                            <p><i class='bx bx-video text-slate-400'></i> {{ $evento['modalidad'] }}</p>
                                        </div>

                                        @if($evento['observaciones'])
                                            <p class="mt-3 text-sm text-slate-500 bg-slate-100 rounded-xl p-3">{{ $evento['observaciones'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="xl:w-72 shrink-0 space-y-3">
                                    @if(!empty($evento['equipo']))
                                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-3">
                                            <p class="text-xs font-bold uppercase text-amber-700 mb-2">Equipo / preparación</p>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($evento['equipo'] as $equipo)
                                                    <span class="rounded-full bg-white border border-amber-200 px-2 py-1 text-xs font-semibold text-amber-800">{{ $equipo }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500">
                                            Sin equipo técnico marcado.
                                        </div>
                                    @endif

                                    @if($evento['url'])
                                        <a href="{{ $evento['url'] }}" class="block text-center rounded-xl bg-slate-900 text-white font-semibold px-4 py-2 hover:bg-slate-800">
                                            Abrir detalle
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-10 text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-3xl mb-4">
                    <i class='bx bx-calendar-x'></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900">No hay actividades en el periodo seleccionado</h2>
                <p class="text-slate-500 mt-1">Ajusta los filtros o amplía el rango de fechas.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
