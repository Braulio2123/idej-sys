@extends('layouts.app')

@php
    use App\Models\Rol;
    use App\Models\SolicitudPagoDocente;

    $rolActual = auth()->user()?->rolClave();

    $badge = function (?string $estatus) {
        return match ($estatus) {
            SolicitudPagoDocente::ESTATUS_PENDIENTE => 'bg-amber-100 text-amber-700',
            SolicitudPagoDocente::ESTATUS_OBSERVADA => 'bg-orange-100 text-orange-700',
            SolicitudPagoDocente::ESTATUS_AUTORIZADA => 'bg-blue-100 text-blue-700',
            SolicitudPagoDocente::ESTATUS_PAGADA => 'bg-green-100 text-green-700',
            SolicitudPagoDocente::ESTATUS_CANCELADA => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-700',
        };
    };
@endphp

@section('title', 'Solicitudes de Pago Docente')

@section('content')
<div class="max-w-7xl mx-auto mt-6 space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Pendientes</p>
            <p class="text-3xl font-bold text-amber-600">{{ $resumen['pendientes'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Observadas</p>
            <p class="text-3xl font-bold text-orange-600">{{ $resumen['observadas'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Autorizadas</p>
            <p class="text-3xl font-bold text-blue-600">{{ $resumen['autorizadas'] }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Pagado este mes</p>
            <p class="text-3xl font-bold text-green-700">${{ number_format($resumen['pagadas_mes'], 2) }}</p>
        </div>
    </div>

    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-money-withdraw text-3xl text-blue-600'></i>
                    Solicitudes de Pago a Docentes
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Flujo: Académica levanta → Administración/Finanzas autoriza → Administración/Finanzas paga.
                </p>
            </div>

            @if(in_array($rolActual, [Rol::ADMIN, Rol::ACADEMICA], true))
                <a href="{{ route('solicitudes_pago.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium shadow-md transition">
                    <i class='bx bx-file-plus text-xl'></i>
                    Nueva solicitud
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 border border-green-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 border border-red-200">{{ session('error') }}</div>
        @endif

        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-5 bg-slate-50 border border-slate-200 rounded-2xl p-4">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Folio, docente, materia..." class="rounded-xl border-slate-300 px-3 py-2 md:col-span-2">

            <select name="estatus" class="rounded-xl border-slate-300 px-3 py-2">
                <option value="">Todos los estatus</option>
                @foreach($estatuses as $estatus)
                    <option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ $estatus }}</option>
                @endforeach
            </select>

            <select name="origen" class="rounded-xl border-slate-300 px-3 py-2">
                <option value="">Todos los orígenes</option>
                @foreach($origenes as $origen)
                    <option value="{{ $origen }}" @selected(request('origen') === $origen)>{{ $origen }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button class="flex-1 rounded-xl bg-slate-800 text-white px-4 py-2 font-semibold">Filtrar</button>
                <a href="{{ route('solicitudes_pago.index') }}" class="rounded-xl bg-slate-200 text-slate-700 px-4 py-2 font-semibold">Limpiar</a>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="py-3 px-4 text-left">Folio / Servicio</th>
                        <th class="py-3 px-4 text-left">Docente</th>
                        <th class="py-3 px-4 text-left">Origen</th>
                        <th class="py-3 px-4 text-left">Monto</th>
                        <th class="py-3 px-4 text-left">Fechas</th>
                        <th class="py-3 px-4 text-left">Estatus</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($solicitudes as $solicitud)
                        <tr class="hover:bg-slate-50/70 transition align-top">
                            <td class="py-3 px-4">
                                <p class="font-bold text-slate-800">{{ $solicitud->folio ?? '#'.$solicitud->id }}</p>
                                <p class="text-slate-600">{{ $solicitud->materia_actividad ?: $solicitud->nivel }}</p>
                                @if($solicitud->programa_grupo)
                                    <p class="text-xs text-slate-500">{{ $solicitud->programa_grupo }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $solicitud->docente->nombre_completo ?? '—' }}</td>
                            <td class="py-3 px-4">
                                <p>{{ $solicitud->origen ?? 'Manual' }}</p>
                                <p class="text-xs text-slate-500">{{ $solicitud->concepto_pago ?? '—' }}</p>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">${{ number_format($solicitud->monto, 2) }}</td>
                            <td class="py-3 px-4 text-slate-600">
                                <p>Solicitud: {{ $solicitud->fecha_solicitud?->format('d/m/Y') ?? '—' }}</p>
                                <p>Límite: {{ $solicitud->fecha_limite_pago?->format('d/m/Y') ?? '—' }}</p>
                                <p>Pago: {{ $solicitud->fecha_pago?->format('d/m/Y') ?? '—' }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-3 py-1 rounded-lg text-xs font-semibold {{ $badge($solicitud->estatus) }}">{{ $solicitud->estatus }}</span>
                                @if($solicitud->prioridad && $solicitud->prioridad !== 'Normal')
                                    <p class="mt-2 text-xs font-bold text-red-600">{{ $solicitud->prioridad }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex flex-wrap justify-center gap-2">
                                    <a href="{{ route('solicitudes_pago.show', $solicitud) }}" class="text-blue-600 hover:text-blue-800 font-medium">Ver</a>

                                    @if(in_array($rolActual, [Rol::ADMIN, Rol::ACADEMICA], true) && $solicitud->puedeEditarAcademica())
                                        <a href="{{ route('solicitudes_pago.edit', $solicitud) }}" class="text-amber-600 hover:text-amber-800 font-medium">Editar</a>
                                    @endif

                                    @if(in_array($rolActual, [Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], true) && $solicitud->estatus === SolicitudPagoDocente::ESTATUS_AUTORIZADA)
                                        <a href="{{ route('solicitudes_pago.form_pagar', $solicitud) }}" class="text-green-600 hover:text-green-800 font-medium">Pagar</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">No se encontraron solicitudes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $solicitudes->links() }}</div>
    </div>
</div>
@endsection
