@extends('layouts.app')

@section('title', 'Prospectos')

@section('content')
@php
    use App\Models\Prospecto;
@endphp

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Prospectos y Relaciones Públicas</h1>
            <p class="text-sm text-slate-500 mt-1">Seguimiento comercial previo a inscripción.</p>
        </div>

        <a href="{{ route('prospectos.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow transition">
            <i class='bx bx-user-plus text-xl'></i>
            Nuevo prospecto
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-xl border border-green-200">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-xl border border-red-200">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('prospectos.index') }}" class="bg-white rounded-2xl shadow border border-slate-100 p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
        <div class="xl:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ $search }}" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Nombre, correo, teléfono o WhatsApp">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Estatus</label>
            <select name="estatus" class="w-full rounded-xl border-slate-300 text-sm">
                <option value="">Todos</option>
                @foreach($estatusDisponibles as $estatus)
                    <option value="{{ $estatus }}" @selected(request('estatus') === $estatus)>{{ $estatus }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Programa</label>
            <select name="programa_id" class="w-full rounded-xl border-slate-300 text-sm">
                <option value="">Todos</option>
                @foreach($programas as $programa)
                    <option value="{{ $programa->id }}" @selected((string) request('programa_id') === (string) $programa->id)>{{ $programa->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Medio</label>
            <select name="medio_contacto" class="w-full rounded-xl border-slate-300 text-sm">
                <option value="">Todos</option>
                @foreach($mediosContacto as $medio)
                    <option value="{{ $medio }}" @selected(request('medio_contacto') === $medio)>{{ $medio }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Asesor</label>
            <select name="asesor_id" class="w-full rounded-xl border-slate-300 text-sm">
                <option value="">Todos</option>
                @foreach($asesores as $asesor)
                    <option value="{{ $asesor->id }}" @selected((string) request('asesor_id') === (string) $asesor->id)>{{ $asesor->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="xl:col-span-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="vencidos" value="1" @checked(request('vencidos')) class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                Mostrar solo prospectos vencidos
            </label>

            <div class="flex gap-2">
                <a href="{{ route('prospectos.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50">Limpiar</a>
                <button class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900">Filtrar</button>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Prospecto</th>
                        <th class="px-4 py-3 text-left">Programa</th>
                        <th class="px-4 py-3 text-left">Medio</th>
                        <th class="px-4 py-3 text-left">Asesor</th>
                        <th class="px-4 py-3 text-left">Próximo contacto</th>
                        <th class="px-4 py-3 text-left">Estatus</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($prospectos as $prospecto)
                        @php
                            $vencido = $prospecto->fecha_proximo_contacto && $prospecto->fecha_proximo_contacto->isPast() && !in_array($prospecto->estatus, [Prospecto::ESTATUS_INSCRITO, Prospecto::ESTATUS_DESCARTADO], true);
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $vencido ? 'bg-red-50/60' : '' }}">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $prospecto->nombre_completo }}</p>
                                <p class="text-xs text-slate-500">{{ $prospecto->telefono ?? $prospecto->whatsapp ?? $prospecto->correo ?? 'Sin contacto' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $prospecto->programa->nombre ?? $prospecto->nivel_interes ?? 'Sin definir' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $prospecto->medio_contacto ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $prospecto->asesor->nombre ?? 'Sin asignar' }}</td>
                            <td class="px-4 py-3">
                                @if($prospecto->fecha_proximo_contacto)
                                    <span class="{{ $vencido ? 'text-red-700 font-bold' : 'text-slate-700' }}">{{ $prospecto->fecha_proximo_contacto->format('d/m/Y H:i') }}</span>
                                @else
                                    <span class="text-slate-400">Sin fecha</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex w-fit px-2 py-1 rounded-lg text-xs font-bold
                                        @if($prospecto->estatus === Prospecto::ESTATUS_INSCRITO) bg-green-100 text-green-700
                                        @elseif($prospecto->estatus === Prospecto::ESTATUS_DESCARTADO) bg-slate-200 text-slate-700
                                        @elseif($vencido) bg-red-100 text-red-700
                                        @else bg-blue-100 text-blue-700 @endif">
                                        {{ $prospecto->estatus }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ $prospecto->prioridad }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-3">
                                    <a href="{{ route('prospectos.show', $prospecto) }}" class="text-blue-700 hover:text-blue-900 font-semibold">Ver</a>
                                    @unless($prospecto->estaConvertido())
                                        <a href="{{ route('prospectos.edit', $prospecto) }}" class="text-amber-700 hover:text-amber-900 font-semibold">Editar</a>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No hay prospectos con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $prospectos->links() }}
        </div>
    </div>
</div>
@endsection
