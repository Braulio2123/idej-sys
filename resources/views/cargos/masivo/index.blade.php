@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- ENCABEZADO --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">💼 Cargos Masivos</h1>
    </div>

    {{-- FLASH --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4 shadow">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4 shadow">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl p-4 mb-6 text-sm">
        <strong>Becas institucionales:</strong> al generar cargos masivos, el sistema aplicará automáticamente la beca vigente de cada alumno solo si el concepto seleccionado es becable. El monto base se conserva como monto original.
    </div>

    {{-- ===================================================== --}}
    {{-- FILTROS --}}
    {{-- ===================================================== --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">🎛️ Filtros de selección</h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            {{-- PROGRAMA --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Programa</label>
                <select id="programa_id" class="filtro w-full border rounded px-3 py-2 text-sm">
                    <option value="">-- Todos --</option>
                    @foreach($programas as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- GRUPO --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Grupo</label>
                <select id="grupo_id" class="filtro w-full border rounded px-3 py-2 text-sm">
                    <option value="">-- Todos --</option>
                    @foreach($grupos as $g)
                        <option value="{{ $g->id }}">
                            {{ $g->nombre }} — {{ $g->programa->nombre ?? 'Sin programa' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- CICLO --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Ciclo Escolar</label>
                <select id="ciclo_id" class="filtro w-full border rounded px-3 py-2 text-sm">
                    <option value="">-- Todos --</option>
                    @foreach($ciclos as $c)
                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- ESTATUS --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Estatus financiero</label>
                <select id="estatus" class="filtro w-full border rounded px-3 py-2 text-sm">
                    <option value="">-- Todos --</option>
                    <option value="Al Corriente">Al Corriente</option>
                    <option value="Con Adeudo">Con Adeudo</option>
                    <option value="En Convenio">En Convenio</option>
                    <option value="Becado">Becado</option>
                </select>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            {{-- BUSCADOR --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Buscar (nombre o matrícula)</label>
                <input type="text" id="buscar"
                       class="filtro w-full border rounded px-3 py-2 text-sm"
                       placeholder="Ej. Juan, A001...">
            </div>

            <div class="flex items-end">
                <p id="loader" class="text-gray-600 text-sm hidden">
                    <i class="fas fa-spinner fa-spin"></i> Filtrando alumnos...
                </p>
            </div>
        </div>
    </div>


    {{-- ===================================================== --}}
    {{-- RESULTADOS + FORM --}}
    {{-- ===================================================== --}}
    <div id="resultados" class="hidden bg-white shadow-md rounded-lg p-6 mb-10">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 Alumnos encontrados</h2>

        <form id="formCargoMasivo" method="POST" action="{{ route('cargos.masivo.store') }}">
            @csrf

            <input type="hidden" id="hidden_programa_id" name="programa_id">
            <input type="hidden" id="hidden_grupo_id" name="grupo_id">
            <input type="hidden" id="hidden_ciclo_id" name="ciclo_id">

            <div id="tablaAlumnos" class="mb-6"></div>

            <div id="formularioCargo" class="hidden border-t pt-6 mt-4">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">💵 Datos del Cargo Masivo</h3>

                {{-- CONCEPTO --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Concepto</label>
                    <select name="concepto_id" required class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">-- Selecciona un concepto --</option>
                        @foreach($conceptos as $concepto)
                            <option value="{{ $concepto->id }}">{{ $concepto->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- MONTO --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Monto (opcional)</label>
                    <input type="number" step="0.01" name="monto"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>

                {{-- VENCIMIENTO --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Fecha de vencimiento</label>
                    <input type="date" name="fecha_vencimiento"
                           required class="w-full border rounded px-3 py-2 text-sm">
                </div>

                {{-- DESCRIPCIÓN --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Descripción</label>
                    <input type="text" name="descripcion"
                           class="w-full border rounded px-3 py-2 text-sm"
                           placeholder="Ej. Colegiatura noviembre 2025">
                </div>

                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                    💰 Aplicar cargos masivos
                </button>
            </div>
        </form>
    </div>


    {{-- ===================================================== --}}
    {{-- HISTORIAL --}}
    {{-- ===================================================== --}}
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">🕓 Historial de operaciones masivas</h2>

        @if($historial->isEmpty())
            <p class="text-gray-500 text-sm">Aún no se han registrado cargos masivos.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="py-2 px-3 border-b text-left">ID</th>
                            <th class="py-2 px-3 border-b text-left">Concepto</th>
                            <th class="py-2 px-3 border-b text-left">Monto</th>
                            <th class="py-2 px-3 border-b text-left">Alumnos</th>
                            <th class="py-2 px-3 border-b text-left">Usuario</th>
                            <th class="py-2 px-3 border-b text-left">Fecha</th>
                            <th class="py-2 px-3 border-b text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($historial as $h)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-3 border-b">{{ $h->id }}</td>
                            <td class="py-2 px-3 border-b">{{ $h->concepto->nombre }}</td>
                            <td class="py-2 px-3 border-b">${{ number_format($h->monto, 2) }}</td>
                            <td class="py-2 px-3 border-b">{{ $h->total_alumnos }}</td>
                            <td class="py-2 px-3 border-b">{{ $h->usuario->nombre ?? 'N/A' }}</td>
                            <td class="py-2 px-3 border-b">{{ $h->created_at->format('d/m/Y') }}</td>
                            <td class="py-2 px-3 border-b text-center">
                                <a href="{{ route('cargos.masivo.show', $h->id) }}"
                                   class="text-indigo-600 hover:underline">
                                    Ver detalles →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $historial->links() }}
            </div>
        @endif
    </div>
</div>


{{-- ===================================================== --}}
{{-- JS INTEGRADO — FUNCIONALIDAD COMPLETA --}}
{{-- ===================================================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    const filtros = document.querySelectorAll('.filtro');
    const loader = document.getElementById('loader');
    const resultados = document.getElementById('resultados');
    const tablaAlumnos = document.getElementById('tablaAlumnos');
    const formularioCargo = document.getElementById('formularioCargo');

    const hiddenPrograma = document.getElementById('hidden_programa_id');
    const hiddenGrupo = document.getElementById('hidden_grupo_id');
    const hiddenCiclo = document.getElementById('hidden_ciclo_id');

    const inputBuscar = document.getElementById('buscar');
    const formCargoMasivo = document.getElementById('formCargoMasivo');


    {{-- === DISPARADORES === --}}
    filtros.forEach(el => el.addEventListener('change', filtrarAlumnos));

    inputBuscar.addEventListener('input', () => {
        clearTimeout(window._busqueda);
        window._busqueda = setTimeout(filtrarAlumnos, 300);
    });


    {{-- === CONFIRMACIÓN ANTES DE ENVIAR === --}}
    formCargoMasivo.addEventListener('submit', e => {
        const seleccionados = document.querySelectorAll('input[name="alumnos[]"]:checked').length;

        if (seleccionados === 0) {
            e.preventDefault();
            alert('Debes seleccionar al menos un alumno.');
            return;
        }

        if (!confirm(`¿Aplicar cargos a ${seleccionados} alumno(s)?`)) {
            e.preventDefault();
        }
    });


    {{-- === FUNCIÓN PRINCIPAL === --}}
    function filtrarAlumnos() {
        loader.classList.remove('hidden');

        const programa = document.getElementById('programa_id').value;
        const grupo = document.getElementById('grupo_id').value;
        const ciclo = document.getElementById('ciclo_id').value;
        const estatus = document.getElementById('estatus').value;
        const buscar = document.getElementById('buscar').value;

        hiddenPrograma.value = programa;
        hiddenGrupo.value = grupo;
        hiddenCiclo.value = ciclo;

        fetch("{{ route('cargos.masivo.filtrar') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                programa_id: programa,
                grupo_id: grupo,
                ciclo_id: ciclo,
                estatus: estatus,
                buscar: buscar
            })
        })
        .then(res => res.json())
        .then(data => {
            loader.classList.add('hidden');

            if (!data.success) {
                tablaAlumnos.innerHTML = `<p class="text-red-600">Error al obtener alumnos.</p>`;
                resultados.classList.remove('hidden');
                formularioCargo.classList.add('hidden');
                return;
            }

            if (data.alumnos.length === 0) {
                tablaAlumnos.innerHTML = `<p class="text-gray-600">No se encontraron alumnos.</p>`;
                resultados.classList.remove('hidden');
                formularioCargo.classList.add('hidden');
                return;
            }

            let html = `
                <table class="min-w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 border-b"><input type="checkbox" id="seleccionarTodo"></th>
                            <th class="px-3 py-2 border-b text-left">Matrícula</th>
                            <th class="px-3 py-2 border-b text-left">Nombre</th>
                            <th class="px-3 py-2 border-b text-left">Grupo</th>
                            <th class="px-3 py-2 border-b text-left">Programa</th>
                            <th class="px-3 py-2 border-b text-left">Estatus financiero</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.alumnos.forEach(a => {
                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 border-b"><input type="checkbox" name="alumnos[]" value="${a.id}"></td>
                        <td class="px-3 py-2 border-b">${a.matricula}</td>
                        <td class="px-3 py-2 border-b">${a.nombre_completo}</td>
                        <td class="px-3 py-2 border-b">${a.grupo}</td>
                        <td class="px-3 py-2 border-b">${a.programa}</td>
                        <td class="px-3 py-2 border-b">${a.estatus_financiero}</td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;
            tablaAlumnos.innerHTML = html;

            resultados.classList.remove('hidden');
            formularioCargo.classList.remove('hidden');

            document.getElementById('seleccionarTodo')
                .addEventListener('change', e => {
                    document.querySelectorAll('input[name="alumnos[]"]')
                        .forEach(chk => chk.checked = e.target.checked);
                });
        })
        .catch(err => {
            console.error(err);
            loader.classList.add('hidden');
            tablaAlumnos.innerHTML = `<p class="text-red-600">Error al filtrar alumnos.</p>`;
            resultados.classList.remove('hidden');
            formularioCargo.classList.add('hidden');
        });
    }

});
</script>

@endsection
