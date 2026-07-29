@extends('layouts.app')

@php
    use App\Models\Rol;
    use App\Models\SolicitudPagoDocente;
    $rolActual = auth()->user()?->rolClave();
    $veFinanzas = in_array($rolActual, [Rol::ADMIN, Rol::CADMIN, Rol::DIRECCION], true);
    $badge = match ($solicitud->estatus) {
        SolicitudPagoDocente::ESTATUS_PENDIENTE => 'bg-amber-100 text-amber-700',
        SolicitudPagoDocente::ESTATUS_OBSERVADA => 'bg-orange-100 text-orange-700',
        SolicitudPagoDocente::ESTATUS_AUTORIZADA => 'bg-blue-100 text-blue-700',
        SolicitudPagoDocente::ESTATUS_PAGADA => 'bg-green-100 text-green-700',
        SolicitudPagoDocente::ESTATUS_RECHAZADA => 'bg-red-100 text-red-700',
        default => 'bg-slate-200 text-slate-700',
    };
@endphp

@section('title', 'Solicitud de pago docente')

@section('content')
<div class="mx-auto mt-6 max-w-6xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div><h1 class="flex items-center gap-2 text-2xl font-semibold text-slate-800"><i class="bx bx-money-withdraw text-3xl text-blue-600"></i>{{ $solicitud->folio }}</h1><p class="mt-1 text-sm text-slate-500">Seguimiento Académica → CAdmin con fecha tentativa visible para informar al docente.</p></div>
            <div class="flex gap-2"><span class="rounded-xl px-4 py-2 text-sm font-bold {{ $badge }}">{{ $solicitud->estatus }}</span><a href="{{ route('solicitudes_pago.index') }}" class="rounded-xl bg-slate-200 px-4 py-2 font-medium text-slate-800">← Volver</a></div>
        </div>

        @if(session('success'))<div class="mb-4 rounded-lg border border-green-200 bg-green-100 px-4 py-3 text-green-700">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-4 rounded-lg border border-red-200 bg-red-100 px-4 py-3 text-red-700">{{ session('error') }}</div>@endif
        @if($solicitud->estatus === SolicitudPagoDocente::ESTATUS_OBSERVADA)<div class="mb-5 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-orange-800"><p class="font-bold">Devuelta a Académica</p><p>{{ $solicitud->motivo_observacion }}</p></div>@endif
        @if($solicitud->estatus === SolicitudPagoDocente::ESTATUS_RECHAZADA)<div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800"><p class="font-bold">CAdmin decidió no ejecutar el pago</p><p>{{ $solicitud->motivo_rechazo }}</p></div>@endif

        <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-3">
            <div class="rounded-xl border bg-slate-50 p-5"><p class="text-xs text-slate-500">Docente</p><p class="mt-1 text-lg font-semibold">{{ $solicitud->docente->nombre_completo }}</p></div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-5"><p class="text-xs text-blue-600">Fecha tentativa de pago</p><p class="mt-1 text-2xl font-bold text-blue-800">{{ $solicitud->fecha_tentativa_pago?->format('d/m/Y') ?? 'Por confirmar' }}</p><p class="mt-1 text-xs text-blue-700">Dato informativo para Académica; puede cambiar.</p></div>
            @if($veFinanzas)<div class="rounded-xl border bg-slate-50 p-5"><p class="text-xs text-slate-500">Monto autorizado</p><p class="mt-1 text-2xl font-bold text-green-700">${{ number_format($solicitud->monto, 2) }}</p><p class="text-xs text-slate-500">{{ $solicitud->esquema_pago ?: 'Sin valorar' }}</p></div>@else<div class="rounded-xl border bg-slate-50 p-5"><p class="text-xs text-slate-500">Tipo de clase</p><p class="mt-1 text-xl font-bold">{{ $solicitud->tipo_clase }}</p></div>@endif
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 p-5"><h2 class="mb-4 font-bold">Registro académico</h2><dl class="space-y-3 text-sm"><div><dt class="text-slate-500">Tipo de clase</dt><dd class="font-semibold">{{ $solicitud->tipo_clase }}</dd></div><div><dt class="text-slate-500">Actividad</dt><dd class="font-semibold">{{ $solicitud->materia_actividad }}</dd></div><div><dt class="text-slate-500">Programa / grupo</dt><dd class="font-semibold">{{ $solicitud->programa_grupo ?: '—' }}</dd></div><div><dt class="text-slate-500">Modalidad</dt><dd class="font-semibold">{{ $solicitud->modalidad ?: '—' }}</dd></div><div><dt class="text-slate-500">Horas reportadas</dt><dd class="font-semibold">{{ $solicitud->horas_totales ?: '—' }}</dd></div></dl></section>
            <section class="rounded-2xl border border-slate-200 p-5"><h2 class="mb-4 font-bold">Fechas impartidas ({{ count($solicitud->fechas_clase_ordenadas) }})</h2><div class="flex flex-wrap gap-2">@forelse($solicitud->fechas_clase_ordenadas as $fecha)<span class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>@empty<span class="text-sm text-slate-500">Sin fechas registradas.</span>@endforelse</div></section>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><h2 class="mb-3 font-bold">Observaciones de Académica</h2><p class="whitespace-pre-line text-sm">{{ $solicitud->observaciones_academica ?: '—' }}</p></section>
            <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><h2 class="mb-3 font-bold">Respuesta de CAdmin</h2><p class="whitespace-pre-line text-sm">{{ $solicitud->observaciones_administracion ?: '—' }}</p></section>
        </div>

        @if($veFinanzas)
        <section class="mt-6 rounded-2xl border border-slate-200 p-5"><h2 class="mb-4 font-bold">Información administrativa y de pago</h2><div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3"><p><span class="text-slate-500">Esquema</span><br><strong>{{ $solicitud->esquema_pago ?: '—' }}</strong></p><p><span class="text-slate-500">Tarifa unitaria</span><br><strong>{{ $solicitud->tarifa_unitaria ? '$'.number_format($solicitud->tarifa_unitaria,2) : '—' }}</strong></p><p><span class="text-slate-500">Valorado por</span><br><strong>{{ $solicitud->valoradoPor->nombre ?? '—' }}</strong></p><p><span class="text-slate-500">Fecha valoración</span><br><strong>{{ $solicitud->fecha_valoracion?->format('d/m/Y H:i') ?? '—' }}</strong></p><p><span class="text-slate-500">Fecha de pago</span><br><strong>{{ $solicitud->fecha_pago?->format('d/m/Y') ?? '—' }}</strong></p><p><span class="text-slate-500">Procesado por</span><br><strong>{{ $solicitud->procesadoPor->nombre ?? '—' }}</strong></p></div></section>
        @endif

        <div class="mt-6 flex flex-wrap gap-3 rounded-xl border border-slate-200 bg-slate-50 p-5">
            @if(in_array($rolActual,[Rol::ADMIN,Rol::ACADEMICA],true) && $solicitud->puedeEditarAcademica())<a href="{{ route('solicitudes_pago.edit',$solicitud) }}" class="rounded-lg bg-amber-500 px-4 py-2 text-white">Editar registro académico</a>@endif
            @if(in_array($rolActual,[Rol::ADMIN,Rol::CADMIN],true) && in_array($solicitud->estatus,[SolicitudPagoDocente::ESTATUS_PENDIENTE,SolicitudPagoDocente::ESTATUS_AUTORIZADA],true))<a href="{{ route('solicitudes_pago.valorar.form',$solicitud) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-white">{{ $solicitud->estatus === SolicitudPagoDocente::ESTATUS_AUTORIZADA ? 'Corregir valoración' : 'Valorar y programar' }}</a>@endif
            @if(in_array($rolActual,[Rol::ADMIN,Rol::CADMIN],true) && in_array($solicitud->estatus,[SolicitudPagoDocente::ESTATUS_PENDIENTE,SolicitudPagoDocente::ESTATUS_AUTORIZADA],true))<a href="{{ route('solicitudes_pago.observar.form',$solicitud) }}" class="rounded-lg bg-orange-500 px-4 py-2 text-white">Devolver con observaciones</a><a href="{{ route('solicitudes_pago.rechazar.form',$solicitud) }}" class="rounded-lg bg-red-600 px-4 py-2 text-white">No aprobar</a>@endif
            @if(in_array($rolActual,[Rol::ADMIN,Rol::CADMIN],true) && $solicitud->estatus===SolicitudPagoDocente::ESTATUS_AUTORIZADA)<a href="{{ route('solicitudes_pago.form_pagar',$solicitud) }}" class="rounded-lg bg-green-600 px-4 py-2 text-white">Registrar pago</a>@endif
            @if(in_array($rolActual,[Rol::ADMIN,Rol::CADMIN],true) && $solicitud->estatus===SolicitudPagoDocente::ESTATUS_PAGADA)<a href="{{ route('solicitudes_pago.acuse_pago',$solicitud) }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-white">Formato PDF</a>@endif
        </div>

        @if(in_array($rolActual,[Rol::ADMIN,Rol::CADMIN],true) && $solicitud->estatus===SolicitudPagoDocente::ESTATUS_AUTORIZADA)
            <form method="POST" action="{{ route('solicitudes_pago.tentativa',$solicitud) }}" class="mt-5 rounded-2xl border border-blue-200 bg-blue-50 p-5">
                @csrf @method('PUT')
                <h2 class="font-bold text-blue-900">Actualizar fecha tentativa</h2><p class="mb-3 text-xs text-blue-700">Académica recibirá una notificación automática y podrá informar al docente.</p>
                <div class="flex flex-col gap-3 md:flex-row"><input type="date" name="fecha_tentativa_pago" required min="{{ now()->format('Y-m-d') }}" value="{{ $solicitud->fecha_tentativa_pago?->format('Y-m-d') }}" class="rounded-xl border-blue-300 px-4 py-2"><input type="text" name="observaciones_administracion" maxlength="1500" value="{{ $solicitud->observaciones_administracion }}" placeholder="Motivo o comentario opcional" class="flex-1 rounded-xl border-blue-300 px-4 py-2"><button class="rounded-xl bg-blue-700 px-5 py-2 font-semibold text-white">Actualizar y notificar</button></div>
            </form>
        @endif
    </div>
</div>
@endsection
