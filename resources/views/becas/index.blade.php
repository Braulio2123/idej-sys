@extends('layouts.app')

@section('title', 'Becas Institucionales')

@section('content')
@php
    use App\Models\Rol;
    $puedeGestionarBecas = Auth::user()?->tieneRol(Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS) ?? false;
@endphp
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-blue-700 font-semibold">Finanzas</p>
            <h1 class="text-3xl font-bold text-slate-900">Becas institucionales</h1>
            <p class="text-slate-600 mt-1">Consulta becas activas, programadas, vencidas o canceladas.</p>
        </div>

        @if($puedeGestionarBecas)
            <form method="POST" action="{{ route('becas.sincronizar') }}" onsubmit="return confirm('¿Sincronizar el estado actual de becas en todos los alumnos?');">
                @csrf
                <button class="bg-slate-900 text-white px-4 py-2 rounded-xl hover:bg-slate-800 font-semibold shadow">
                    Sincronizar becas
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow p-5 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Buscar alumno</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, matrícula o correo" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus</label>
                <select name="estatus" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    @foreach($estatusDisponibles as $estatus)
                        <option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-xl font-semibold">Filtrar</button>
                <a href="{{ route('becas.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-900 text-white uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Alumno</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-center">%</th>
                        <th class="px-4 py-3 text-left">Vigencia</th>
                        <th class="px-4 py-3 text-left">Estatus</th>
                        <th class="px-4 py-3 text-left">Autorizó</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($becas as $beca)
                        <tr class="border-b hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('alumnos.show', $beca->alumno) }}" class="font-bold text-blue-700 hover:underline">
                                    {{ $beca->alumno->nombre_completo ?? 'Alumno no disponible' }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $beca->alumno->matricula ?? 'Sin matrícula' }} · {{ $beca->alumno->grupo->programa->nombre ?? 'Sin programa' }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $beca->tipo }}</td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-700">{{ $beca->porcentaje }}%</td>
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
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('alumnos.becas.index', $beca->alumno) }}" class="text-blue-700 hover:underline font-semibold">Ver expediente</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay becas registradas con los filtros actuales.</td>
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
