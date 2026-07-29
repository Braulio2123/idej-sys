@extends('layouts.app')

@section('content')

{{-- Mensajes --}}
@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg shadow">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg shadow">
        {{ session('error') }}
    </div>
@endif

{{-- ENCABEZADO --}}
<div class="bg-white shadow-lg rounded-lg p-6 mb-6">

    <h2 class="text-2xl font-bold text-gray-800 mb-2">
        Parcialidades del Convenio #{{ $convenio->id }}
    </h2>

    <p class="text-gray-600">
        Alumno:
        <strong>{{ $alumno->nombre_completo }}</strong>
    </p>

    <p class="text-gray-600">
        Total Reestructurado:
        <strong>${{ number_format($convenio->total_reestructurado, 2) }}</strong>
    </p>
</div>

@if($convenio->estatus !== 'Activo')
    <div class="mb-5 rounded-lg border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
        Este convenio está <strong>{{ strtolower($convenio->estatus) }}</strong>. Sus parcialidades se conservan como historial y no pueden modificarse.
    </div>
@elseif($tienePagosAplicados)
    <div class="mb-5 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
        El convenio ya tiene pagos aplicados. Para conservar la trazabilidad contractual, su calendario de parcialidades quedó bloqueado.
    </div>
@endif

{{-- BOTONES SUPERIORES --}}
<div class="flex justify-between mb-4">

    <a href="{{ route('alumnos.show', $alumno) }}"
       class="text-gray-700 hover:underline">
        ← Volver al Alumno
    </a>

    @if($convenio->estatus === 'Activo' && ! $tienePagosAplicados)
        <a href="{{ route('parcialidades.create', $convenio) }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 shadow">
            + Crear Parcialidad
        </a>
    @else
        <span class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
            Calendario contractual en solo consulta
        </span>
    @endif

</div>


{{-- TABLA --}}
<div class="bg-white shadow-lg rounded-lg overflow-hidden overflow-x-auto">

    <table class="min-w-full whitespace-nowrap">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-4 py-3 text-left text-gray-600 font-semibold">#</th>
                <th class="px-4 py-3 text-left text-gray-600 font-semibold">Monto</th>
                <th class="px-4 py-3 text-left text-gray-600 font-semibold">Adeudo</th>
                <th class="px-4 py-3 text-left text-gray-600 font-semibold">Vencimiento</th>
                <th class="px-4 py-3 text-left text-gray-600 font-semibold">Estatus</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>

        <tbody>
            @foreach($parcialidades as $p)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $loop->iteration }}</td>

                    <td class="px-4 py-3">
                        ${{ number_format($p->monto_parcialidad, 2) }}
                    </td>

                    <td class="px-4 py-3">
                        ${{ number_format($p->monto_adeudo, 2) }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $p->fecha_vencimiento->format('d/m/Y') }}
                    </td>

                    <td class="px-4 py-3">
                        <span class="
                            px-3 py-1 rounded text-white text-sm
                            {{ $p->estatus === 'Pagado' ? 'bg-green-600' :
                               ($p->estatus === 'Parcialmente Pagado' ? 'bg-yellow-500' :
                               ($p->estatus === 'Cancelada' ? 'bg-slate-500' : 'bg-red-600')) }}
                        ">
                            {{ $p->estatus }}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-right space-x-2">

                        @if($convenio->estatus === 'Activo' && ! $tienePagosAplicados && $p->estatus !== 'Cancelada')
                            <a href="{{ route('parcialidades.edit', [$convenio, $p]) }}"
                               class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                Editar
                            </a>
                        @else
                            <span class="text-slate-400">Solo consulta</span>
                        @endif

                        <span class="text-xs text-slate-500">No eliminable: historial contractual</span>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection
