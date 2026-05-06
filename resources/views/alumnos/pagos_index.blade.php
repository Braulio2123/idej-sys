@extends('layouts.app')

@section('content')
@php
    use App\Models\Rol;
    use Illuminate\Support\Str;

    $usuarioActual = Auth::user();
    $puedeCancelarPagos = $usuarioActual?->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS) ?? false;
@endphp
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">💵 Todos los Pagos</h1>
                <p class="text-sm text-gray-500 mt-1">Alumno: <strong>{{ $alumno->nombre_completo }}</strong></p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('alumnos.pagos.create', $alumno) }}"
                   class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 font-medium">
                    Registrar pago
                </a>
                <a href="{{ route('alumnos.show', $alumno) }}"
                   class="text-green-600 hover:underline font-medium self-center">
                    ← Regresar al resumen
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        @endif

        @if($pagos->isEmpty())
            <p class="text-gray-500">No hay pagos registrados.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-700">
                    <thead class="bg-green-600 text-white uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Folio</th>
                            <th class="px-4 py-3">Monto</th>
                            <th class="px-4 py-3">Método</th>
                            <th class="px-4 py-3">Referencia</th>
                            <th class="px-4 py-3">Comprobante</th>
                            <th class="px-4 py-3">Estatus</th>
                            <th class="px-4 py-3">Recibo</th>
                            <th class="px-4 py-3">Acciones</th>
                            <th class="px-4 py-3">Registrado por</th>
                            <th class="px-4 py-3">Corte</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagos as $pago)
                            <tr class="border-b hover:bg-gray-50 {{ $pago->estaCancelado() ? 'bg-red-50 text-slate-500' : '' }}">
                                <td class="px-4 py-3">{{ $pago->id }}</td>
                                <td class="px-4 py-3">{{ optional($pago->fecha_pago)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $pago->folio_recibo ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold {{ $pago->estaCancelado() ? 'line-through' : '' }}">${{ number_format($pago->monto_total_pagado, 2) }}</td>
                                <td class="px-4 py-3">{{ $pago->metodo_pago }}</td>
                                <td class="px-4 py-3">{{ $pago->referencia_principal ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($pago->archivo_comprobante)
                                        <a href="{{ asset('storage/' . $pago->archivo_comprobante) }}" target="_blank" class="text-indigo-600 hover:underline">
                                            Ver archivo
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($pago->estaCancelado())
                                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Cancelado</span>
                                        <div class="text-xs text-slate-500 mt-1">{{ optional($pago->fecha_cancelacion)->format('d/m/Y H:i') }}</div>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Activo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('alumnos.pagos.recibo', [$alumno, $pago]) }}" target="_blank" class="inline-flex items-center px-3 py-1 rounded bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900">
                                        PDF
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    @if($puedeCancelarPagos && ! $pago->estaCancelado() && $pago->corteCaja?->estaAbierta())
                                        <a href="{{ route('alumnos.pagos.cancelar.confirmar', [$alumno, $pago]) }}" class="inline-flex items-center px-3 py-1 rounded bg-red-600 text-white text-xs font-semibold hover:bg-red-700">Cancelar</a>
                                    @elseif($puedeCancelarPagos && ! $pago->estaCancelado() && $pago->corteCaja?->estaCerrada())
                                        <a href="{{ route('alumnos.pagos.ajuste-cancelacion.confirmar', [$alumno, $pago]) }}" class="inline-flex items-center px-3 py-1 rounded bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700">Ajuste</a>
                                    @elseif($pago->estaCancelado())
                                        <span class="text-xs text-slate-500">{{ Str::limit($pago->motivo_cancelacion, 45) }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $pago->usuario->nombre ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    @if($pago->corteCaja)
                                        <a href="{{ route('cortes-caja.show', $pago->corteCaja) }}" class="text-indigo-600 hover:underline font-semibold">#{{ $pago->corteCaja->id }}</a>
                                    @else
                                        <span class="text-slate-400">Sin corte</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $pagos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
