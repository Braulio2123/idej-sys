@extends('layouts.app')

@php
    use App\Models\Rol;
    use App\Models\SolicitudPagoDocente;
    $rolActual = auth()->user()?->rolClave();
    $veFinanzas = in_array($rolActual, [Rol::ADMIN, Rol::CADMIN, Rol::DIRECCION], true);
    $badge = fn ($estatus) => match ($estatus) {
        SolicitudPagoDocente::ESTATUS_PENDIENTE => 'bg-amber-100 text-amber-700',
        SolicitudPagoDocente::ESTATUS_OBSERVADA => 'bg-orange-100 text-orange-700',
        SolicitudPagoDocente::ESTATUS_AUTORIZADA => 'bg-blue-100 text-blue-700',
        SolicitudPagoDocente::ESTATUS_PAGADA => 'bg-green-100 text-green-700',
        SolicitudPagoDocente::ESTATUS_RECHAZADA => 'bg-red-100 text-red-700',
        SolicitudPagoDocente::ESTATUS_CANCELADA => 'bg-slate-200 text-slate-700',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

@section('title', 'Solicitudes de Pago Docente')

@section('content')
<div class="mx-auto mt-6 max-w-7xl space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs uppercase text-slate-500">Pendientes de valoración</p><p class="text-3xl font-bold text-amber-600">{{ $resumen['pendientes'] }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs uppercase text-slate-500">Observadas</p><p class="text-3xl font-bold text-orange-600">{{ $resumen['observadas'] }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs uppercase text-slate-500">Programadas</p><p class="text-3xl font-bold text-blue-600">{{ $resumen['autorizadas'] }}</p></div>
        @if($veFinanzas)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs uppercase text-slate-500">Pagado este mes</p><p class="text-3xl font-bold text-green-700">${{ number_format($resumen['pagadas_mes'], 2) }}</p></div>@endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-semibold text-slate-800"><i class="bx bx-money-withdraw text-3xl text-blue-600"></i> Solicitudes de pago a docentes</h1>
                <p class="mt-1 text-xs text-slate-500">Académica registra clases impartidas → CAdmin valora y programa → CAdmin paga o rechaza.</p>
            </div>
            @if(in_array($rolActual, [Rol::ADMIN, Rol::ACADEMICA], true))
                <a href="{{ route('solicitudes_pago.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 font-medium text-white shadow hover:bg-blue-700"><i class="bx bx-file-plus text-xl"></i>Nueva solicitud</a>
            @endif
        </div>

        @if(session('success'))<div class="mb-4 rounded-lg border border-green-200 bg-green-100 px-4 py-3 text-green-700">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-4 rounded-lg border border-red-200 bg-red-100 px-4 py-3 text-red-700">{{ session('error') }}</div>@endif

        <form method="GET" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-5">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Folio, docente, actividad..." class="rounded-xl border-slate-300 px-3 py-2 md:col-span-2">
            <select name="estatus" class="rounded-xl border-slate-300 px-3 py-2"><option value="">Todos los estados</option>@foreach($estatuses as $estatus)<option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ $estatus }}</option>@endforeach</select>
            <select name="tipo_clase" class="rounded-xl border-slate-300 px-3 py-2"><option value="">Todos los tipos</option>@foreach($tiposClase as $tipo)<option value="{{ $tipo }}" @selected(request('tipo_clase') === $tipo)>{{ $tipo }}</option>@endforeach</select>
            <div class="flex gap-2"><button class="flex-1 rounded-xl bg-slate-800 px-4 py-2 font-semibold text-white">Filtrar</button><a href="{{ route('solicitudes_pago.index') }}" class="rounded-xl bg-slate-200 px-4 py-2 font-semibold text-slate-700">Limpiar</a></div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-600"><tr><th class="px-4 py-3 text-left">Solicitud</th><th class="px-4 py-3 text-left">Docente</th><th class="px-4 py-3 text-left">Clases impartidas</th>@if($veFinanzas)<th class="px-4 py-3 text-left">Valoración</th>@endif<th class="px-4 py-3 text-left">Tentativa de pago</th><th class="px-4 py-3 text-left">Estado</th><th class="px-4 py-3 text-center">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($solicitudes as $solicitud)
                    <tr class="align-top hover:bg-slate-50">
                        <td class="px-4 py-3"><p class="font-bold text-slate-800">{{ $solicitud->folio }}</p><p>{{ $solicitud->materia_actividad }}</p><p class="text-xs text-slate-500">{{ $solicitud->programa_grupo ?: 'Sin grupo relacionado' }}</p></td>
                        <td class="px-4 py-3 font-medium">{{ $solicitud->docente->nombre_completo ?? '—' }}</td>
                        <td class="px-4 py-3"><p class="font-semibold">{{ $solicitud->tipo_clase }}</p><p class="text-xs text-slate-500">{{ count($solicitud->fechas_clase_ordenadas) }} fecha(s)</p><p class="text-xs text-slate-500">{{ $solicitud->fecha_inicio_periodo?->format('d/m/Y') }} — {{ $solicitud->fecha_fin_periodo?->format('d/m/Y') }}</p></td>
                        @if($veFinanzas)<td class="px-4 py-3"><p class="font-bold">${{ number_format($solicitud->monto, 2) }}</p><p class="text-xs text-slate-500">{{ $solicitud->esquema_pago ?: 'Sin valorar' }}</p></td>@endif
                        <td class="px-4 py-3"><p class="font-semibold {{ $solicitud->fecha_tentativa_pago ? 'text-blue-700' : 'text-slate-500' }}">{{ $solicitud->fecha_tentativa_pago?->format('d/m/Y') ?? 'Por confirmar' }}</p>@if($solicitud->fecha_pago)<p class="text-xs text-green-700">Pagado: {{ $solicitud->fecha_pago->format('d/m/Y') }}</p>@endif</td>
                        <td class="px-4 py-3"><span class="rounded-lg px-3 py-1 text-xs font-semibold {{ $badge($solicitud->estatus) }}">{{ $solicitud->estatus }}</span></td>
                        <td class="px-4 py-3"><div class="flex flex-wrap justify-center gap-2"><a href="{{ route('solicitudes_pago.show', $solicitud) }}" class="font-medium text-blue-600">Ver</a>@if(in_array($rolActual,[Rol::ADMIN,Rol::ACADEMICA],true) && $solicitud->puedeEditarAcademica())<a href="{{ route('solicitudes_pago.edit',$solicitud) }}" class="font-medium text-amber-600">Editar</a>@endif @if(in_array($rolActual,[Rol::ADMIN,Rol::CADMIN],true) && $solicitud->estatus===SolicitudPagoDocente::ESTATUS_PENDIENTE)<a href="{{ route('solicitudes_pago.valorar.form',$solicitud) }}" class="font-medium text-indigo-600">Valorar</a>@endif @if(in_array($rolActual,[Rol::ADMIN,Rol::CADMIN],true) && $solicitud->estatus===SolicitudPagoDocente::ESTATUS_AUTORIZADA)<a href="{{ route('solicitudes_pago.form_pagar',$solicitud) }}" class="font-medium text-green-600">Pagar</a>@endif</div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No se encontraron solicitudes.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $solicitudes->links() }}</div>
    </div>
</div>
@endsection
