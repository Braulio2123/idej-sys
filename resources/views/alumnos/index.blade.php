@extends('layouts.app')

@php
    use App\Models\Rol;
    $puedeModificarAlumnos = in_array(auth()->user()?->rolClave(), [Rol::ADMIN, Rol::RECEPCION, Rol::CADMIN], true);
@endphp

@section('content')

<div class="max-w-7xl mx-auto mt-6">

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        {{-- Título + botón --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Gestión de Alumnos</h1>
                <p class="text-xs text-slate-500 mt-1">Administración general de alumnos registrados en el sistema</p>
            </div>

            @if($puedeModificarAlumnos)
                <a href="{{ route('alumnos.create') }}"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700
                          text-white px-5 py-2.5 rounded-xl font-medium shadow-md transition">
                    <i class='bx bx-user-plus text-xl'></i>
                    Registrar Alumno
                </a>
            @endif
        </div>

        {{-- Mensaje de éxito --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- 🔥 FILTROS DINÁMICOS (SUBMIT AUTOMÁTICO) --}}
        <form method="GET"
              action="{{ route('alumnos.index') }}"
              id="filtrosForm"
              class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">

            {{-- Estatus financiero --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Estatus Financiero</label>
                <select name="estatus_financiero"
                        class="auto-submit w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                    <option value="">-- Todos --</option>
                    <option value="Al Corriente" {{ request('estatus_financiero') == 'Al Corriente' ? 'selected' : '' }}>Al Corriente</option>
                    <option value="Con Adeudo" {{ request('estatus_financiero') == 'Con Adeudo' ? 'selected' : '' }}>Con Adeudo</option>
                </select>
            </div>

            {{-- Condición del alumno --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Condición</label>
                <select name="condicion_alumno"
                        class="auto-submit w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                    <option value="">-- Todas --</option>
                    <option value="Normal" {{ request('condicion_alumno') == 'Normal' ? 'selected' : '' }}>Normal</option>
                    <option value="Becado" {{ request('condicion_alumno') == 'Becado' ? 'selected' : '' }}>Becado</option>
                    <option value="En Convenio" {{ request('condicion_alumno') == 'En Convenio' ? 'selected' : '' }}>En Convenio</option>
                </select>
            </div>

            {{-- Estatus académico --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Estatus Académico</label>
                <select name="estatus_academico"
                        class="auto-submit w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                    <option value="">-- Todos --</option>
                    <option value="Activo" {{ request('estatus_academico') == 'Activo' ? 'selected' : '' }}>Activo</option>
                    <option value="Baja Temporal" {{ request('estatus_academico') == 'Baja Temporal' ? 'selected' : '' }}>Baja Temporal</option>
                    <option value="Suspendido" {{ request('estatus_academico') == 'Suspendido' ? 'selected' : '' }}>Suspendido</option>
                </select>
            </div>

            {{-- Programa --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Programa</label>
                <select name="programa"
                        class="auto-submit w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                    <option value="">-- Todos --</option>
                    @foreach($programas as $p)
                        <option value="{{ $p->nombre }}" {{ request('programa') == $p->nombre ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Grupo --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Grupo</label>
                <select name="grupo_id"
                        class="auto-submit w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                    <option value="">-- Todos --</option>
                    @foreach($grupos as $g)
                        <option value="{{ $g->id }}" {{ request('grupo_id') == $g->id ? 'selected' : '' }}>
                            {{ $g->nombre }} — {{ $g->programa->nombre ?? 'Sin programa' }}
                        </option>
                    @endforeach
                </select>
            </div>

        </form>

        {{-- TABLA --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="py-3 px-4 text-left">Matrícula</th>
                        <th class="py-3 px-4 text-left">Nombre</th>
                        <th class="py-3 px-4 text-left">Programa</th>
                        <th class="py-3 px-4 text-left">Condición</th>
                        <th class="py-3 px-4 text-left">Financiero</th>
                        <th class="py-3 px-4 text-left">Académico</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($alumnos as $alumno)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $alumno->matricula }}</td>
                            <td class="py-3 px-4">{{ $alumno->nombre_completo }}</td>

                            {{-- Programa obtenido del grupo --}}
                            <td class="py-3 px-4 text-slate-600">
                                {{ $alumno->programa ?? '—' }}
                            </td>

                            {{-- Condición --}}
                            <td class="py-3 px-4">
                                @if($alumno->condicion_alumno === 'Becado')
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 text-xs rounded-lg font-semibold">
                                        Becado
                                    </span>
                                    @if($alumno->beca_porcentaje)
                                        <span class="ml-1 text-blue-500 text-xs">
                                            ({{ $alumno->beca_porcentaje }}%)
                                        </span>
                                    @endif
                                @elseif($alumno->condicion_alumno === 'En Convenio')
                                    <span class="bg-amber-100 text-amber-700 px-2 py-1 text-xs rounded-lg font-semibold">
                                        En Convenio
                                    </span>
                                @else
                                    <span class="bg-slate-200 text-slate-700 px-2 py-1 text-xs rounded-lg font-semibold">
                                        Normal
                                    </span>
                                @endif
                            </td>

                            {{-- Financiero --}}
                            <td class="py-3 px-4">
                                @if($alumno->estatus_financiero === 'Con Adeudo')
                                    <span class="bg-red-100 text-red-700 px-2 py-1 text-xs rounded-lg font-semibold">
                                        Con Adeudo
                                    </span>
                                @else
                                    <span class="bg-green-100 text-green-700 px-2 py-1 text-xs rounded-lg font-semibold">
                                        Al Corriente
                                    </span>
                                @endif
                            </td>

                            {{-- Académico --}}
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ $alumno->estatus_academico }}
                            </td>

                            {{-- Acciones --}}
                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center gap-3">
                                    <a href="{{ route('alumnos.show', $alumno) }}"
                                       class="text-blue-600 hover:text-blue-800 font-medium transition">Ver</a>

                                    @if($puedeModificarAlumnos)
                                        <a href="{{ route('alumnos.edit', $alumno) }}"
                                           class="text-amber-600 hover:text-amber-800 font-medium transition">Editar</a>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="py-5 text-center text-slate-500">
                                No hay alumnos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="mt-6">
            {{ $alumnos->links() }}
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.auto-submit').forEach(element => {
    element.addEventListener('change', () => {
        document.getElementById('filtrosForm').submit();
    });
});
</script>
@endpush
