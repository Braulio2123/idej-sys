@extends('layouts.app')

@section('title', 'Expediente Documental')

@section('content')
@php
    use App\Models\DocumentoAlumno;
    use App\Models\Rol;

    $usuarioActual = Auth::user();
    $puedeGestionar = $usuarioActual?->tieneRol(Rol::RECEPCION, Rol::CADMIN, Rol::RRPP, Rol::ACADEMICA) ?? false;
    $puedeEliminar = $usuarioActual?->tieneRol(Rol::CADMIN) ?? false;
@endphp

<div class="container mx-auto px-2 md:px-4 py-4">
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">Expediente documental</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ $alumno->nombre_completo }}</h1>
            <p class="text-slate-600 mt-1">
                Matrícula: <strong>{{ $alumno->matricula }}</strong>
                · Programa: <strong>{{ $alumno->grupo->programa->nombre ?? 'Sin programa' }}</strong>
                @if($alumno->grupo?->programa?->nivel)
                    · Nivel: <strong>{{ $alumno->grupo->programa->nivel }}</strong>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($puedeGestionar)
                <form action="{{ route('alumnos.documentos.generar-checklist', $alumno) }}" method="POST" onsubmit="return confirm('¿Generar documentos pendientes desde el catálogo de requisitos? No se duplicarán documentos existentes.');">
                    @csrf
                    <button class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg shadow text-sm font-semibold">
                        Generar checklist
                    </button>
                </form>
            @endif
            <a href="{{ route('alumnos.show', $alumno) }}" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg shadow text-sm font-semibold">
                ← Volver al expediente
            </a>
        </div>
    </div>

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

    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white border border-slate-100 rounded-2xl shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Requisitos</p>
            <p class="text-3xl font-bold text-cyan-800 mt-1">{{ $resumen['requisitos'] }}</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-2xl shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Registrados</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ $resumen['total'] }}</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-2xl shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Pendientes</p>
            <p class="text-3xl font-bold text-red-700 mt-1">{{ $resumen['pendientes'] }}</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-2xl shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">En revisión</p>
            <p class="text-3xl font-bold text-yellow-700 mt-1">{{ $resumen['revision'] }}</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-2xl shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Aceptados</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $resumen['aceptados'] }}</p>
        </div>
        <div class="bg-white border border-slate-100 rounded-2xl shadow p-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Rechazados</p>
            <p class="text-3xl font-bold text-red-800 mt-1">{{ $resumen['rechazados'] }}</p>
        </div>
    </div>

    @if($requisitosDisponibles->isEmpty())
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl">
            Este alumno no tiene requisitos documentales activos por programa/nivel. Puedes capturar documentos manualmente o configurar el catálogo en <strong>Área Académica → Requisitos Documentales</strong>.
        </div>
    @endif

    @if($puedeGestionar)
        <div class="bg-white rounded-2xl shadow border border-slate-100 p-6 mb-6">
            <h2 class="text-xl font-bold text-slate-900 mb-1">Registrar documento</h2>
            <p class="text-sm text-slate-500 mb-5">Puedes capturarlo manualmente o relacionarlo con un requisito del catálogo.</p>

            <form action="{{ route('alumnos.documentos.store', $alumno) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @csrf

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Requisito del catálogo</label>
                    <select name="requisito_documental_id" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="">Captura manual</option>
                        @foreach($requisitosDisponibles as $requisito)
                            <option value="{{ $requisito->id }}" @selected((string) old('requisito_documental_id') === (string) $requisito->id)>
                                {{ $requisito->tipo_documento }} · {{ $requisito->alcance }}{{ $requisito->obligatorio ? ' · Obligatorio' : ' · Opcional' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Si eliges un requisito, el tipo de documento se toma del catálogo.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo manual de documento</label>
                    <select name="tipo_documento" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="">Usar requisito seleccionado</option>
                        @foreach($tiposDocumento as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo_documento') === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus</label>
                    <select name="estatus" required class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                        @foreach($estatusDocumento as $estatus)
                            <option value="{{ $estatus }}" @selected(old('estatus', DocumentoAlumno::ESTATUS_PENDIENTE) === $estatus)>{{ $estatus }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha del documento</label>
                    <input type="date" name="fecha_documento" value="{{ old('fecha_documento') }}" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Archivo PDF/imagen</label>
                    <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                    <p class="text-xs text-slate-400 mt-1">Máximo 5 MB.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="3" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500" placeholder="Ej. Documento recibido por recepción, falta cotejar original, etc.">{{ old('observaciones') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo de rechazo</label>
                    <textarea name="motivo_rechazo" rows="3" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500" placeholder="Usar solo si el documento fue rechazado.">{{ old('motivo_rechazo') }}</textarea>
                </div>

                <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                    <button class="bg-cyan-600 hover:bg-cyan-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow">
                        Guardar documento
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
        <div class="bg-slate-900 text-white px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold">Documentos registrados</h2>
                <p class="text-sm text-slate-300">Control del expediente documental del alumno.</p>
            </div>
        </div>

        @if($documentos->isEmpty())
            <div class="p-8 text-center text-slate-500">
                No hay documentos registrados para este alumno.
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($documentos as $documento)
                    <div class="p-6">
                        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <h3 class="text-lg font-bold text-slate-900">{{ $documento->tipo_documento }}</h3>
                                    @if($documento->requisitoDocumental)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-cyan-100 text-cyan-700">
                                            {{ $documento->requisitoDocumental->obligatorio ? 'Requisito obligatorio' : 'Requisito opcional' }}
                                        </span>
                                    @endif
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                        @if($documento->estatus === DocumentoAlumno::ESTATUS_ACEPTADO) bg-green-100 text-green-700
                                        @elseif($documento->estatus === DocumentoAlumno::ESTATUS_RECHAZADO) bg-red-100 text-red-700
                                        @elseif($documento->estatus === DocumentoAlumno::ESTATUS_EN_REVISION) bg-yellow-100 text-yellow-700
                                        @elseif($documento->estatus === DocumentoAlumno::ESTATUS_ENTREGADO) bg-blue-100 text-blue-700
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $documento->estatus }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1 text-sm text-slate-600">
                                    <p><strong>Archivo:</strong> {{ $documento->nombre_original ?? 'Sin archivo cargado' }}</p>
                                    <p><strong>Tamaño:</strong> {{ $documento->tamano_legible }}</p>
                                    <p><strong>Fecha documento:</strong> {{ optional($documento->fecha_documento)->format('d/m/Y') ?? '—' }}</p>
                                    <p><strong>Fecha entrega:</strong> {{ optional($documento->fecha_entrega)->format('d/m/Y H:i') ?? '—' }}</p>
                                    <p><strong>Registró:</strong> {{ $documento->usuarioSubio->nombre ?? '—' }}</p>
                                    <p><strong>Revisó:</strong> {{ $documento->usuarioReviso->nombre ?? 'Sin revisión' }}</p>
                                </div>

                                @if($documento->observaciones)
                                    <div class="mt-3 bg-slate-50 border border-slate-200 rounded-lg p-3">
                                        <p class="text-xs uppercase font-semibold text-slate-500 mb-1">Observaciones</p>
                                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $documento->observaciones }}</p>
                                    </div>
                                @endif

                                @if($documento->motivo_rechazo)
                                    <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-xs uppercase font-semibold text-red-700 mb-1">Motivo de rechazo</p>
                                        <p class="text-sm text-red-700 whitespace-pre-line">{{ $documento->motivo_rechazo }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 xl:justify-end">
                                @if($documento->archivo_path)
                                    <a href="{{ route('alumnos.documentos.download', [$alumno, $documento]) }}" class="bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow">
                                        Descargar
                                    </a>
                                @endif

                                @if($puedeEliminar)
                                    <form action="{{ route('alumnos.documentos.destroy', [$alumno, $documento]) }}" method="POST" onsubmit="return confirm('¿Eliminar este documento y su archivo? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if($puedeGestionar)
                            <details class="mt-5 bg-slate-50 border border-slate-200 rounded-xl p-4">
                                <summary class="cursor-pointer font-semibold text-slate-700">Editar / revisar documento</summary>

                                <form action="{{ route('alumnos.documentos.update', [$alumno, $documento]) }}" method="POST" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                                    @csrf
                                    @method('PUT')

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Requisito del catálogo</label>
                                        <select name="requisito_documental_id" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                                            <option value="">Captura manual</option>
                                            @foreach($requisitosDisponibles as $requisito)
                                                <option value="{{ $requisito->id }}" @selected((int) $documento->requisito_documental_id === (int) $requisito->id)>
                                                    {{ $requisito->tipo_documento }} · {{ $requisito->alcance }}{{ $requisito->obligatorio ? ' · Obligatorio' : ' · Opcional' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo manual</label>
                                        <select name="tipo_documento" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                                            <option value="">Usar requisito seleccionado</option>
                                            @foreach($tiposDocumento as $tipo)
                                                <option value="{{ $tipo }}" @selected($documento->tipo_documento === $tipo)>{{ $tipo }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Estatus</label>
                                        <select name="estatus" required class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                                            @foreach($estatusDocumento as $estatus)
                                                <option value="{{ $estatus }}" @selected($documento->estatus === $estatus)>{{ $estatus }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha documento</label>
                                        <input type="date" name="fecha_documento" value="{{ optional($documento->fecha_documento)->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Reemplazar archivo</label>
                                        <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones</label>
                                        <textarea name="observaciones" rows="3" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">{{ $documento->observaciones }}</textarea>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo de rechazo</label>
                                        <textarea name="motivo_rechazo" rows="3" class="w-full rounded-lg border-slate-300 focus:border-cyan-500 focus:ring-cyan-500">{{ $documento->motivo_rechazo }}</textarea>
                                    </div>

                                    <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                                        <button class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 rounded-lg font-semibold shadow">
                                            Guardar cambios
                                        </button>
                                    </div>
                                </form>
                            </details>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="p-5 border-t border-slate-100">
                {{ $documentos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
