@extends('layouts.app')

@php
    use App\Models\Rol;
    use App\Models\SolicitudPagoDocente;

    $rolActual = auth()->user()?->rolClave();
    $badge = match ($solicitud->estatus) {
        SolicitudPagoDocente::ESTATUS_PENDIENTE => 'bg-amber-100 text-amber-700',
        SolicitudPagoDocente::ESTATUS_OBSERVADA => 'bg-orange-100 text-orange-700',
        SolicitudPagoDocente::ESTATUS_AUTORIZADA => 'bg-blue-100 text-blue-700',
        SolicitudPagoDocente::ESTATUS_PAGADA => 'bg-green-100 text-green-700',
        SolicitudPagoDocente::ESTATUS_CANCELADA => 'bg-red-100 text-red-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

@section('title', 'Detalle de Solicitud Docente')

@section('content')
<div class="max-w-6xl mx-auto mt-6 space-y-6">
    <div class="bg-white/90 backdrop-blur shadow-xl rounded-2xl p-6 border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-money-withdraw text-3xl text-blue-600'></i>
                    Solicitud {{ $solicitud->folio ?? '#'.$solicitud->id }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">Solicitud de pago a docente con trazabilidad académica y administrativa.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="px-4 py-2 rounded-xl text-sm font-bold {{ $badge }}">{{ $solicitud->estatus }}</span>
                <a href="{{ route('solicitudes_pago.index') }}" class="inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2 rounded-xl font-medium transition">← Volver</a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 border border-green-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 border border-red-200">{{ session('error') }}</div>
        @endif

        @if($solicitud->estatus === SolicitudPagoDocente::ESTATUS_OBSERVADA && $solicitud->motivo_observacion)
            <div class="bg-orange-50 border border-orange-200 text-orange-800 rounded-2xl p-4 mb-5">
                <p class="font-bold">Solicitud observada por Administración/Finanzas</p>
                <p class="mt-1 text-sm">{{ $solicitud->motivo_observacion }}</p>
            </div>
        @endif

        @if($solicitud->estatus === SolicitudPagoDocente::ESTATUS_CANCELADA && $solicitud->motivo_cancelacion)
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 mb-5">
                <p class="font-bold">Solicitud cancelada</p>
                <p class="mt-1 text-sm">{{ $solicitud->motivo_cancelacion }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">
                <p class="text-slate-500 text-xs">Docente</p>
                <p class="text-lg font-semibold text-slate-800 mt-1">{{ $solicitud->docente->nombre_completo ?? 'No disponible' }}</p>
            </div>
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">
                <p class="text-slate-500 text-xs">Monto solicitado</p>
                <p class="text-2xl font-bold text-green-700 mt-1">${{ number_format($solicitud->monto, 2) }}</p>
            </div>
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">
                <p class="text-slate-500 text-xs">Prioridad</p>
                <p class="text-lg font-semibold text-slate-800 mt-1">{{ $solicitud->prioridad ?? 'Normal' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-slate-800 mb-4">Servicio académico</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Origen</dt><dd class="font-semibold text-slate-800">{{ $solicitud->origen ?? 'Manual' }}</dd></div>
                    <div><dt class="text-slate-500">Concepto</dt><dd class="font-semibold text-slate-800">{{ $solicitud->concepto_pago ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Nivel</dt><dd class="font-semibold text-slate-800">{{ $solicitud->nivel ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Programa / grupo</dt><dd class="font-semibold text-slate-800">{{ $solicitud->programa_grupo ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Materia / actividad</dt><dd class="font-semibold text-slate-800">{{ $solicitud->materia_actividad ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Periodo</dt><dd class="font-semibold text-slate-800">{{ $solicitud->periodo ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Modalidad</dt><dd class="font-semibold text-slate-800">{{ $solicitud->modalidad ?? '—' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-slate-800 mb-4">Cálculo y fechas</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Sesiones</dt><dd class="font-semibold text-slate-800">{{ $solicitud->numero_sesiones ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Horas totales</dt><dd class="font-semibold text-slate-800">{{ $solicitud->horas_totales ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Tarifa por hora</dt><dd class="font-semibold text-slate-800">{{ $solicitud->tarifa_hora ? '$'.number_format($solicitud->tarifa_hora, 2) : '—' }}</dd></div>
                    <div><dt class="text-slate-500">Fecha solicitud</dt><dd class="font-semibold text-slate-800">{{ $solicitud->fecha_solicitud?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Periodo del servicio</dt><dd class="font-semibold text-slate-800">{{ $solicitud->fecha_inicio_periodo?->format('d/m/Y') ?? '—' }} — {{ $solicitud->fecha_fin_periodo?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Fecha límite sugerida</dt><dd class="font-semibold text-slate-800">{{ $solicitud->fecha_limite_pago?->format('d/m/Y') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Fecha pago</dt><dd class="font-semibold text-slate-800">{{ $solicitud->fecha_pago?->format('d/m/Y') ?? '—' }}</dd></div>
                </dl>
            </section>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <h2 class="font-bold text-slate-800 mb-4">Trazabilidad</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Creado por</dt><dd class="font-semibold text-slate-800">{{ $solicitud->creadoPor->nombre ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Autorizado por</dt><dd class="font-semibold text-slate-800">{{ $solicitud->autorizadoPor->nombre ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Fecha autorización</dt><dd class="font-semibold text-slate-800">{{ $solicitud->fecha_autorizacion?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Pagado/procesado por</dt><dd class="font-semibold text-slate-800">{{ $solicitud->procesadoPor->nombre ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Cancelado por</dt><dd class="font-semibold text-slate-800">{{ $solicitud->canceladoPor->nombre ?? '—' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <h2 class="font-bold text-slate-800 mb-4">Datos de pago</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Método</dt><dd class="font-semibold text-slate-800">{{ $solicitud->metodo_pago ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Referencia</dt><dd class="font-semibold text-slate-800">{{ $solicitud->referencia_pago ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Banco</dt><dd class="font-semibold text-slate-800">{{ $solicitud->banco_pago ?? '—' }}</dd></div>
                    <div>
                        <dt class="text-slate-500">Comprobante</dt>
                        <dd class="font-semibold text-slate-800">
                            @if($solicitud->comprobante_pago_path)
                                <a href="{{ route('solicitudes_pago.comprobante', $solicitud) }}" class="text-blue-600 hover:underline">Descargar comprobante</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-slate-800 mb-2">Observaciones de Académica</h2>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $solicitud->observaciones_academica ?: $solicitud->observaciones ?: '—' }}</p>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-slate-800 mb-2">Observaciones de Administración/Finanzas</h2>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $solicitud->observaciones_administracion ?: '—' }}</p>
            </section>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-xl shadow-inner p-5 flex flex-wrap gap-3 mt-6">
            @if(in_array($rolActual, [Rol::ADMIN, Rol::ACADEMICA], true) && $solicitud->puedeEditarAcademica())
                <a href="{{ route('solicitudes_pago.edit', $solicitud) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg shadow">Editar</a>
            @endif

            @if(in_array($rolActual, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true) && $solicitud->estatus === SolicitudPagoDocente::ESTATUS_PENDIENTE)
                <form method="POST" action="{{ route('solicitudes_pago.aprobar', $solicitud) }}">
                    @csrf
                    @method('PUT')
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">Autorizar para pago</button>
                </form>
            @endif

            @if(in_array($rolActual, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true) && in_array($solicitud->estatus, [SolicitudPagoDocente::ESTATUS_PENDIENTE, SolicitudPagoDocente::ESTATUS_AUTORIZADA], true))
                <a href="{{ route('solicitudes_pago.observar.form', $solicitud) }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg shadow">Observar / devolver</a>
            @endif

            @if(in_array($rolActual, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true) && $solicitud->estatus === SolicitudPagoDocente::ESTATUS_AUTORIZADA)
                <a href="{{ route('solicitudes_pago.form_pagar', $solicitud) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow">Registrar pago</a>
            @endif

            @if(in_array($rolActual, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true) && !in_array($solicitud->estatus, [SolicitudPagoDocente::ESTATUS_PAGADA, SolicitudPagoDocente::ESTATUS_CANCELADA], true))
                <a href="{{ route('solicitudes_pago.cancelar.form', $solicitud) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow">Cancelar</a>
            @endif

            @if($rolActual === Rol::ADMIN && $solicitud->estatus !== SolicitudPagoDocente::ESTATUS_PAGADA)
                <form method="POST" action="{{ route('solicitudes_pago.destroy', $solicitud) }}" onsubmit="return confirm('¿Seguro de eliminar esta solicitud? Es preferible cancelar para conservar trazabilidad.');">
                    @csrf
                    @method('DELETE')
                    <button class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg shadow">Eliminar</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
