@extends('layouts.app')

@section('title', 'Requisitos Documentales')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
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

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center shadow">
                <i class='bx bx-list-check text-3xl'></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Catálogo de requisitos documentales</h1>
                <p class="text-sm text-slate-500">Checklist automático para expedientes de alumnos.</p>
            </div>
        </div>

        <a href="{{ route('requisitos_documentales.create') }}" class="inline-flex items-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2.5 rounded-xl shadow font-semibold">
            <i class='bx bx-plus text-xl'></i>
            Nuevo requisito
        </a>
    </div>

    <form method="GET" action="{{ route('requisitos_documentales.index') }}" class="bg-white border border-slate-100 shadow rounded-2xl p-5 mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Documento o descripción" class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Programa</label>
            <select name="programa_id" class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                <option value="">Todos</option>
                @foreach($programas as $programa)
                    <option value="{{ $programa->id }}" @selected((string) $programaId === (string) $programa->id)>{{ $programa->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nivel</label>
            <select name="nivel" class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                <option value="">Todos</option>
                @foreach($niveles as $item)
                    <option value="{{ $item }}" @selected($nivel === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus</label>
            <select name="estatus" class="w-full rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                <option value="activos" @selected($estatus === 'activos')>Activos</option>
                <option value="inactivos" @selected($estatus === 'inactivos')>Inactivos</option>
                <option value="todos" @selected($estatus === 'todos')>Todos</option>
            </select>
        </div>

        <div class="md:col-span-5 flex justify-end gap-2">
            <a href="{{ route('requisitos_documentales.index') }}" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-semibold hover:bg-slate-300">Limpiar</a>
            <button class="px-4 py-2 rounded-xl bg-slate-800 text-white font-semibold hover:bg-slate-900">Filtrar</button>
        </div>
    </form>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        @if($requisitos->isEmpty())
            <div class="p-8 text-center text-slate-500">No hay requisitos documentales registrados.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-slate-700">
                    <thead class="bg-cyan-700 text-white uppercase text-xs tracking-wide">
                        <tr>
                            <th class="px-5 py-3">Orden</th>
                            <th class="px-5 py-3">Documento</th>
                            <th class="px-5 py-3">Alcance</th>
                            <th class="px-5 py-3">Condición</th>
                            <th class="px-5 py-3">Estatus</th>
                            <th class="px-5 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisitos as $requisito)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-5 py-3 text-slate-500">{{ $requisito->orden }}</td>
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900">{{ $requisito->tipo_documento }}</p>
                                    @if($requisito->descripcion)
                                        <p class="text-xs text-slate-500 mt-1">{{ \Illuminate\Support\Str::limit($requisito->descripcion, 90) }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $requisito->alcance }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $requisito->obligatorio ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $requisito->obligatorio ? 'Obligatorio' : 'Opcional' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $requisito->activo ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $requisito->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex justify-center items-center gap-3">
                                        <a href="{{ route('requisitos_documentales.edit', $requisito) }}" class="text-blue-600 hover:text-blue-700 hover:underline font-semibold">Editar</a>
                                        <form action="{{ route('requisitos_documentales.destroy', $requisito) }}" method="POST" onsubmit="return confirm('¿Eliminar o desactivar este requisito?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:text-red-700 hover:underline font-semibold">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-5 border-t border-slate-100">
                {{ $requisitos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
