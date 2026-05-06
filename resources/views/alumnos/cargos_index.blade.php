@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-6">

    <!-- CARD PRINCIPAL -->
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        <!-- TÍTULO -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800 flex items-center gap-2">
                    <i class='bx bx-layer text-3xl text-blue-600'></i>
                    Cargos de {{ $alumno->nombre_completo }}
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Historial completo de cargos generados para este alumno
                </p>
            </div>

            <a href="{{ route('alumnos.show', $alumno) }}"
               class="inline-flex items-center gap-2 text-sm bg-slate-100 hover:bg-slate-200
                      text-slate-700 px-4 py-2 rounded-xl transition shadow-sm">
                <i class='bx bx-arrow-back text-lg'></i>
                Regresar al resumen
            </a>
        </div>

        {{-- Si no hay cargos --}}
        @if ($cargos->isEmpty())
            <div class="text-center py-10 text-slate-500">
                <i class='bx bx-info-circle text-4xl text-slate-400 mb-2'></i>
                <p class="text-sm">No hay cargos registrados para este alumno.</p>
            </div>

        @else

            <!-- TABLA -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 shadow">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">ID</th>
                            <th class="px-4 py-3 text-left font-semibold">Concepto</th>
                            <th class="px-4 py-3 text-left font-semibold">Descripción</th>
                            <th class="px-4 py-3 text-left font-semibold">Monto Original</th>
                            <th class="px-4 py-3 text-left font-semibold">Beca Aplicada</th>
                            <th class="px-4 py-3 text-left font-semibold">Monto Adeudo</th>
                            <th class="px-4 py-3 text-left font-semibold">Estatus</th>
                            <th class="px-4 py-3 text-left font-semibold">Vencimiento</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($cargos as $cargo)

                            <tr class="hover:bg-slate-50/70 transition">

                                <td class="px-4 py-3 text-slate-800 font-medium">
                                    {{ $cargo->id }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $cargo->concepto->nombre ?? 'N/A' }}
                                </td>

                                <td class="px-4 py-3 text-slate-600">
                                    {{ $cargo->descripcion_cargo }}
                                </td>

                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    ${{ number_format($cargo->monto_original, 2) }}
                                </td>

                                <td class="px-4 py-3">
                                    @if(($cargo->beca_porcentaje_aplicado ?? 0) > 0)
                                        <span class="font-bold text-emerald-700">{{ $cargo->beca_porcentaje_aplicado }}%</span>
                                        <p class="text-xs text-slate-500">-${{ number_format($cargo->beca_monto_aplicado, 2) }}</p>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 font-semibold text-blue-600">
                                    ${{ number_format($cargo->monto_adeudo, 2) }}
                                </td>

                                <td class="px-4 py-3">
                                    @if($cargo->estatus === 'Pagado')
                                        <span class="bg-green-100 text-green-700 font-semibold text-xs px-3 py-1 rounded-lg">
                                            Pagado
                                        </span>
                                    @elseif($cargo->estatus === 'Pendiente')
                                        <span class="bg-amber-100 text-amber-700 font-semibold text-xs px-3 py-1 rounded-lg">
                                            Pendiente
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-700 font-semibold text-xs px-3 py-1 rounded-lg">
                                            {{ $cargo->estatus }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-slate-700">
                                    {{ \Carbon\Carbon::parse($cargo->fecha_vencimiento)->format('d/m/Y') }}
                                </td>

                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-6">
                {{ $cargos->links() }}
            </div>

        @endif

    </div>
</div>

@endsection
