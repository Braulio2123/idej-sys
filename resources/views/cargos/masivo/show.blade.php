@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- ENCABEZADO --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">
            📄 Resumen de Cargo Masivo #{{ $cargoMasivo->id }}
        </h1>

        <a href="{{ route('cargos.masivo.index') }}"
           class="text-indigo-600 hover:underline mb-4 inline-block">
            ← Regresar a Cargos Masivos
        </a>

        {{-- DATOS DEL OPERATIVO --}}
        <div class="grid md:grid-cols-2 gap-6">

            {{-- IZQUIERDA --}}
            <div>
                <p class="mb-2"><strong>Concepto:</strong><br>
                    <span class="text-gray-700">{{ $cargoMasivo->concepto->nombre }}</span>
                </p>

                <p class="mb-2"><strong>Monto aplicado:</strong><br>
                    <span class="text-green-700 font-semibold">${{ number_format($cargoMasivo->monto, 2) }}</span>
                </p>

                <p class="mb-2"><strong>Fecha de vencimiento:</strong><br>
                    <span class="text-gray-700">
                        {{ \Carbon\Carbon::parse($cargoMasivo->fecha_vencimiento)->format('d/m/Y') }}
                    </span>
                </p>
            </div>

            {{-- DERECHA --}}
            <div>
                <p class="mb-2"><strong>Descripción:</strong><br>
                    <span class="text-gray-700">{{ $cargoMasivo->descripcion }}</span>
                </p>

                <p class="mb-2"><strong>Total de alumnos afectados:</strong><br>
                    <span class="text-blue-700 font-semibold">
                        {{ $cargoMasivo->total_alumnos }}
                    </span>
                </p>

                <p class="mb-2"><strong>Registrado por:</strong><br>
                    <span class="text-gray-700">
                        {{ $cargoMasivo->usuario->nombre ?? 'Usuario eliminado' }}
                    </span>
                </p>

                <p class="mb-2"><strong>Fecha de operación:</strong><br>
                    <span class="text-gray-700">
                        {{ $cargoMasivo->created_at->format('d/m/Y H:i') }}
                    </span>
                </p>
            </div>

        </div>
    </div>

    {{-- TABLA DE ALUMNOS --}}
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            👥 Alumnos afectados por este cargo masivo
        </h2>

        @if($alumnos->isEmpty())
            <p class="text-gray-500">No se encontraron alumnos registrados.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="py-2 px-3 border-b text-left">Matrícula</th>
                            <th class="py-2 px-3 border-b text-left">Nombre</th>
                            <th class="py-2 px-3 border-b text-left">Grupo</th>
                            <th class="py-2 px-3 border-b text-left">Estatus Financiero</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumnos as $alumno)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 border-b">{{ $alumno->matricula }}</td>
                                <td class="py-2 px-3 border-b">{{ $alumno->nombre_completo }}</td>
                                <td class="py-2 px-3 border-b">
                                    {{ $alumno->grupo->nombre ?? '—' }}
                                </td>
                                <td class="py-2 px-3 border-b">
                                    @if($alumno->estatus_financiero === 'Con Adeudo')
                                        <span class="text-red-600 font-semibold">Con Adeudo</span>
                                    @else
                                        <span class="text-green-600 font-semibold">Al Corriente</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            <div class="mt-4">
                {{ $alumnos->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
