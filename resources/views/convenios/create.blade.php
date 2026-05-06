@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-white shadow-md rounded-lg p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Crear Convenio de Pago</h2>
        <p class="text-sm text-gray-600 mt-1">
            Alumno: <strong>{{ $alumno->nombre_completo }}</strong>. Selecciona únicamente los cargos que serán reestructurados en parcialidades.
        </p>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 rounded-lg border border-red-200 bg-red-50 text-red-700">
            <ul class="list-disc pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alumnos.convenios.store', $alumno) }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="descripcion" class="block text-sm font-medium text-gray-700">
                Descripción del convenio
            </label>
            <input type="text" name="descripcion" id="descripcion"
                   value="{{ old('descripcion') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                   placeholder="Ejemplo: Convenio por reinscripción y mensualidades"
                   required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="numero_parcialidades" class="block text-sm font-medium text-gray-700">
                    Número de parcialidades
                </label>
                <input type="number" name="numero_parcialidades" id="numero_parcialidades" min="1" max="60"
                       value="{{ old('numero_parcialidades') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">
                    Fecha de inicio
                </label>
                <input type="date" name="fecha_inicio" id="fecha_inicio"
                       value="{{ old('fecha_inicio') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
        </div>

        <div class="mb-3 flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-gray-800">Cargos a incluir</h3>
            <div class="text-right text-sm">
                <span class="text-gray-600">Total seleccionado:</span>
                <strong id="totalSeleccionado" class="text-indigo-700">$0.00</strong>
            </div>
        </div>

        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Seleccionar</th>
                        <th class="px-4 py-2 text-left">Concepto</th>
                        <th class="px-4 py-2 text-left">Descripción</th>
                        <th class="px-4 py-2 text-center">Vencimiento</th>
                        <th class="px-4 py-2 text-right">Adeudo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cargosPendientes as $cargo)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <input type="checkbox"
                                       name="cargos[]"
                                       value="{{ $cargo->id }}"
                                       data-monto="{{ $cargo->monto_adeudo }}"
                                       @checked(collect(old('cargos', []))->contains($cargo->id))>
                            </td>
                            <td class="px-4 py-2">{{ $cargo->concepto->nombre ?? 'Concepto' }}</td>
                            <td class="px-4 py-2">{{ $cargo->descripcion_cargo }}</td>
                            <td class="px-4 py-2 text-center">
                                {{ optional($cargo->fecha_vencimiento)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-2 text-right font-semibold">
                                ${{ number_format($cargo->monto_adeudo, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-gray-500">
                                No hay cargos pendientes para este alumno.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-xs text-gray-500">
            Al crear el convenio, los cargos seleccionados pasarán a estatus <strong>En Convenio</strong> y el adeudo se controlará mediante parcialidades.
        </p>

        <div class="mt-6 flex justify-between items-center">
            <a href="{{ route('alumnos.show', $alumno) }}" class="text-gray-600 hover:underline">
                ← Volver al alumno
            </a>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
                Crear Convenio
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('input[name="cargos[]"]');
        const total = document.getElementById('totalSeleccionado');

        function actualizarTotal() {
            let suma = 0;
            checkboxes.forEach((checkbox) => {
                if (checkbox.checked) {
                    suma += parseFloat(checkbox.dataset.monto || '0');
                }
            });

            total.textContent = suma.toLocaleString('es-MX', {
                style: 'currency',
                currency: 'MXN'
            });
        }

        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', actualizarTotal));
        actualizarTotal();
    });
</script>
@endpush
