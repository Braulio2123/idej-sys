@extends('layouts.app')

@section('title', 'Cargos recurrentes')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Cargos recurrentes</h1>
            <p class="text-sm text-slate-500 mt-1">Programa colegiaturas u otros cargos periódicos. El sistema evita duplicados por alumno, plan y periodo.</p>
        </div>
        <a href="{{ route('cargos.recurrentes.create') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
            + Nuevo plan recurrente
        </a>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <strong>Regla institucional:</strong> no se generan cargos duplicados si el mismo plan ya generó cargo para el alumno en el mismo periodo. Para pruebas usa primero “Simular”.
    </div>

    @if($planes->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">Aún no hay planes de cargos recurrentes.</div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Plan</th>
                        <th class="px-4 py-3 text-left">Concepto</th>
                        <th class="px-4 py-3 text-left">Alcance</th>
                        <th class="px-4 py-3 text-left">Vencimiento</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($planes as $plan)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $plan->nombre }}</p>
                                <p class="text-xs text-slate-500">Monto: {{ $plan->monto ? '$'.number_format($plan->monto, 2) : 'monto del concepto' }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $plan->concepto->nombre ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $plan->alcanceDescripcion() }}</td>
                            <td class="px-4 py-3">Día {{ $plan->dia_vencimiento }} · {{ $plan->frecuencia_meses === 1 ? 'mensual' : 'cada '.$plan->frecuencia_meses.' meses' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $plan->activo ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <form method="POST" action="{{ route('cargos.recurrentes.ejecutar', $plan) }}" data-confirm="Se hará una simulación. No se crearán cargos reales.">
                                        @csrf
                                        <input type="hidden" name="dry_run" value="1">
                                        <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Simular</button>
                                    </form>
                                    <form method="POST" action="{{ route('cargos.recurrentes.ejecutar', $plan) }}" data-confirm="Se generarán cargos reales del periodo actual para este plan. ¿Continuar?">
                                        @csrf
                                        <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Generar ahora</button>
                                    </form>
                                    <a href="{{ route('cargos.recurrentes.edit', $plan) }}" class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">Editar</a>
                                    <form method="POST" action="{{ route('cargos.recurrentes.destroy', $plan) }}" data-confirm="El plan se inactivará, no se eliminará físicamente. ¿Continuar?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">Inactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $planes->links() }}
    @endif
</div>
@endsection
