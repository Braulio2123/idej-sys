@extends('layouts.app')

@section('title', 'Seguimientos del Alumno')

@section('content')
@php
    use App\Models\Rol;
    use App\Models\Seguimiento;

    $usuarioActual = Auth::user();
    $puedeGestionar = $usuarioActual?->tieneRol(Rol::RECEPCION, Rol::CADMIN, Rol::FINANZAS, Rol::RRPP, Rol::ACADEMICA) ?? false;
@endphp

@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg shadow-sm">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg shadow-sm">
        ⚠️ {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg shadow-sm">
        <p class="font-semibold mb-2">Revisa los datos capturados:</p>
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mx-auto px-2 md:px-4 py-4" x-data="{ nuevo: false, editar: null }">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-slate-500">Expediente → Seguimientos</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ $alumno->nombre_completo }}</h1>
            <p class="text-sm text-slate-600 mt-1">
                Matrícula {{ $alumno->matricula }} · {{ $alumno->grupo->programa->nombre ?? 'Sin programa' }} · {{ $alumno->grupo->nombre ?? 'Sin grupo' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('alumnos.show', $alumno) }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-semibold">
                ← Volver al expediente
            </a>

            @if($puedeGestionar)
                <button type="button" @click="nuevo = !nuevo" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold shadow">
                    + Nuevo seguimiento
                </button>
            @endif
        </div>
    </div>

    <div class="bg-white border border-slate-100 shadow rounded-2xl p-5 mb-6">
        <form method="GET" action="{{ route('alumnos.seguimientos.index', $alumno) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus</label>
                <select name="estatus" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Todos</option>
                    @foreach($estatusDisponibles as $estatus)
                        <option value="{{ $estatus }}" @selected(($filtros['estatus'] ?? null) === $estatus)>{{ $estatus }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo</label>
                <select name="tipo" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Todos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? null) === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Prioridad</label>
                <select name="prioridad" class="w-full rounded-lg border-slate-300 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Todas</option>
                    @foreach($prioridades as $prioridad)
                        <option value="{{ $prioridad }}" @selected(($filtros['prioridad'] ?? null) === $prioridad)>{{ $prioridad }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-semibold">Filtrar</button>
                <a href="{{ route('alumnos.seguimientos.index', $alumno) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">Limpiar</a>
            </div>
        </form>
    </div>

    @if($puedeGestionar)
        <div x-show="nuevo" x-transition class="bg-purple-50 border border-purple-200 rounded-2xl shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-purple-900 mb-4">Registrar nuevo seguimiento</h2>
            <form method="POST" action="{{ route('alumnos.seguimientos.store', $alumno) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo</label>
                    <select name="tipo" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Prioridad</label>
                    <select name="prioridad" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($prioridades as $prioridad)
                            <option value="{{ $prioridad }}" @selected($prioridad === Seguimiento::PRIORIDAD_NORMAL)>{{ $prioridad }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus</label>
                    <select name="estatus" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($estatusDisponibles as $estatus)
                            <option value="{{ $estatus }}" @selected($estatus === Seguimiento::ESTATUS_ABIERTO)>{{ $estatus }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Próximo contacto</label>
                    <input type="datetime-local" name="fecha_proximo_contacto" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2 xl:col-span-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Asunto</label>
                    <input type="text" name="asunto" required maxlength="160" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="4" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Resultado / acuerdo</label>
                    <textarea name="resultado" rows="4" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500"></textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow">Guardar seguimiento</button>
                </div>
            </form>
        </div>
    @endif

    <div class="space-y-5">
        @forelse($seguimientos as $seguimiento)
            @php
                $estaVencido = $seguimiento->fecha_proximo_contacto && $seguimiento->fecha_proximo_contacto->isPast() && in_array($seguimiento->estatus, ['Abierto', 'En proceso'], true);
            @endphp

            <div class="bg-white border rounded-2xl shadow p-5 {{ $estaVencido ? 'border-red-200' : 'border-slate-100' }}">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">{{ $seguimiento->tipo }}</span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($seguimiento->prioridad === 'Urgente') bg-red-100 text-red-700
                                @elseif($seguimiento->prioridad === 'Alta') bg-orange-100 text-orange-700
                                @elseif($seguimiento->prioridad === 'Baja') bg-slate-100 text-slate-600
                                @else bg-blue-100 text-blue-700 @endif">
                                {{ $seguimiento->prioridad }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($seguimiento->estatus === 'Cerrado') bg-green-100 text-green-700
                                @elseif($seguimiento->estatus === 'Cancelado') bg-slate-200 text-slate-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ $seguimiento->estatus }}
                            </span>
                            @if($estaVencido)
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-600 text-white">Vencido</span>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-slate-900">{{ $seguimiento->asunto }}</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Registrado por {{ $seguimiento->usuario->nombre ?? 'Usuario no disponible' }} · Área: {{ $seguimiento->area ?? 'No especificada' }} · {{ optional($seguimiento->created_at)->format('d/m/Y H:i') }}
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-600 mt-4">
                            <p><strong>Fecha de contacto:</strong> {{ optional($seguimiento->fecha_contacto)->format('d/m/Y H:i') ?? '—' }}</p>
                            <p><strong>Próximo contacto:</strong> {{ optional($seguimiento->fecha_proximo_contacto)->format('d/m/Y H:i') ?? 'Sin programar' }}</p>
                        </div>

                        @if($seguimiento->descripcion)
                            <div class="mt-4">
                                <p class="text-xs uppercase font-semibold text-slate-500 mb-1">Descripción</p>
                                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $seguimiento->descripcion }}</p>
                            </div>
                        @endif

                        @if($seguimiento->resultado)
                            <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
                                <p class="text-xs uppercase font-semibold text-slate-500 mb-1">Resultado / acuerdo</p>
                                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $seguimiento->resultado }}</p>
                            </div>
                        @endif
                    </div>

                    @if($puedeGestionar)
                        <div class="flex xl:flex-col gap-2">
                            <button type="button" @click="editar = editar === {{ $seguimiento->id }} ? null : {{ $seguimiento->id }}" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold">
                                Editar
                            </button>
                            <form method="POST" action="{{ route('alumnos.seguimientos.destroy', [$alumno, $seguimiento]) }}" onsubmit="return confirm('¿Eliminar este seguimiento? Esta acción quedará registrada en bitácora.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold">Eliminar</button>
                            </form>
                        </div>
                    @endif
                </div>

                @if($puedeGestionar)
                    <div x-show="editar === {{ $seguimiento->id }}" x-transition class="mt-5 border-t border-slate-200 pt-5">
                        <form method="POST" action="{{ route('alumnos.seguimientos.update', [$alumno, $seguimiento]) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo</label>
                                <select name="tipo" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo }}" @selected($seguimiento->tipo === $tipo)>{{ $tipo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Prioridad</label>
                                <select name="prioridad" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                                    @foreach($prioridades as $prioridad)
                                        <option value="{{ $prioridad }}" @selected($seguimiento->prioridad === $prioridad)>{{ $prioridad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus</label>
                                <select name="estatus" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                                    @foreach($estatusDisponibles as $estatus)
                                        <option value="{{ $estatus }}" @selected($seguimiento->estatus === $estatus)>{{ $estatus }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Próximo contacto</label>
                                <input type="datetime-local" name="fecha_proximo_contacto" value="{{ optional($seguimiento->fecha_proximo_contacto)->format('Y-m-d\\TH:i') }}" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                            </div>
                            <div class="md:col-span-2 xl:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Asunto</label>
                                <input type="text" name="asunto" value="{{ $seguimiento->asunto }}" required maxlength="160" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción</label>
                                <textarea name="descripcion" rows="4" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">{{ $seguimiento->descripcion }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Resultado / acuerdo</label>
                                <textarea name="resultado" rows="4" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">{{ $seguimiento->resultado }}</textarea>
                            </div>
                            <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-8 text-center text-slate-500">
                No hay seguimientos registrados con los filtros seleccionados.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $seguimientos->links() }}
    </div>
</div>
@endsection
