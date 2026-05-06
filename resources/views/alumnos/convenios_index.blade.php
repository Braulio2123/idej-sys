@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">

        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            📜 Todos los Convenios de {{ $alumno->nombre_completo }}
        </h1>

        <a href="{{ route('alumnos.show', $alumno) }}"
           class="text-yellow-600 hover:underline mb-4 inline-block">← Regresar al resumen</a>

        @if($convenios->isEmpty())
            <p class="text-gray-500">No hay convenios registrados.</p>

        @else
            @foreach($convenios as $convenio)
                <div class="border rounded p-4 mb-6">

                    {{-- Encabezado --}}
                                        {{-- Encabezado --}}
                    <div class="flex items-start justify-between mb-2">

                        <div>
                            <p class="font-semibold">
                                Convenio #{{ $convenio->id }} — {{ $convenio->descripcion }}

                                @if($convenio->estatus === 'Finalizado')
                                    <span class="text-green-700 font-semibold">(Finalizado ✅)</span>
                                @else
                                    <span class="text-yellow-700 font-semibold">(Activo 🕓)</span>
                                @endif
                            </p>

                            <p class="text-gray-700 text-sm">
                                Total reestructurado:
                                <strong>${{ number_format($convenio->total_reestructurado, 2) }}</strong>
                                — {{ $convenio->numero_parcialidades }} parcialidades
                            </p>
                        </div>

                        <div class="flex flex-col gap-2 text-sm">

                            {{-- Ver parcialidades --}}
                            <a href="{{ route('parcialidades.index', $convenio) }}"
                            class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 shadow text-center">
                                Ver parcialidades →
                            </a>

                            {{-- Ver detalle del convenio --}}
                            <a href="{{ route('alumnos.convenios.show', [$alumno, $convenio]) }}"
                            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 shadow text-center">
                                Ver detalle →
                            </a>

                        </div>

                    </div>



                    {{-- Total y número de parcialidades --}}
                    <p>
                        Total reestructurado:
                        <strong>${{ number_format($convenio->total_reestructurado, 2) }}</strong> |
                        {{ $convenio->numero_parcialidades }} parcialidades
                    </p>

                    {{-- Tabla --}}
                    <table class="min-w-full text-sm mt-4 border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">#</th>
                                <th class="px-3 py-2 text-right">Monto</th>
                                <th class="px-3 py-2 text-center">Vencimiento</th>
                                <th class="px-3 py-2 text-center">Estatus</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($convenio->parcialidades as $i => $p)
                                <tr class="border-b">
                                    <td class="px-3 py-2">{{ $i + 1 }}</td>

                                    <td class="px-3 py-2 text-right">
                                        ${{ number_format($p->monto_parcialidad, 2) }}
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        {{ \Carbon\Carbon::parse($p->fecha_vencimiento)->format('d/m/Y') }}
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        @if($p->estatus === 'Pagado')
                                            <span class="text-green-700 font-semibold">Pagado</span>
                                        @else
                                            <span class="text-red-700 font-semibold">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            @endforeach

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $convenios->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
