@extends('layouts.app')

@section('title', 'Expediente del Alumno')

@section('content')
@php
    use App\Models\Rol;
    use App\Models\Seguimiento;
    use App\Models\DocumentoAlumno;
    use App\Models\Beca;

    $usuarioActual = Auth::user();
    $puedeOperarFinanzas = $usuarioActual?->tieneRol(Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS) ?? false;
    $puedeCancelarPagos = $usuarioActual?->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS) ?? false;
    $puedeEditarAlumno = $usuarioActual?->tieneRol(Rol::RECEPCION, Rol::CADMIN) ?? false;
    $puedeRegistrarSeguimiento = $usuarioActual?->tieneRol(Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::RRPP, Rol::ACADEMICA) ?? false;
    $puedeGestionarDocumentos = $usuarioActual?->tieneRol(Rol::RECEPCION, Rol::CADMIN, Rol::RRPP, Rol::ACADEMICA) ?? false;
    $puedeGestionarBecas = $usuarioActual?->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS) ?? false;

    $becaActiva = $alumno->becaVigente();
    $becasActivas = $alumno->becas->where('estatus', Beca::ESTATUS_ACTIVA)->count();
    $descuentoBecasHistorico = $alumno->cargos->sum('beca_monto_aplicado');

    $totalAdeudo = $alumno->cargos->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado', 'En Convenio'])->sum('monto_adeudo');
    $totalPagado = $alumno->pagos->where('estatus', 'Activo')->sum('monto_total_pagado');
    $conveniosActivos = $alumno->convenios->where('estatus', 'Activo')->count();
    $tiposSeguimiento = Seguimiento::tipos();
    $prioridadesSeguimiento = Seguimiento::prioridades();
    $estatusSeguimiento = Seguimiento::estatusDisponibles();
@endphp

