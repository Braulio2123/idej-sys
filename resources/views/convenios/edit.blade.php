@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6 mt-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Editar Convenio</h1>
    <p class="text-sm text-gray-600 mb-5">
        Solo se permite modificar la descripción. El total reestructurado y las parcialidades no se editan desde aquí porque ya forman parte del historial financiero del alumno.
    </p>

    @if($errors->any())
        <div class="mb-4 p-4 rounded-lg border border-red-200 bg-red-50 text-red-700">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-lg bg-indigo-50 border border-indigo-100">
            <p class="text-xs text-indigo-700 font-semibold">Total reestructurado</p>
            <p class="text-xl font-bold text-indigo-900">${{ number_format($convenio->total_reestructurado, 2) }}</p>
        </div>
        <div class="p-4 rounded-lg bg-blue-50 border border-blue-100">
            <p class="text-xs text-blue-700 font-semibold">Parcialidades</p>
            <p class="text-xl font-bold text-blue-900">{{ $convenio->numero_parcialidades }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
            <p class="text-xs text-gray-700 font-semibold">Estatus</p>
            <p class="text-xl font-bold text-gray-900">{{ $convenio->estatus }}</p>
        </div>
    </div>

    <form action="{{ route('alumnos.convenios.update', [$alumno, $convenio]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold" for="descripcion">Descripción</label>
            <input type="text" id="descripcion" name="descripcion" value="{{ old('descripcion', $convenio->descripcion) }}" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-2">Cargos relacionados</h2>
            <div class="border rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Concepto</th>
                            <th class="px-3 py-2 text-left">Descripción</th>
                            <th class="px-3 py-2 text-right">Adeudo original</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($convenio->cargos as $cargo)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $cargo->concepto->nombre ?? 'Concepto' }}</td>
                                <td class="px-3 py-2">{{ $cargo->descripcion_cargo }}</td>
                                <td class="px-3 py-2 text-right">${{ number_format($cargo->pivot->monto_adeudo_original, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-3 text-center text-gray-500">Este convenio no tiene cargos relacionados en la tabla pivote.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('alumnos.convenios.show', [$alumno, $convenio]) }}" class="text-gray-600 hover:underline">← Volver</a>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
