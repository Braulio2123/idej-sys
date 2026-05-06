@extends('layouts.app')

@section('title', 'Prospecto')

@section('content')
@php
    use App\Models\Prospecto;
    $convertido = $prospecto->estaConvertido();
    $vencido = $prospecto->fecha_proximo_contacto && $prospecto->fecha_proximo_contacto->isPast() && !in_array($prospecto->estatus, [Prospecto::ESTATUS_INSCRITO, Prospecto::ESTATUS_DESCARTADO], true);
@endphp

<div class="max-w-7xl mx-auto space-y-6" x-data="{ seguimiento: false, conversion: false }">
    <div class="bg-gradient-to-r from-blue-900 via-blue-800 to-slate-900 text-white rounded-3xl shadow overflow-hidden">
        <div class="p-6 md:p-8 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        @if($convertido) bg-green-400 text-green-950
                        @elseif($prospecto->estatus === Prospecto::ESTATUS_DESCARTADO) bg-slate-300 text-slate-900
                        @elseif($vencido) bg-red-400 text-red-950
                        @else bg-amber-300 text-slate-950 @endif">
                        {{ $prospecto->estatus }}
                    </span>
                    <span class="px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-semibold">
                        Prioridad {{ $prospecto->prioridad }}
                    </span>
                </div>

                <h1 class="text-3xl font-bold">{{ $prospecto->nombre_completo }}</h1>
                <p class="text-blue-100 mt-2">
                    {{ $prospecto->programa->nombre ?? $prospecto->nivel_interes ?? 'Programa de interés no definido' }}
                </p>
                <p class="text-sm text-blue-200 mt-1">
                    Medio: {{ $prospecto->medio_contacto ?? '—' }} · Origen: {{ $prospecto->origen ?? '—' }} · Asesor: {{ $prospecto->asesor->nombre ?? 'Sin asignar' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('prospectos.index') }}" class="bg-white/10 hover:bg-white/20 border border-white/20 px-4 py-2 rounded-xl text-sm font-semibold">Volver</a>

                @unless($convertido)
                    <a href="{{ route('prospectos.edit', $prospecto) }}" class="bg-white text-blue-900 hover:bg-blue-50 px-4 py-2 rounded-xl text-sm font-semibold shadow">Editar</a>
                    <button type="button" @click="seguimiento = !seguimiento" class="bg-purple-500 hover:bg-purple-600 px-4 py-2 rounded-xl text-sm font-semibold shadow">+ Seguimiento</button>
                    <button type="button" @click="conversion = !conversion" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-xl text-sm font-semibold shadow">Convertir a alumno</button>
                @else
                    <a href="{{ route('alumnos.show', $prospecto->alumno) }}" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-xl text-sm font-semibold shadow">Ver alumno</a>
                @endunless
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-xl border border-green-200">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-xl border border-red-200">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-xl border border-red-200">
            <p class="font-semibold">Revisa los datos capturados:</p>
            <ul class="list-disc pl-5 text-sm mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Próximo contacto</p>
            <p class="mt-2 text-lg font-bold {{ $vencido ? 'text-red-700' : 'text-slate-800' }}">
                {{ $prospecto->fecha_proximo_contacto ? $prospecto->fecha_proximo_contacto->format('d/m/Y H:i') : 'Sin programar' }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Último contacto</p>
            <p class="mt-2 text-lg font-bold text-slate-800">{{ $prospecto->fecha_contacto ? $prospecto->fecha_contacto->format('d/m/Y H:i') : '—' }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Seguimientos</p>
            <p class="mt-2 text-2xl font-bold text-purple-700">{{ $seguimientos->count() }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Conversión</p>
            <p class="mt-2 text-lg font-bold {{ $convertido ? 'text-green-700' : 'text-slate-800' }}">
                {{ $convertido ? 'Convertido' : 'Pendiente' }}
            </p>
        </div>
    </div>

    @unless($convertido)
        <div x-show="seguimiento" x-transition class="bg-purple-50 border border-purple-200 rounded-2xl shadow p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold text-purple-900">Registrar seguimiento</h2>
                    <p class="text-sm text-purple-700">Registra llamadas, WhatsApp, visitas, correos, acuerdos o próximos pasos.</p>
                </div>
                <button type="button" @click="seguimiento = false" class="text-purple-700 hover:text-purple-900 font-semibold">Cerrar</button>
            </div>

            <form method="POST" action="{{ route('prospectos.seguimientos.store', $prospecto) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo</label>
                    <select name="tipo" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($tiposSeguimiento as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Prioridad</label>
                    <select name="prioridad" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($prioridadesSeguimiento as $prioridad)
                            <option value="{{ $prioridad }}" @selected(old('prioridad', $prospecto->prioridad) === $prioridad)>{{ $prioridad }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus del seguimiento</label>
                    <select name="estatus" required class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @foreach($estatusSeguimiento as $estatus)
                            <option value="{{ $estatus }}" @selected(old('estatus', 'Abierto') === $estatus)>{{ $estatus }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Próximo contacto</label>
                    <input type="datetime-local" name="fecha_proximo_contacto" value="{{ old('fecha_proximo_contacto') }}" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                </div>

                <div class="md:col-span-2 xl:col-span-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Asunto</label>
                    <input type="text" name="asunto" value="{{ old('asunto') }}" required maxlength="160" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500" placeholder="Ej. Pidió información de inscripción / Se agenda visita / Falta enviar requisitos">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="4" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">{{ old('descripcion') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Resultado / acuerdo</label>
                    <textarea name="resultado" rows="4" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">{{ old('resultado') }}</textarea>
                </div>

                <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow">Guardar seguimiento</button>
                </div>
            </form>
        </div>

        <div x-show="conversion" x-transition class="bg-green-50 border border-green-200 rounded-2xl shadow p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold text-green-900">Convertir prospecto a alumno</h2>
                    <p class="text-sm text-green-700">Se creará un alumno y se conservará la trazabilidad del prospecto.</p>
                </div>
                <button type="button" @click="conversion = false" class="text-green-700 hover:text-green-900 font-semibold">Cerrar</button>
            </div>

            <form method="POST" action="{{ route('prospectos.convertir', $prospecto) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Matrícula *</label>
                    <input type="text" name="matricula" value="{{ old('matricula') }}" required class="w-full rounded-lg border-slate-300 focus:border-green-500 focus:ring-green-500">
                </div>

                <div class="xl:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Grupo</label>
                    <select name="grupo_id" class="w-full rounded-lg border-slate-300 focus:border-green-500 focus:ring-green-500">
                        <option value="">Sin grupo todavía</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" @selected((string) old('grupo_id') === (string) $grupo->id)>
                                {{ $grupo->nombre }} — {{ $grupo->programa->nombre ?? 'Sin programa' }} — {{ $grupo->cicloEscolar->nombre ?? 'Sin ciclo' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Correo</label>
                    <input type="email" name="correo" value="{{ old('correo', $prospecto->correo) }}" class="w-full rounded-lg border-slate-300 focus:border-green-500 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $prospecto->telefono ?? $prospecto->whatsapp) }}" class="w-full rounded-lg border-slate-300 focus:border-green-500 focus:ring-green-500">
                </div>

                <div class="md:col-span-2 xl:col-span-2 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl p-4 text-sm">
                    Si el alumno tendrá beca, primero conviértelo y después regístrala desde su expediente en el módulo <strong>Becas</strong>, con autorización y vigencia.
                </div>

                <div class="md:col-span-2 xl:col-span-5 flex justify-end">
                    <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow">Crear alumno</button>
                </div>
            </form>
        </div>
    @endunless

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white rounded-2xl shadow border border-slate-100 p-6 space-y-4">
            <h2 class="text-xl font-bold text-slate-800">Datos de contacto</h2>
            <div class="text-sm space-y-2 text-slate-700">
                <p><strong>Correo:</strong> {{ $prospecto->correo ?? '—' }}</p>
                <p><strong>Teléfono:</strong> {{ $prospecto->telefono ?? '—' }}</p>
                <p><strong>WhatsApp:</strong> {{ $prospecto->whatsapp ?? '—' }}</p>
                <p><strong>Programa:</strong> {{ $prospecto->programa->nombre ?? '—' }}</p>
                <p><strong>Nivel:</strong> {{ $prospecto->nivel_interes ?? '—' }}</p>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h3 class="font-bold text-slate-800 mb-2">Observaciones</h3>
                <p class="text-sm text-slate-600 whitespace-pre-line">{{ $prospecto->observaciones ?: 'Sin observaciones.' }}</p>
            </div>

            @if($prospecto->motivo_descarte)
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="font-bold text-red-700 mb-2">Motivo de descarte</h3>
                    <p class="text-sm text-red-700 whitespace-pre-line">{{ $prospecto->motivo_descarte }}</p>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow border border-slate-100 p-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <h2 class="text-xl font-bold text-slate-800">Historial de seguimientos</h2>
                <span class="text-xs text-slate-500">{{ $seguimientos->count() }} registros</span>
            </div>

            <div class="space-y-3">
                @forelse($seguimientos as $seguimiento)
                    <div class="rounded-xl border border-slate-100 p-4 hover:bg-slate-50 transition">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                            <div>
                                <p class="font-bold text-slate-800">{{ $seguimiento->asunto }}</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $seguimiento->tipo }} · {{ $seguimiento->prioridad }} · {{ $seguimiento->estatus }} · {{ $seguimiento->usuario->nombre ?? 'Sin usuario' }}
                                </p>
                            </div>
                            <div class="text-xs text-slate-500 md:text-right">
                                <p>Contacto: {{ $seguimiento->fecha_contacto ? $seguimiento->fecha_contacto->format('d/m/Y H:i') : '—' }}</p>
                                <p>Próximo: {{ $seguimiento->fecha_proximo_contacto ? $seguimiento->fecha_proximo_contacto->format('d/m/Y H:i') : '—' }}</p>
                            </div>
                        </div>

                        @if($seguimiento->descripcion)
                            <p class="text-sm text-slate-600 mt-3 whitespace-pre-line">{{ $seguimiento->descripcion }}</p>
                        @endif

                        @if($seguimiento->resultado)
                            <p class="text-sm text-slate-700 mt-3 bg-slate-100 rounded-lg p-3 whitespace-pre-line"><strong>Resultado:</strong> {{ $seguimiento->resultado }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-500">Todavía no hay seguimientos registrados.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
