@extends('layouts.app')

@php
    use Illuminate\Support\Str;
    use App\Models\Rol;
@endphp

@section('title', 'Bitácora del Sistema')

@section('content')
<div class="max-w-7xl mx-auto mt-6">
    <div class="bg-white/90 backdrop-blur shadow-lg rounded-2xl p-6 border border-slate-200">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Bitácora del Sistema</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Auditoría de acciones realizadas por usuarios dentro de IDEJ-SYS.
                </p>
            </div>

            <a href="{{ route('bitacoras.export.pdf', request()->query()) }}"
               class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl font-medium shadow transition">
                <i class='bx bxs-file-pdf text-xl'></i> Exportar PDF
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-6 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-6 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <form method="GET" id="filtrosForm" class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Usuario</label>
                <select name="usuario" class="auto-submit w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('usuario') == $u->id ? 'selected' : '' }}>
                            {{ $u->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Módulo</label>
                <select name="modulo" class="auto-submit w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach($modulos as $modulo)
                        <option value="{{ $modulo }}" {{ request('modulo') === $modulo ? 'selected' : '' }}>
                            {{ $modulo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Acción</label>
                <input type="text" name="accion" placeholder="Ej: Crear cargo" value="{{ request('accion') }}"
                       class="w-full auto-submit rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Desde</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                       class="w-full auto-submit rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Hasta</label>
                <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                       class="w-full auto-submit rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Buscar</label>
                <input type="text" name="buscar" placeholder="Descripción, IP, correo..." value="{{ request('buscar') }}"
                       class="w-full auto-submit rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="py-3 px-4 text-left">Fecha</th>
                        <th class="py-3 px-4 text-left">Usuario</th>
                        <th class="py-3 px-4 text-left">Módulo</th>
                        <th class="py-3 px-4 text-left">Acción</th>
                        <th class="py-3 px-4 text-left">Descripción</th>
                        <th class="py-3 px-4 text-left">IP</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($bitacoras as $b)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                {{ ($b->fecha_evento ?? $b->created_at)?->format('d/m/Y H:i') }}
                            </td>

                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ $b->usuario->nombre ?? 'Sistema' }}
                                @if($b->usuario?->email)
                                    <span class="block text-xs font-normal text-slate-500">{{ $b->usuario->email }}</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-slate-700">
                                {{ $b->modulo ?? 'Sistema' }}
                            </td>

                            <td class="py-3 px-4 text-slate-800 font-medium">
                                {{ $b->accion ?? $b->tipo }}
                            </td>

                            <td class="py-3 px-4 text-slate-600">
                                {{ Str::limit($b->descripcion, 70) }}
                            </td>

                            <td class="py-3 px-4 text-slate-500">
                                {{ $b->ip_address ?? '—' }}
                            </td>

                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('bitacoras.show', $b) }}" class="text-indigo-600 hover:text-indigo-800 font-medium transition">
                                    Ver
                                </a>

                                @if(auth()->user()?->rolClave() === Rol::ADMIN)
                                    <form action="{{ route('bitacoras.destroy', $b) }}" method="POST" class="inline ml-3" onsubmit="return confirm('¿Seguro de ocultar este registro de bitácora? Se conserva en base de datos para trazabilidad.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                            Ocultar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-5 text-center text-slate-500">
                                No hay registros en la bitácora.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $bitacoras->links() }}
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