@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg shadow-sm">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg shadow-sm">
        ⚠️ {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg shadow-sm">
        <p class="font-semibold mb-2">Revisa los datos capturados:</p>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mx-auto px-2 md:px-4 py-4"
     x-data="{ openResumen: true, openDocumentos: true, openSeguimientos: true, openNuevoSeguimiento: false, openCargos: false, openPagos: false, openConvenios: false }">

    {{-- Encabezado ejecutivo --}}
    <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-slate-800 text-white rounded-2xl shadow-xl p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-amber-200 mb-2">Expediente institucional</p>
                <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $alumno->nombre_completo }}</h1>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1 text-sm text-blue-50/90">
                    <p>Matrícula: <strong class="text-white">{{ $alumno->matricula }}</strong></p>
                    <p>Correo: <strong class="text-white">{{ $alumno->correo ?? 'Sin correo' }}</strong></p>
                    <p>Teléfono: <strong class="text-white">{{ $alumno->telefono ?? 'Sin teléfono' }}</strong></p>
                    <p>Programa: <strong class="text-white">{{ $alumno->grupo->programa->nombre ?? 'Sin asignar' }}</strong></p>
                    <p>Grupo: <strong class="text-white">{{ $alumno->grupo->nombre ?? 'Sin grupo' }}</strong></p>
                    <p>Ciclo: <strong class="text-white">{{ $alumno->grupo->cicloEscolar->nombre ?? 'Sin ciclo' }}</strong></p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 lg:justify-end">
                @if($puedeEditarAlumno)
                    <a href="{{ route('alumnos.edit', $alumno) }}" class="bg-white/10 hover:bg-white/20 border border-white/20 px-4 py-2 rounded-xl text-sm font-semibold transition">
                        Editar alumno
                    </a>
                @endif

                @if($puedeOperarFinanzas)
                    <a href="{{ route('alumnos.cargos.create', $alumno) }}" class="bg-indigo-500 hover:bg-indigo-600 px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                        + Cargo
                    </a>
                    <a href="{{ route('alumnos.pagos.create', $alumno) }}" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                        + Pago
                    </a>
                    @if(in_array($alumno->estatus_financiero, ['Con Adeudo', 'En Convenio'], true))
                        <a href="{{ route('alumnos.convenios.create', $alumno) }}" class="bg-amber-400 hover:bg-amber-500 text-slate-900 px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                            + Convenio
                        </a>
                    @endif
                @endif

                @if($puedeRegistrarSeguimiento)
                    <button type="button" @click="openNuevoSeguimiento = !openNuevoSeguimiento" class="bg-purple-500 hover:bg-purple-600 px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                        + Seguimiento
                    </button>
                @endif


                @if($puedeGestionarDocumentos)
                    <form action="{{ route('alumnos.documentos.generar-checklist', $alumno) }}" method="POST" onsubmit="return confirm('¿Generar checklist documental desde el catálogo? No se duplicarán documentos existentes.');">
                        @csrf
                        <button class="bg-cyan-500 hover:bg-cyan-600 px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                            Generar checklist
                        </button>
                    </form>
                @endif

                <a href="{{ route('alumnos.documentos.index', $alumno) }}" class="bg-white/10 hover:bg-white/20 border border-white/20 px-4 py-2 rounded-xl text-sm font-semibold transition">
                    Expediente documental
                </a>

                <a href="{{ route('alumnos.becas.index', $alumno) }}" class="bg-white/10 hover:bg-white/20 border border-white/20 px-4 py-2 rounded-xl text-sm font-semibold transition">
                    Becas
                </a>

                @if($puedeGestionarBecas)
                    <a href="{{ route('alumnos.becas.create', $alumno) }}" class="bg-emerald-500 hover:bg-emerald-600 px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                        + Beca
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Indicadores principales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Estatus financiero</p>
            <p class="mt-2 text-lg font-bold
                @if($alumno->estatus_financiero === 'Al Corriente') text-green-700
                @elseif($alumno->estatus_financiero === 'Con Adeudo') text-red-700
                @elseif($alumno->estatus_financiero === 'En Convenio') text-amber-700
                @else text-blue-700 @endif">
                {{ $alumno->estatus_financiero }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Adeudo actual</p>
            <p class="mt-2 text-2xl font-bold text-red-700">${{ number_format($totalAdeudo, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Pagado histórico</p>
            <p class="mt-2 text-2xl font-bold text-green-700">${{ number_format($totalPagado, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Saldo a favor</p>
            <p class="mt-2 text-2xl font-bold text-blue-700">${{ number_format($alumno->saldo_a_favor, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Beca vigente</p>
            <p class="mt-2 text-2xl font-bold {{ $becaActiva ? 'text-emerald-700' : 'text-slate-500' }}">
                {{ $becaActiva ? $becaActiva->porcentaje . '%' : 'Sin beca' }}
            </p>
            @if($becaActiva)
                <p class="text-xs text-slate-500 mt-1">{{ $becaActiva->tipo }}</p>
            @elseif($descuentoBecasHistorico > 0)
                <p class="text-xs text-slate-500 mt-1">Histórico: ${{ number_format($descuentoBecasHistorico, 2) }}</p>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Seguimientos abiertos</p>
            <p class="mt-2 text-2xl font-bold {{ $seguimientosVencidos > 0 ? 'text-red-700' : 'text-purple-700' }}">
                {{ $seguimientosAbiertos }}
                @if($seguimientosVencidos > 0)
                    <span class="text-sm font-semibold">({{ $seguimientosVencidos }} venc.)</span>
                @endif
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Documentos</p>
            <p class="mt-2 text-2xl font-bold {{ $documentosPendientes > 0 ? 'text-red-700' : 'text-cyan-700' }}">
                {{ $documentosAceptados }}/{{ $documentosTotal }}
                @if($documentosPendientes > 0)
                    <span class="text-sm font-semibold">({{ $documentosPendientes }} pend.)</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Formulario rápido de seguimiento --}}
    @if($puedeRegistrarSeguimiento)
        <div x-show="openNuevoSeguimiento" x-transition class="bg-purple-50 border border-purple-200 rounded-2xl shadow p-6 mb-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold text-purple-900">Registrar seguimiento</h2>
                    <p class="text-sm text-purple-700">Usa este registro para llamadas, WhatsApp, visitas, entrega de documentos, acuerdos de pago u observaciones académicas.</p>
                </div>
                <button type="button" @click="openNuevoSeguimiento = false" class="text-purple-700 hover:text-purple-900 font-semibold">Cerrar</button>
            </div>

            <form method="POST" action="{{ route('alumnos.seguimientos.store', $alumno) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo</label>
                    <select name="tipo" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($tiposSeguimiento as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Prioridad</label>
                    <select name="prioridad" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($prioridadesSeguimiento as $prioridad)
                            <option value="{{ $prioridad }}" @selected(old('prioridad', 'Normal') === $prioridad)>{{ $prioridad }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus</label>
                    <select name="estatus" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($estatusSeguimiento as $estatus)
                            <option value="{{ $estatus }}" @selected(old('estatus', 'Abierto') === $estatus)>{{ $estatus }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Próximo contacto</label>
                    <input type="datetime-local" name="fecha_proximo_contacto" value="{{ old('fecha_proximo_contacto') }}" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Asunto</label>
                    <input type="text" name="asunto" value="{{ old('asunto') }}" required maxlength="160" placeholder="Ej. Se acordó enviar comprobante / Falta documento / Llamada de seguimiento" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="4" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500" placeholder="Describe el contacto, comentario, solicitud o situación detectada.">{{ old('descripcion') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Resultado / acuerdo</label>
                    <textarea name="resultado" rows="4" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500" placeholder="Registra el acuerdo, respuesta del alumno o acción pendiente.">{{ old('resultado') }}</textarea>
                </div>

                <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow">
                        Guardar seguimiento
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Resumen académico y administrativo --}}
    <div class="bg-white shadow rounded-2xl border border-slate-100 mb-6 overflow-hidden">
        <button @click="openResumen = !openResumen" class="w-full bg-slate-800 text-white px-6 py-3 text-lg font-semibold flex justify-between">
            <span>Resumen del expediente</span>
            <span x-text="openResumen ? '▲' : '▼'"></span>
        </button>

        <div x-show="openResumen" x-transition class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-5 text-sm">
            <div class="border border-slate-100 rounded-xl p-4">
                <h3 class="font-bold text-slate-800 mb-3">Datos personales</h3>
                <p><strong>Nombre:</strong> {{ $alumno->nombre_completo }}</p>
                <p><strong>Correo:</strong> {{ $alumno->correo ?? 'Sin correo' }}</p>
                <p><strong>Teléfono:</strong> {{ $alumno->telefono ?? 'Sin teléfono' }}</p>
            </div>

            <div class="border border-slate-100 rounded-xl p-4">
                <h3 class="font-bold text-slate-800 mb-3">Datos académicos</h3>
                <p><strong>Programa:</strong> {{ $alumno->grupo->programa->nombre ?? 'Sin asignar' }}</p>
                <p><strong>Grupo:</strong> {{ $alumno->grupo->nombre ?? 'Sin grupo' }}</p>
                <p><strong>Ciclo escolar:</strong> {{ $alumno->grupo->cicloEscolar->nombre ?? 'Sin ciclo' }}</p>
                <p><strong>Estatus académico:</strong> {{ $alumno->estatus_academico }}</p>
                <p><strong>Condición:</strong> {{ $alumno->condicion_alumno ?? 'Normal' }}</p>
            </div>

            <div class="border border-slate-100 rounded-xl p-4">
                <h3 class="font-bold text-slate-800 mb-3">Datos financieros</h3>
                <p><strong>Estatus:</strong> {{ $alumno->estatus_financiero }}</p>
                <p><strong>Beca:</strong> {{ $alumno->beca_porcentaje > 0 ? $alumno->beca_porcentaje . '%' : 'No aplica' }}</p>
                <p><strong>Convenios activos:</strong> {{ $conveniosActivos }}</p>
                <p><strong>Saldo a favor:</strong> ${{ number_format($alumno->saldo_a_favor, 2) }}</p>
            </div>
        </div>
    </div>


    {{-- Expediente documental --}}
    <div class="bg-white shadow rounded-2xl border border-slate-100 mb-6 overflow-hidden">
        <button @click="openDocumentos = !openDocumentos" class="w-full bg-cyan-600 text-white px-6 py-3 text-lg font-semibold flex justify-between">
            <span>Expediente documental</span>
            <span x-text="openDocumentos ? '▲' : '▼'"></span>
        </button>

        <div x-show="openDocumentos" x-transition class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <div class="bg-cyan-50 border border-cyan-100 rounded-xl p-4">
                    <p class="text-xs uppercase text-cyan-700 font-semibold">Requisitos / registrados</p>
                    <p class="text-2xl font-bold text-cyan-900">{{ $requisitosDocumentales }}/{{ $documentosTotal }}</p>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                    <p class="text-xs uppercase text-green-700 font-semibold">Aceptados</p>
                    <p class="text-2xl font-bold text-green-800">{{ $documentosAceptados }}</p>
                </div>
                <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                    <p class="text-xs uppercase text-red-700 font-semibold">Pendientes / rechazados</p>
                    <p class="text-2xl font-bold text-red-800">{{ $documentosPendientes }}</p>
                </div>
            </div>

            @if($documentos->isEmpty())
                <div class="text-slate-500 bg-slate-50 border border-dashed border-slate-300 rounded-xl p-5">
                    No hay documentos registrados para este alumno.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border rounded-lg overflow-hidden">
                        <thead class="bg-cyan-600 text-white text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Documento</th>
                                <th class="px-4 py-3 text-left">Estatus</th>
                                <th class="px-4 py-3 text-left">Archivo</th>
                                <th class="px-4 py-3 text-left">Registró</th>
                                <th class="px-4 py-3 text-left">Revisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentos as $documento)
                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800">{{ $documento->tipo_documento }}</p>
                                        @if($documento->requisitoDocumental)
                                            <p class="text-[11px] text-cyan-700 font-semibold mt-0.5">{{ $documento->requisitoDocumental->obligatorio ? 'Requisito obligatorio' : 'Requisito opcional' }}</p>
                                        @endif
                                        @if($documento->observaciones)
                                            <p class="text-xs text-slate-500 mt-1">{{ \Illuminate\Support\Str::limit($documento->observaciones, 90) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                            @if($documento->estatus === DocumentoAlumno::ESTATUS_ACEPTADO) bg-green-100 text-green-700
                                            @elseif($documento->estatus === DocumentoAlumno::ESTATUS_RECHAZADO) bg-red-100 text-red-700
                                            @elseif($documento->estatus === DocumentoAlumno::ESTATUS_EN_REVISION) bg-yellow-100 text-yellow-700
                                            @elseif($documento->estatus === DocumentoAlumno::ESTATUS_ENTREGADO) bg-blue-100 text-blue-700
                                            @else bg-slate-100 text-slate-700 @endif">
                                            {{ $documento->estatus }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($documento->archivo_path)
                                            <a href="{{ route('alumnos.documentos.download', [$alumno, $documento]) }}" class="text-cyan-700 hover:underline font-semibold">
                                                Descargar
                                            </a>
                                            <p class="text-xs text-slate-500">{{ $documento->tamano_legible }}</p>
                                        @else
                                            <span class="text-slate-400">Sin archivo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $documento->usuarioSubio->nombre ?? '—' }}
                                        <p class="text-xs text-slate-400">{{ optional($documento->fecha_entrega)->format('d/m/Y H:i') ?? optional($documento->created_at)->format('d/m/Y H:i') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $documento->usuarioReviso->nombre ?? '—' }}
                                        <p class="text-xs text-slate-400">{{ optional($documento->fecha_revision)->format('d/m/Y H:i') ?? 'Sin revisión' }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-5 flex justify-end">
                <a href="{{ route('alumnos.documentos.index', $alumno) }}" class="text-cyan-700 hover:underline font-semibold">
                    Gestionar expediente documental →
                </a>
            </div>
        </div>
    </div>

    {{-- Seguimientos --}}
    <div class="bg-white shadow rounded-2xl border border-slate-100 mb-6 overflow-hidden">
        <button @click="openSeguimientos = !openSeguimientos" class="w-full bg-purple-600 text-white px-6 py-3 text-lg font-semibold flex justify-between">
            <span>Seguimientos recientes</span>
            <span x-text="openSeguimientos ? '▲' : '▼'"></span>
        </button>

        <div x-show="openSeguimientos" x-transition class="p-6">
            @if($seguimientos->isEmpty())
                <div class="text-slate-500 bg-slate-50 border border-dashed border-slate-300 rounded-xl p-5">
                    No hay seguimientos registrados para este alumno.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($seguimientos as $seguimiento)
                        @php
                            $estaVencido = $seguimiento->fecha_proximo_contacto && $seguimiento->fecha_proximo_contacto->isPast() && in_array($seguimiento->estatus, ['Abierto', 'En proceso'], true);
                        @endphp
                        <div class="border rounded-xl p-4 {{ $estaVencido ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">{{ $seguimiento->tipo }}</span>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                            @if($seguimiento->prioridad === 'Urgente') bg-red-100 text-red-700
                                            @elseif($seguimiento->prioridad === 'Alta') bg-orange-100 text-orange-700
                                            @elseif($seguimiento->prioridad === 'Baja') bg-slate-100 text-slate-600
                                            @else bg-blue-100 text-blue-700 @endif">
                                            {{ $seguimiento->prioridad }}
                                        </span>
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                            @if($seguimiento->estatus === 'Cerrado') bg-green-100 text-green-700
                                            @elseif($seguimiento->estatus === 'Cancelado') bg-slate-200 text-slate-700
                                            @else bg-yellow-100 text-yellow-700 @endif">
                                            {{ $seguimiento->estatus }}
                                        </span>
                                        @if($estaVencido)
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-600 text-white">Vencido</span>
                                        @endif
                                    </div>
                                    <h3 class="font-bold text-slate-900">{{ $seguimiento->asunto }}</h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Registrado por {{ $seguimiento->usuario->nombre ?? 'Usuario no disponible' }} · {{ optional($seguimiento->created_at)->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                <div class="text-sm text-slate-600 lg:text-right">
                                    <p><strong>Contacto:</strong> {{ optional($seguimiento->fecha_contacto)->format('d/m/Y H:i') ?? '—' }}</p>
                                    <p><strong>Próximo:</strong> {{ optional($seguimiento->fecha_proximo_contacto)->format('d/m/Y H:i') ?? 'Sin programar' }}</p>
                                </div>
                            </div>

                            @if($seguimiento->descripcion)
                                <p class="text-sm text-slate-700 mt-3 whitespace-pre-line">{{ $seguimiento->descripcion }}</p>
                            @endif

                            @if($seguimiento->resultado)
                                <div class="mt-3 bg-white border border-slate-200 rounded-lg p-3">
                                    <p class="text-xs uppercase font-semibold text-slate-500 mb-1">Resultado / acuerdo</p>
                                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $seguimiento->resultado }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex justify-end">
                    <a href="{{ route('alumnos.seguimientos.index', $alumno) }}" class="text-purple-700 hover:underline font-semibold">
                        Ver historial completo →
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Cargos --}}
    <div class="bg-white shadow rounded-2xl border border-slate-100 mb-6 overflow-hidden">
        <button @click="openCargos = !openCargos" class="w-full bg-indigo-600 text-white px-6 py-3 text-lg font-semibold flex justify-between">
            <span>Cargos recientes</span>
            <span x-text="openCargos ? '▲' : '▼'"></span>
        </button>

        <div x-show="openCargos" x-transition class="p-6">
            @if($cargos->isEmpty())
                <p class="text-gray-500">No hay cargos registrados.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 border rounded-lg overflow-hidden">
                        <thead class="bg-indigo-600 text-white text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3">Concepto</th>
                                <th class="px-4 py-3">Descripción</th>
                                <th class="px-4 py-3 text-right">Original</th>
                                <th class="px-4 py-3 text-right">Beca</th>
                                <th class="px-4 py-3 text-right">Adeudo</th>
                                <th class="px-4 py-3">Estatus</th>
                                <th class="px-4 py-3">Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cargos as $cargo)
                                <tr class="hover:bg-gray-50 border-b">
                                    <td class="px-4 py-3">{{ $cargo->concepto->nombre ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $cargo->descripcion_cargo }}</td>
                                    <td class="px-4 py-3 text-right">${{ number_format($cargo->monto_original, 2) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if(($cargo->beca_porcentaje_aplicado ?? 0) > 0)
                                            <span class="text-emerald-700 font-semibold">{{ $cargo->beca_porcentaje_aplicado }}%</span>
                                            <p class="text-xs text-slate-500">-${{ number_format($cargo->beca_monto_aplicado, 2) }}</p>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">${{ number_format($cargo->monto_adeudo, 2) }}</td>
                                    <td class="px-4 py-3">{{ $cargo->estatus }}</td>
                                    <td class="px-4 py-3">{{ $cargo->fecha_vencimiento ? $cargo->fecha_vencimiento->format('d/m/Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5 flex justify-end">
                    <a href="{{ route('alumnos.cargos.index', $alumno) }}" class="text-indigo-600 hover:underline font-semibold">Ver todos los cargos →</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Pagos --}}
    <div class="bg-white shadow rounded-2xl border border-slate-100 mb-6 overflow-hidden">
        <button @click="openPagos = !openPagos" class="w-full bg-green-600 text-white px-6 py-3 text-lg font-semibold flex justify-between">
            <span>Pagos recientes</span>
            <span x-text="openPagos ? '▲' : '▼'"></span>
        </button>

        <div x-show="openPagos" x-transition class="p-6">
            @if($pagos->isEmpty())
                <p class="text-gray-500">No hay pagos registrados.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 border rounded-lg overflow-hidden">
                        <thead class="bg-green-600 text-white text-xs uppercase">
                            <tr>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Folio</th>
                                <th class="px-4 py-3 text-right">Monto</th>
                                <th class="px-4 py-3">Método</th>
                                <th class="px-4 py-3">Referencia</th>
                                <th class="px-4 py-3">Comprobante</th>
                                <th class="px-4 py-3">Estatus</th>
                                <th class="px-4 py-3">Recibo</th>
                                <th class="px-4 py-3">Acciones</th>
                                <th class="px-4 py-3">Registró</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagos as $pago)
                                <tr class="hover:bg-gray-50 border-b {{ $pago->estaCancelado() ? 'bg-red-50 text-slate-500' : '' }}">
                                    <td class="px-4 py-3">{{ optional($pago->fecha_pago)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $pago->folio_recibo ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right {{ $pago->estaCancelado() ? 'line-through' : '' }}">${{ number_format($pago->monto_total_pagado, 2) }}</td>
                                    <td class="px-4 py-3">{{ $pago->metodo_pago }}</td>
                                    <td class="px-4 py-3">{{ $pago->referencia_principal ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($pago->archivo_comprobante)
                                            <a href="{{ asset('storage/' . $pago->archivo_comprobante) }}" target="_blank" class="text-indigo-600 hover:underline">Ver</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($pago->estaCancelado())
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Cancelado</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Activo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('alumnos.pagos.recibo', [$alumno, $pago]) }}" target="_blank" class="text-slate-700 hover:underline font-semibold">PDF</a>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($puedeCancelarPagos && ! $pago->estaCancelado() && $pago->corteCaja?->estaAbierta())
                                            <a href="{{ route('alumnos.pagos.cancelar.confirmar', [$alumno, $pago]) }}" class="text-red-600 hover:underline font-semibold">Cancelar</a>
                                        @elseif($puedeCancelarPagos && ! $pago->estaCancelado() && $pago->corteCaja?->estaCerrada())
                                            <a href="{{ route('alumnos.pagos.ajuste-cancelacion.confirmar', [$alumno, $pago]) }}" class="text-amber-600 hover:underline font-semibold">Ajuste</a>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $pago->usuario->nombre ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5 flex justify-end">
                    <a href="{{ route('alumnos.pagos.index', $alumno) }}" class="text-green-600 hover:underline font-semibold">Ver todos los pagos →</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Convenios --}}
    <div class="bg-white shadow rounded-2xl border border-slate-100 overflow-hidden">
        <button @click="openConvenios = !openConvenios" class="w-full bg-amber-500 text-white px-6 py-3 text-lg font-semibold flex justify-between">
            <span>Convenios recientes</span>
            <span x-text="openConvenios ? '▲' : '▼'"></span>
        </button>

        <div x-show="openConvenios" x-transition class="p-6">
            @if($convenios->isEmpty())
                <p class="text-gray-500">No hay convenios registrados.</p>
            @else
                @foreach($convenios as $convenio)
                    <div class="border rounded-xl p-4 mb-5 shadow-sm bg-gray-50">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
                            <div>
                                <p class="font-bold text-slate-800">Convenio #{{ $convenio->id }} — {{ $convenio->descripcion }}</p>
                                <p class="text-sm text-slate-600">
                                    Total: <strong>${{ number_format($convenio->total_reestructurado, 2) }}</strong> · {{ $convenio->numero_parcialidades }} parcialidades · Estatus: <strong>{{ $convenio->estatus }}</strong>
                                </p>
                            </div>
                            <a href="{{ route('parcialidades.index', $convenio) }}" class="bg-indigo-500 text-white text-sm px-3 py-2 rounded-lg hover:bg-indigo-600 shadow text-center">
                                Ver parcialidades →
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border rounded-lg overflow-hidden bg-white">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="px-3 py-2 text-left">#</th>
                                        <th class="px-3 py-2 text-right">Monto</th>
                                        <th class="px-3 py-2 text-center">Vencimiento</th>
                                        <th class="px-3 py-2 text-center">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($convenio->parcialidades as $i => $p)
                                        <tr class="border-b">
                                            <td class="px-3 py-2">{{ $i + 1 }}</td>
                                            <td class="px-3 py-2 text-right">${{ number_format($p->monto_parcialidad, 2) }}</td>
                                            <td class="px-3 py-2 text-center">{{ \Carbon\Carbon::parse($p->fecha_vencimiento)->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2 text-center">{{ $p->estatus }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <div class="text-right mt-5">
                    <a href="{{ route('alumnos.convenios.index', $alumno) }}" class="text-amber-600 hover:underline font-semibold">Ver todos los convenios →</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
