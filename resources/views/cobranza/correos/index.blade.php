@extends('layouts.app')

@section('title', 'Cobranza por correo')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Cobranza por correo</h1>
            <p class="text-sm text-slate-500 mt-1">Módulo conservado para fase posterior. Por ahora no envía correos ni procesa candidatos.</p>
        </div>
        <div class="rounded-xl px-4 py-3 text-sm {{ $recordatoriosActivos ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
            {{ $recordatoriosActivos ? 'Recordatorios activos' : 'Recordatorios desactivados' }}
        </div>
    </div>

    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        <strong>Canal pendiente:</strong> el envío de recordatorios por correo se deja pausado para enfocarnos en la estabilidad general del sistema. SMS y WhatsApp no se usarán.
    </div>


    @if(session('info'))
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">{{ session('info') }}</div>
    @endif

    @unless($recordatoriosActivos)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Este flujo está pausado temporalmente. No se requiere activar configuración de correo en esta fase.
        </div>
    @endunless

    <form method="GET" action="{{ route('cobranza.correos.index') }}" class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Programa</label>
                <select name="programa_id" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    @foreach($programas as $programa)
                        <option value="{{ $programa->id }}" @selected((string) request('programa_id') === (string) $programa->id)>{{ $programa->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Grupo</label>
                <select name="grupo_id" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}" @selected((string) request('grupo_id') === (string) $grupo->id)>{{ $grupo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Límite</label>
                <input type="number" min="1" max="300" name="limite" value="{{ $limite }}" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    <input type="checkbox" name="solo_vencidos" value="1" @checked(request()->boolean('solo_vencidos')) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Solo vencidos
                </label>
            </div>
            <div class="flex items-end">
                <button class="w-full rounded-xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-900">Filtrar</button>
            </div>
        </div>
    </form>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
        <h2 class="font-bold">Módulo pendiente temporalmente</h2>
        <p class="mt-1 text-sm">La cobranza por correo queda en pausa por decisión operativa. Esta pantalla no enviará correos ni buscará candidatos hasta reactivar la fase de recordatorios.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Candidatos a recordatorio</h2>
                <p class="text-sm text-slate-500">{{ $alumnos->count() }} alumno(s) encontrados según la ventana configurada.</p>
            </div>
            <form method="POST" action="{{ route('cobranza.correos.enviar') }}" data-confirm="Esta acción procesará recordatorios de cobranza por correo. Para prueba marca Simular. ¿Continuar?" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="hidden" name="limite" value="{{ $limite }}">
                <input type="hidden" name="programa_id" value="{{ request('programa_id') }}">
                <input type="hidden" name="grupo_id" value="{{ request('grupo_id') }}">
                <input type="hidden" name="solo_vencidos" value="{{ request()->boolean('solo_vencidos') ? 1 : 0 }}">
                <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700">
                    <input type="checkbox" name="dry_run" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Simular primero
                </label>
                <button class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Procesar correos</button>
            </form>
        </div>

        @if($alumnos->isEmpty())
            <div class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">No hay alumnos pendientes dentro de la ventana actual.</div>
        @else
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Alumno</th>
                            <th class="px-4 py-3 text-left">Correo</th>
                            <th class="px-4 py-3 text-left">Grupo</th>
                            <th class="px-4 py-3 text-left">Cargos</th>
                            <th class="px-4 py-3 text-right">Adeudo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($alumnos as $alumno)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $alumno->nombre_completo }}</p>
                                    <p class="text-xs text-slate-500">{{ $alumno->matricula }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $alumno->correo }}</td>
                                <td class="px-4 py-3">{{ $alumno->grupo->nombre ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @foreach($alumno->cargos as $cargo)
                                        <div class="text-xs text-slate-600">{{ $cargo->concepto->nombre ?? 'Cargo' }} · vence {{ $cargo->fecha_vencimiento?->format('d/m/Y') }}</div>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-800">${{ number_format($alumno->cargos->sum('monto_adeudo'), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
