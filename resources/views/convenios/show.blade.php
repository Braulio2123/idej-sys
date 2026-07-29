@extends('layouts.app')

@section('content')

{{-- MENSAJES --}}
@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg shadow">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg shadow">
        {{ session('error') }}
    </div>
@endif
@if(session('info'))
    <div class="mb-4 p-4 bg-blue-100 text-blue-800 border border-blue-300 rounded-lg shadow">
        {{ session('info') }}
    </div>
@endif
@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg shadow">
        <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@php($convenioActivo = $convenio->estatus === 'Activo')
<div class="container mx-auto px-4 py-8">


    {{-- ============================== --}}
    {{-- ENCABEZADO DEL CONVENIO --}}
    {{-- ============================== --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8">

        <h2 class="text-3xl font-bold text-gray-900 mb-2">
            Convenio #{{ $convenio->id }}
        </h2>

        <p class="text-gray-600 text-lg mb-4">
            Alumno:
            <strong>{{ $alumno->nombre_completo }}</strong>
        </p>

        {{-- Estatus del convenio --}}
        <div class="mb-4">
            <span class="
                px-3 py-1 text-sm rounded font-semibold
                @if($convenio->estatus === 'Finalizado') bg-green-100 text-green-700
                @elseif($convenio->estatus === 'Cancelado') bg-red-100 text-red-700
                @else bg-yellow-100 text-yellow-700
                @endif
            ">
                Estatus: {{ $convenio->estatus }}
            </span>
        </div>

        @if($convenio->estatus === 'Cancelado')
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                <p class="font-bold">Convenio cancelado</p>
                <p class="text-sm mt-1">{{ $convenio->motivo_cancelacion ?: 'Sin motivo capturado.' }}</p>
                <p class="text-xs mt-2">Cancelado {{ $convenio->fecha_cancelacion?->format('d/m/Y H:i') ?? 'sin fecha' }} por {{ $convenio->canceladoPor->nombre ?? 'usuario no disponible' }}. Los cargos, parcialidades y vínculos se conservaron como historial.</p>
            </div>
        @endif

        {{-- TOTALES --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">

            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-lg shadow-sm">
                <p class="text-sm text-indigo-700 font-medium">Total Reestructurado</p>
                <p class="text-2xl font-bold text-indigo-800">
                    ${{ number_format($convenio->total_reestructurado, 2) }}
                </p>
            </div>

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg shadow-sm">
                <p class="text-sm text-blue-700 font-medium">Número de parcialidades</p>
                <p class="text-2xl font-bold text-blue-800">
                    {{ $convenio->numero_parcialidades }}
                </p>
            </div>

            <div class="p-4 bg-green-50 border border-green-200 rounded-lg shadow-sm">
                <p class="text-sm text-green-700 font-medium">Parcialidades Pagadas</p>
                <p class="text-2xl font-bold text-green-800">
                    {{ $convenio->parcialidades()->where('estatus','Pagado')->count() }}
                    /
                    {{ $convenio->parcialidades()->count() }}
                </p>
            </div>

        </div>


        {{-- BOTONES SUPERIORES --}}
        <div class="mt-6 flex flex-wrap gap-3">

            <a href="{{ route('alumnos.show', $alumno) }}"
               class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-300">
                ← Volver al Alumno
            </a>

            <a href="{{ route('alumnos.convenios.pdf', [$alumno, $convenio]) }}" target="_blank"
               class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-900">
                Formato PDF
            </a>

            <a href="{{ route('parcialidades.index', $convenio) }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
                Ver Parcialidades
            </a>

            @if($convenioActivo && !$tienePagosAplicados)
                <a href="{{ route('parcialidades.create', $convenio) }}"
                   class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                    + Crear Parcialidad
                </a>

                <a href="{{ route('alumnos.convenios.edit', [$alumno, $convenio]) }}"
                   class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-700">
                    Editar Convenio
                </a>
            @endif

        </div>

        @if($convenioActivo && !$tienePagosAplicados)
            <details class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
                <summary class="cursor-pointer font-semibold text-red-700">Cancelar convenio y reactivar cargos</summary>
                <form action="{{ route('alumnos.convenios.destroy', [$alumno, $convenio]) }}" method="POST" class="mt-3 space-y-3" data-confirm="¿Cancelar este convenio? Se conservarán parcialidades y vínculos, y se reactivarán los cargos originales.">
                    @csrf
                    @method('DELETE')
                    <textarea name="motivo_cancelacion" required minlength="10" maxlength="3000" rows="3" class="w-full rounded-lg border-red-300" placeholder="Explica el motivo administrativo de la cancelación"></textarea>
                    <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">Confirmar cancelación</button>
                </form>
            </details>
        @elseif($convenioActivo && $tienePagosAplicados)
            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Este convenio ya tiene pagos aplicados. No puede cancelarse ni cambiar su calendario desde esta pantalla; cualquier corrección requiere un ajuste administrativo documentado.
            </div>
        @endif

    </div>




    {{-- ============================== --}}
    {{-- CARGOS INCLUIDOS EN EL CONVENIO --}}
    {{-- ============================== --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">
            Cargos incluidos en el convenio
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border rounded-lg overflow-hidden">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold">Concepto</th>
                        <th class="px-4 py-2 text-left font-semibold">Descripción</th>
                        <th class="px-4 py-2 text-right font-semibold">Monto original</th>
                        <th class="px-4 py-2 text-right font-semibold">Adeudo reestructurado</th>
                        <th class="px-4 py-2 text-center font-semibold">Estatus anterior</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($convenio->cargos as $cargo)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $cargo->concepto->nombre ?? 'Concepto' }}</td>
                            <td class="px-4 py-2">{{ $cargo->descripcion_cargo }}</td>
                            <td class="px-4 py-2 text-right">${{ number_format($cargo->pivot->monto_original, 2) }}</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format($cargo->pivot->monto_adeudo_original, 2) }}</td>
                            <td class="px-4 py-2 text-center">{{ $cargo->pivot->estatus_original }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                                Este convenio fue creado antes de registrar la relación múltiple de cargos. Revisa el cargo original asociado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================== --}}
    {{-- TABLA DETALLADA DE PARCIALIDADES --}}
    {{-- ============================== --}}
    <div class="bg-white shadow-lg rounded-xl p-6">

        <h3 class="text-2xl font-bold text-gray-800 mb-6">
            Parcialidades del Convenio
        </h3>

        <div class="overflow-x-auto">

            <table class="w-full text-sm border rounded-lg overflow-hidden">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold">#</th>
                        <th class="px-4 py-2 text-right font-semibold">Monto</th>
                        <th class="px-4 py-2 text-right font-semibold">Adeudo</th>
                        <th class="px-4 py-2 text-center font-semibold">Vencimiento</th>
                        <th class="px-4 py-2 text-center font-semibold">Estatus</th>
                        <th class="px-4 py-2 text-right font-semibold"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($convenio->parcialidades as $i => $p)
                        <tr class="border-b hover:bg-gray-50">

                            <td class="px-4 py-2">{{ $i + 1 }}</td>

                            <td class="px-4 py-2 text-right">
                                ${{ number_format($p->monto_parcialidad, 2) }}
                            </td>

                            <td class="px-4 py-2 text-right">
                                ${{ number_format($p->monto_adeudo, 2) }}
                            </td>

                            <td class="px-4 py-2 text-center">
                                {{ $p->fecha_vencimiento->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-2 text-center">

                                @if($p->estatus === 'Pagado')
                                    <span class="text-green-700 font-semibold">Pagado</span>
                                @elseif($p->estatus === 'Parcialmente Pagado')
                                    <span class="text-yellow-700 font-semibold">Parcialmente</span>
                                @elseif($p->fecha_vencimiento && $p->fecha_vencimiento->isPast())
                                    <span class="text-red-700 font-semibold">Vencida</span>
                                @else
                                    <span class="text-red-700 font-semibold">Pendiente</span>
                                @endif

                            </td>

                            <td class="px-4 py-2 text-right space-x-2">

                                @if($convenioActivo && !$tienePagosAplicados && $p->estatus !== 'Cancelada')
                                    <a href="{{ route('parcialidades.edit', [$convenio, $p]) }}"
                                       class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Editar
                                    </a>
                                @else
                                    <span class="text-slate-400">Solo consulta</span>
                                @endif

                                <span class="text-xs text-slate-500">Las parcialidades forman parte del historial contractual.</span>

                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
