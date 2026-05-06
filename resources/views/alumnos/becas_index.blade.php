@extends('layouts.app')

@section('title', 'Becas del Alumno')

@section('content')
@php
    use App\Models\Rol;
    $usuarioActual = Auth::user();
    $puedeGestionarBecas = $usuarioActual?->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS) ?? false;
@endphp

<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('alumnos.show', $alumno) }}" class="text-blue-700 hover:underline font-semibold">← Volver al expediente</a>
            <h1 class="text-3xl font-bold text-slate-900 mt-2">Becas de {{ $alumno->nombre_completo }}</h1>
            <p class="text-slate-600">Matrícula: {{ $alumno->matricula }} · Beca vigente reflejada en alumno: <strong>{{ $alumno->beca_porcentaje }}%</strong></p>
        </div>

        @if($puedeGestionarBecas)
            <a href="{{ route('alumnos.becas.create', $alumno) }}" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-xl font-semibold shadow text-center">
                + Registrar beca
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Condición</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ $alumno->condicion_alumno }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">% actual</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $alumno->beca_porcentaje }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Becas activas</p>
            <p class="mt-2 text-3xl font-bold text-blue-700">{{ $alumno->becas()->activas()->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Descuento histórico en cargos</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">${{ number_format($alumno->cargos()->sum('beca_monto_aplicado'), 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-900 text-white uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Tipo / motivo</th>
                        <th class="px-4 py-3 text-center">%</th>
                        <th class="px-4 py-3 text-left">Vigencia</th>
                        <th class="px-4 py-3 text-left">Estatus</th>
                        <th class="px-4 py-3 text-left">Autorizó</th>
                        <th class="px-4 py-3 text-left">Cargos aplicados</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($becas as $beca)
                        <tr class="border-b hover:bg-slate-50 align-top">
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-900">{{ $beca->tipo }}</p>
                                <p class="text-slate-600">{{ $beca->motivo }}</p>
                                @if($beca->observaciones)
                                    <p class="text-xs text-slate-500 mt-1">{{ $beca->observaciones }}</p>
                                @endif
                                @if($beca->estaCancelada())
                                    <p class="text-xs text-red-700 mt-1">Cancelada por: {{ $beca->canceladoPor->nombre ?? 'N/A' }} · {{ $beca->fecha_cancelacion?->format('d/m/Y H:i') }}</p>
                                    <p class="text-xs text-red-700">Motivo: {{ $beca->motivo_cancelacion }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-700 text-lg">{{ $beca->porcentaje }}%</td>
                            <td class="px-4 py-3">
                                {{ $beca->fecha_inicio?->format('d/m/Y') }}
                                <span class="text-slate-400">→</span>
                                {{ $beca->fecha_fin?->format('d/m/Y') ?? 'Indefinida' }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $color = match($beca->estatus) {
                                        'Activa' => 'bg-green-100 text-green-700',
                                        'Programada' => 'bg-blue-100 text-blue-700',
                                        'Vencida' => 'bg-amber-100 text-amber-700',
                                        'Cancelada' => 'bg-red-100 text-red-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $color }}">{{ $beca->estatus }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $beca->autorizadoPor->nombre ?? 'No especificado' }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold">{{ $beca->cargos->count() }} cargo(s)</p>
                                <p class="text-xs text-slate-500">Descuento: ${{ number_format($beca->cargos->sum('beca_monto_aplicado'), 2) }}</p>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($puedeGestionarBecas && ! $beca->estaCancelada())
                                    <a href="{{ route('alumnos.becas.cancelar.confirmar', [$alumno, $beca]) }}" class="text-red-600 hover:underline font-semibold">Cancelar</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Este alumno aún no tiene becas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $becas->links() }}
        </div>
    </div>
</div>
@endsection
