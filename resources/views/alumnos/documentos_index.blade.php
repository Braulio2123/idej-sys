@extends('layouts.app')

@section('title', 'Expediente Documental')

@section('content')
@php
    use App\Models\DocumentoAlumno;

    $usuarioActual = Auth::user();
    $puedeGestionarDocumentos = usuarioTienePermiso('documentos.gestionar');
    $puedeRegistrar = $puedeGestionarDocumentos && count($tiposDocumento) > 0;
    $puedeGenerarChecklist = $puedeGestionarDocumentos;
    $puedeEliminar = usuarioTienePermiso('documentos.eliminar');
@endphp

<div class="container mx-auto px-2 md:px-4 py-4">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-slate-500">Expediente documental privado</p>
            <h1 class="text-3xl font-bold text-slate-900">{{ $alumno->nombre_completo }}</h1>
            <p class="mt-1 text-slate-600">
                Matrícula: <strong>{{ $alumno->matricula }}</strong>
                · Programa: <strong>{{ $alumno->grupo->programa->nombre ?? 'Sin programa' }}</strong>
            </p>
            <p class="mt-2 text-xs text-slate-500">Solo se muestran las categorías documentales autorizadas para tu función.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($puedeGenerarChecklist)
                <form action="{{ route('alumnos.documentos.generar-checklist', $alumno) }}" method="POST" onsubmit="return confirm('¿Generar documentos pendientes desde el catálogo? No se duplicarán registros existentes.');">
                    @csrf
                    <button class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-cyan-700">Generar checklist</button>
                </form>
            @endif
            <a href="{{ route('alumnos.show', $alumno) }}" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-slate-800">← Volver al expediente</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-100 p-4 text-green-800">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-100 p-4 text-red-800">⚠️ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="mb-2 font-semibold">Revisa los datos capturados:</p>
            <ul class="list-inside list-disc text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
        @foreach([
            'Requisitos' => $resumen['requisitos'],
            'Registrados' => $resumen['total'],
            'Pendientes' => $resumen['pendientes'],
            'En revisión' => $resumen['revision'],
            'Aceptados' => $resumen['aceptados'],
            'Rechazados' => $resumen['rechazados'],
            'Archivados' => $resumen['archivados'],
        ] as $etiqueta => $cantidad)
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ $etiqueta }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $cantidad }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-6 flex flex-wrap items-center gap-2">
        <a href="{{ route('alumnos.documentos.index', $alumno) }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ $mostrarArchivados ? 'bg-slate-100 text-slate-700' : 'bg-cyan-600 text-white' }}">Documentos activos</a>
        <a href="{{ route('alumnos.documentos.index', [$alumno, 'archivados' => 1]) }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ $mostrarArchivados ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-700' }}">Ver archivados</a>
    </div>

    @if($puedeRegistrar && ! $mostrarArchivados)
        <div class="mb-6 rounded-2xl border border-slate-100 bg-white p-6 shadow">
            <h2 class="text-xl font-bold text-slate-900">Registrar documento</h2>
            <p class="mb-5 text-sm text-slate-500">El sistema asignará <strong>Pendiente</strong> si no hay archivo y <strong>Entregado</strong> cuando la carga sea válida. La aceptación o rechazo corresponde al área revisora.</p>

            <form action="{{ route('alumnos.documentos.store', $alumno) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                @csrf
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Requisito del catálogo</label>
                    <select name="requisito_documental_id" class="w-full rounded-lg border-slate-300">
                        <option value="">Captura manual</option>
                        @foreach($requisitosDisponibles as $requisito)
                            <option value="{{ $requisito->id }}" @selected((string) old('requisito_documental_id') === (string) $requisito->id)>{{ $requisito->tipo_documento }} · {{ $requisito->alcance }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Tipo manual</label>
                    <select name="tipo_documento" class="w-full rounded-lg border-slate-300">
                        <option value="">Usar requisito seleccionado</option>
                        @foreach($tiposDocumento as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo_documento') === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Fecha del documento</label>
                    <input type="date" name="fecha_documento" value="{{ old('fecha_documento') }}" class="w-full rounded-lg border-slate-300">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Archivo privado</label>
                    <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border-slate-300">
                    <p class="mt-1 text-xs text-slate-500">PDF, JPG o PNG, máximo 5 MB. Se valida el contenido real del archivo.</p>
                </div>
                <div class="md:col-span-2 xl:col-span-4">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Observaciones</label>
                    <textarea name="observaciones" rows="3" maxlength="5000" class="w-full rounded-lg border-slate-300">{{ old('observaciones') }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                    <button class="rounded-lg bg-cyan-600 px-5 py-2.5 font-semibold text-white shadow hover:bg-cyan-700">Guardar documento</button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow">
        <div class="bg-slate-900 px-6 py-4 text-white">
            <h2 class="text-xl font-bold">{{ $mostrarArchivados ? 'Documentos archivados' : 'Documentos autorizados para tu rol' }}</h2>
        </div>

        @if($documentos->isEmpty())
            <div class="p-8 text-center text-slate-500">No hay documentos disponibles para esta vista.</div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($documentos as $documento)
                    @php
                        $puedeGestionarDocumento = $documento->puedeGestionar($usuarioActual);
                        $puedeRevisarDocumento = $documento->puedeRevisar($usuarioActual);
                        $puedeDescargarDocumento = $documento->puedeDescargar($usuarioActual);
                        $esInmutable = in_array($documento->estatus, [DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO], true);
                    @endphp
                    <div class="p-6">
                        <div class="flex flex-col gap-5 xl:flex-row xl:justify-between">
                            <div class="min-w-0">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-slate-900">{{ $documento->tipo_documento }}</h3>
                                    <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">{{ $documento->clasificacion() }}</span>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                        @if($documento->estatus === DocumentoAlumno::ESTATUS_ACEPTADO) bg-green-100 text-green-700
                                        @elseif($documento->estatus === DocumentoAlumno::ESTATUS_RECHAZADO) bg-red-100 text-red-700
                                        @elseif($documento->estatus === DocumentoAlumno::ESTATUS_EN_REVISION) bg-yellow-100 text-yellow-700
                                        @elseif($documento->estatus === DocumentoAlumno::ESTATUS_ENTREGADO) bg-blue-100 text-blue-700
                                        @else bg-slate-100 text-slate-700 @endif">{{ $documento->estatus }}</span>
                                    @if($documento->trashed())<span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">Archivado</span>@endif
                                </div>
                                <div class="grid grid-cols-1 gap-x-8 gap-y-1 text-sm text-slate-600 md:grid-cols-2">
                                    <p><strong>Archivo:</strong> {{ $documento->nombre_original ?? 'Sin archivo cargado' }}</p>
                                    <p><strong>Tamaño:</strong> {{ $documento->tamano_legible }}</p>
                                    <p><strong>Fecha entrega:</strong> {{ optional($documento->fecha_entrega)->format('d/m/Y H:i') ?? '—' }}</p>
                                    <p><strong>Registró:</strong> {{ $documento->usuarioSubio->nombre ?? '—' }}</p>
                                    <p><strong>Revisó:</strong> {{ $documento->usuarioReviso->nombre ?? 'Sin revisión' }}</p>
                                </div>
                                @if($documento->observaciones)<p class="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">{{ $documento->observaciones }}</p>@endif
                                @if($documento->motivo_rechazo)<p class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700"><strong>Motivo:</strong> {{ $documento->motivo_rechazo }}</p>@endif
                            </div>

                            <div class="flex flex-wrap gap-2 xl:justify-end">
                                @if($puedeDescargarDocumento)
                                    <a href="{{ route('alumnos.documentos.download', [$alumno, $documento]) }}" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-cyan-700">Descargar</a>
                                @endif
                                @if($puedeEliminar && ! $documento->trashed())
                                    <form action="{{ route('alumnos.documentos.destroy', [$alumno, $documento]) }}" method="POST" onsubmit="return confirm('¿Archivar este documento? El archivo privado se conservará como evidencia.');">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-700">Archivar</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if(($puedeGestionarDocumento || $puedeRevisarDocumento) && ! $documento->trashed() && ! $esInmutable)
                            <details class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <summary class="cursor-pointer font-semibold text-slate-700">Actualizar documento</summary>
                                <form action="{{ route('alumnos.documentos.update', [$alumno, $documento]) }}" method="POST" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    @csrf @method('PUT')

                                    @if($documento->archivo_path)
                                        <input type="hidden" name="requisito_documental_id" value="{{ $documento->requisito_documental_id }}">
                                        <input type="hidden" name="tipo_documento" value="{{ $documento->tipo_documento }}">
                                        <div class="md:col-span-2 xl:col-span-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">La clasificación no puede cambiar mientras este registro contenga un archivo.</div>
                                    @else
                                        <div class="md:col-span-2">
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Requisito</label>
                                            <select name="requisito_documental_id" class="w-full rounded-lg border-slate-300">
                                                <option value="">Captura manual</option>
                                                @foreach($requisitosDisponibles as $requisito)
                                                    <option value="{{ $requisito->id }}" @selected((int) $documento->requisito_documental_id === (int) $requisito->id)>{{ $requisito->tipo_documento }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Tipo manual</label>
                                            <select name="tipo_documento" class="w-full rounded-lg border-slate-300">
                                                <option value="">Usar requisito seleccionado</option>
                                                @foreach($tiposDocumento as $tipo)<option value="{{ $tipo }}" @selected($documento->tipo_documento === $tipo)>{{ $tipo }}</option>@endforeach
                                            </select>
                                        </div>
                                    @endif

                                    @if($puedeRevisarDocumento)
                                        <div>
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Estatus de revisión</label>
                                            <select name="estatus" class="w-full rounded-lg border-slate-300">
                                                @foreach([DocumentoAlumno::ESTATUS_ENTREGADO, DocumentoAlumno::ESTATUS_EN_REVISION, DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO] as $estatus)
                                                    <option value="{{ $estatus }}" @selected($documento->estatus === $estatus)>{{ $estatus }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="estatus" value="{{ $documento->estatus }}">
                                    @endif

                                    <div>
                                        <label class="mb-1 block text-sm font-semibold text-slate-700">Fecha documento</label>
                                        <input type="date" name="fecha_documento" value="{{ optional($documento->fecha_documento)->format('Y-m-d') }}" class="w-full rounded-lg border-slate-300">
                                    </div>
                                    @if($puedeGestionarDocumento)
                                        <div class="md:col-span-2">
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">{{ $documento->archivo_path ? 'Reemplazar archivo' : 'Cargar archivo' }}</label>
                                            <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border-slate-300">
                                        </div>
                                    @endif
                                    <div class="md:col-span-2">
                                        <label class="mb-1 block text-sm font-semibold text-slate-700">Observaciones</label>
                                        <textarea name="observaciones" rows="3" maxlength="5000" class="w-full rounded-lg border-slate-300">{{ $documento->observaciones }}</textarea>
                                    </div>
                                    @if($puedeRevisarDocumento)
                                        <div class="md:col-span-2">
                                            <label class="mb-1 block text-sm font-semibold text-slate-700">Motivo de rechazo</label>
                                            <textarea name="motivo_rechazo" rows="3" maxlength="5000" class="w-full rounded-lg border-slate-300">{{ $documento->motivo_rechazo }}</textarea>
                                        </div>
                                    @endif
                                    <div class="md:col-span-2 xl:col-span-4 flex justify-end"><button class="rounded-lg bg-slate-800 px-5 py-2.5 font-semibold text-white hover:bg-slate-900">Guardar cambios</button></div>
                                </form>
                            </details>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="border-t border-slate-100 p-5">{{ $documentos->links() }}</div>
        @endif
    </div>
</div>
@endsection
