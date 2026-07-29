<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\DocumentoAlumno;
use App\Models\RequisitoDocumental;
use App\Models\Rol;
use App\Services\PrivateFileService;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DocumentoAlumnoController extends Controller
{
    use RegistraBitacora;

    public function __construct(private readonly PrivateFileService $privateFiles)
    {
    }

    public function index(Request $request, Alumno $alumno)
    {
        $usuario = $request->user();
        $alumno->load('grupo.programa');

        $mostrarArchivados = $request->boolean('archivados');

        $documentosQuery = $alumno->documentos()
            ->visiblesPara($usuario)
            ->with(['usuarioSubio', 'usuarioReviso', 'requisitoDocumental.programa']);

        if ($mostrarArchivados) {
            $documentosQuery->onlyTrashed();
        }

        $documentos = $documentosQuery
            ->orderByRaw("FIELD(estatus, 'Rechazado', 'Pendiente', 'Entregado', 'En revisión', 'Aceptado')")
            ->orderBy('tipo_documento')
            ->paginate(15)
            ->withQueryString();

        $requisitosDisponibles = RequisitoDocumental::paraAlumno($alumno)
            ->with('programa')
            ->orderBy('orden')
            ->orderBy('tipo_documento')
            ->get()
            ->filter(fn (RequisitoDocumental $requisito) => DocumentoAlumno::usuarioPuedeGestionarTipo($usuario, $requisito->tipo_documento))
            ->values();

        $tiposDocumento = array_values(array_filter(
            DocumentoAlumno::tiposDisponibles(),
            fn (string $tipo) => DocumentoAlumno::usuarioPuedeGestionarTipo($usuario, $tipo)
        ));

        $queryVisible = fn () => $alumno->documentos()->visiblesPara($usuario);

        $resumen = [
            'total' => $queryVisible()->count(),
            'pendientes' => $queryVisible()->pendientes()->count(),
            'aceptados' => $queryVisible()->aceptados()->count(),
            'revision' => $queryVisible()->where('estatus', DocumentoAlumno::ESTATUS_EN_REVISION)->count(),
            'rechazados' => $queryVisible()->where('estatus', DocumentoAlumno::ESTATUS_RECHAZADO)->count(),
            'archivados' => $queryVisible()->onlyTrashed()->count(),
            'requisitos' => $requisitosDisponibles->count(),
        ];

        return view('alumnos.documentos_index', compact(
            'alumno',
            'documentos',
            'tiposDocumento',
            'requisitosDisponibles',
            'resumen',
            'mostrarArchivados'
        ));
    }

    public function generarChecklist(Request $request, Alumno $alumno)
    {
        abort_unless($request->user()?->tienePermiso('documentos.gestionar'), 403);

        $alumno->load('grupo.programa');

        $requisitos = RequisitoDocumental::paraAlumno($alumno)
            ->orderBy('orden')
            ->orderBy('tipo_documento')
            ->get()
            ->filter(fn (RequisitoDocumental $requisito) => DocumentoAlumno::usuarioPuedeGestionarTipo($request->user(), $requisito->tipo_documento))
            ->values();

        if ($requisitos->isEmpty()) {
            return redirect()
                ->route('alumnos.documentos.index', $alumno)
                ->with('error', 'No hay requisitos documentales activos para este alumno. Revisa el catálogo de requisitos.');
        }

        $creados = DB::transaction(function () use ($alumno, $requisitos) {
            Alumno::whereKey($alumno->id)->lockForUpdate()->firstOrFail();
            $creados = 0;

            foreach ($requisitos as $requisito) {
                $yaExiste = DocumentoAlumno::withTrashed()
                    ->where('alumno_id', $alumno->id)
                    ->where(function ($query) use ($requisito) {
                        $query->where('requisito_documental_id', $requisito->id)
                            ->orWhere('tipo_documento', $requisito->tipo_documento);
                    })
                    ->exists();

                if ($yaExiste) {
                    continue;
                }

                DocumentoAlumno::create([
                    'alumno_id' => $alumno->id,
                    'requisito_documental_id' => $requisito->id,
                    'usuario_subio_id' => Auth::id(),
                    'tipo_documento' => $requisito->tipo_documento,
                    'estatus' => DocumentoAlumno::ESTATUS_PENDIENTE,
                    'observaciones' => $requisito->descripcion,
                ]);

                $creados++;
            }

            return $creados;
        }, 3);

        $mensaje = $creados > 0
            ? "Checklist documental generado. Se crearon {$creados} documentos pendientes."
            : 'El alumno ya tenía registrados todos los documentos esperados para su programa/nivel.';

        $this->bitacora(
            'Generar Checklist Documental',
            "Se generó checklist documental para el alumno {$alumno->nombre_completo}. Documentos creados: {$creados}.",
            'Documentos de Alumnos',
            null,
            $alumno->id
        );

        return redirect()
            ->route('alumnos.documentos.index', $alumno)
            ->with('success', $mensaje);
    }

    public function store(Request $request, Alumno $alumno)
    {
        $validated = $request->validate([
            'requisito_documental_id' => ['nullable', 'exists:requisitos_documentales,id'],
            'tipo_documento' => ['required_without:requisito_documental_id', 'nullable', 'string', 'max:120'],
            'fecha_documento' => ['nullable', 'date'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:5120'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ], $this->mensajesValidacionDocumento(), $this->atributosDocumento());

        $requisito = $this->obtenerRequisitoValido($alumno, $validated['requisito_documental_id'] ?? null);
        $tipoDocumento = trim((string) ($requisito?->tipo_documento ?? $validated['tipo_documento']));

        $documentoAutorizacion = new DocumentoAlumno(['tipo_documento' => $tipoDocumento]);
        abort_unless($documentoAutorizacion->puedeGestionar($request->user()), 403);

        $archivoGuardado = null;

        if ($request->hasFile('archivo')) {
            $archivoGuardado = $this->privateFiles->store(
                $request->file('archivo'),
                "documentos/alumnos/{$alumno->id}",
                PrivateFileService::DOCUMENT_MIMES,
                'archivo'
            );
        }

        try {
            $documento = DB::transaction(function () use ($alumno, $requisito, $tipoDocumento, $validated, $archivoGuardado) {
                Alumno::whereKey($alumno->id)->lockForUpdate()->firstOrFail();

                if ($requisito) {
                    $duplicado = DocumentoAlumno::withTrashed()
                        ->where('alumno_id', $alumno->id)
                        ->where('requisito_documental_id', $requisito->id)
                        ->exists();

                    if ($duplicado) {
                        throw ValidationException::withMessages([
                            'requisito_documental_id' => 'Este requisito documental ya existe, incluso dentro del historial archivado. Revisa el registro existente antes de crear otro.',
                        ]);
                    }
                }

                $documento = new DocumentoAlumno([
                    'alumno_id' => $alumno->id,
                    'requisito_documental_id' => $requisito?->id,
                    'usuario_subio_id' => Auth::id(),
                    'tipo_documento' => $tipoDocumento,
                    'estatus' => $archivoGuardado ? DocumentoAlumno::ESTATUS_ENTREGADO : DocumentoAlumno::ESTATUS_PENDIENTE,
                    'fecha_documento' => $validated['fecha_documento'] ?? null,
                    'observaciones' => $validated['observaciones'] ?? $requisito?->descripcion,
                    'motivo_rechazo' => null,
                ]);

                if ($archivoGuardado) {
                    $this->aplicarMetadatosArchivo($documento, $archivoGuardado);
                }

                $documento->save();

                return $documento;
            }, 3);
        } catch (\Throwable $e) {
            $this->privateFiles->delete($archivoGuardado['path'] ?? null);
            throw $e;
        }

        $this->bitacora(
            'Registrar Documento Alumno',
            "Se registró el documento {$documento->tipo_documento} del alumno {$alumno->nombre_completo} con clasificación {$documento->clasificacion()}.",
            'Documentos de Alumnos',
            $documento,
            $alumno->id
        );

        return redirect()
            ->route('alumnos.documentos.index', $alumno)
            ->with('success', $archivoGuardado ? 'Documento cargado correctamente y enviado a revisión.' : 'Documento pendiente registrado correctamente.');
    }

    public function update(Request $request, Alumno $alumno, DocumentoAlumno $documento)
    {
        $this->validarDocumentoDelAlumno($alumno, $documento);
        abort_unless($documento->puedeGestionar($request->user()) || $documento->puedeRevisar($request->user()), 403);

        if (in_array($documento->estatus, [DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO], true)) {
            return back()->with('error', 'Un documento aceptado o rechazado es evidencia inmutable. Archiva el registro y carga uno nuevo si necesitas sustituirlo.');
        }

        $validated = $request->validate([
            'requisito_documental_id' => ['nullable', 'exists:requisitos_documentales,id'],
            'tipo_documento' => ['required_without:requisito_documental_id', 'nullable', 'string', 'max:120'],
            'estatus' => ['nullable', Rule::in(DocumentoAlumno::estatusDisponibles())],
            'fecha_documento' => ['nullable', 'date'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:5120'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'motivo_rechazo' => ['nullable', 'string', 'max:5000'],
        ], $this->mensajesValidacionDocumento(), $this->atributosDocumento());

        $requisito = $this->obtenerRequisitoValido($alumno, $validated['requisito_documental_id'] ?? null);
        $tipoDocumento = trim((string) ($requisito?->tipo_documento ?? $validated['tipo_documento']));
        $documentoPropuesto = new DocumentoAlumno(['tipo_documento' => $tipoDocumento]);

        abort_unless($documentoPropuesto->puedeGestionar($request->user()) || $documentoPropuesto->puedeRevisar($request->user()), 403);

        if ($documento->archivo_path && $tipoDocumento !== $documento->tipo_documento) {
            throw ValidationException::withMessages([
                'tipo_documento' => 'No se puede reclasificar un registro que ya contiene un archivo. Archívalo y crea uno nuevo con el tipo correcto.',
            ]);
        }

        $estatusSolicitado = $validated['estatus'] ?? $documento->estatus;
        $requiereRevision = in_array($estatusSolicitado, [
            DocumentoAlumno::ESTATUS_EN_REVISION,
            DocumentoAlumno::ESTATUS_ACEPTADO,
            DocumentoAlumno::ESTATUS_RECHAZADO,
        ], true);

        if ($requiereRevision && ! $documentoPropuesto->puedeRevisar($request->user())) {
            abort(403, 'No tienes permiso para revisar este tipo de documento.');
        }

        if ($estatusSolicitado === DocumentoAlumno::ESTATUS_RECHAZADO && mb_strlen(trim((string) ($validated['motivo_rechazo'] ?? ''))) < 8) {
            throw ValidationException::withMessages([
                'motivo_rechazo' => 'Explica claramente por qué se rechaza el documento.',
            ]);
        }

        $archivoGuardado = null;

        if ($request->hasFile('archivo')) {
            $archivoGuardado = $this->privateFiles->store(
                $request->file('archivo'),
                "documentos/alumnos/{$alumno->id}",
                PrivateFileService::DOCUMENT_MIMES,
                'archivo'
            );
        }

        $pathAnterior = $documento->archivo_path;

        try {
            $documentoActualizado = DB::transaction(function () use (
                $documento,
                $alumno,
                $requisito,
                $tipoDocumento,
                $validated,
                $estatusSolicitado,
                $archivoGuardado,
                $request
            ) {
                $actual = DocumentoAlumno::whereKey($documento->id)->lockForUpdate()->firstOrFail();
                $this->validarDocumentoDelAlumno($alumno, $actual);

                if (in_array($actual->estatus, [DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO], true)) {
                    throw ValidationException::withMessages([
                        'estatus' => 'El documento fue revisado por otro usuario y ya no puede modificarse.',
                    ]);
                }

                if ($requisito) {
                    $duplicado = DocumentoAlumno::withTrashed()
                        ->where('alumno_id', $alumno->id)
                        ->where('requisito_documental_id', $requisito->id)
                        ->where('id', '!=', $actual->id)
                        ->exists();

                    if ($duplicado) {
                        throw ValidationException::withMessages([
                            'requisito_documental_id' => 'Ese requisito ya está relacionado con otro documento del alumno.',
                        ]);
                    }
                }

                $actual->requisito_documental_id = $requisito?->id;
                $actual->tipo_documento = $tipoDocumento;
                $actual->fecha_documento = $validated['fecha_documento'] ?? null;
                $actual->observaciones = $validated['observaciones'] ?? null;

                if ($archivoGuardado) {
                    $this->aplicarMetadatosArchivo($actual, $archivoGuardado);
                    $actual->estatus = DocumentoAlumno::ESTATUS_ENTREGADO;
                    $actual->usuario_reviso_id = null;
                    $actual->fecha_revision = null;
                    $actual->motivo_rechazo = null;
                } else {
                    $archivoDisponible = filled($actual->archivo_path);

                    if (in_array($estatusSolicitado, [DocumentoAlumno::ESTATUS_EN_REVISION, DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO], true) && ! $archivoDisponible) {
                        throw ValidationException::withMessages([
                            'estatus' => 'No se puede revisar, aceptar o rechazar un registro que todavía no tiene archivo.',
                        ]);
                    }

                    $actual->estatus = $estatusSolicitado;
                    $actual->motivo_rechazo = $estatusSolicitado === DocumentoAlumno::ESTATUS_RECHAZADO
                        ? trim((string) ($validated['motivo_rechazo'] ?? ''))
                        : null;

                    if (in_array($estatusSolicitado, [DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO], true)) {
                        $actual->usuario_reviso_id = $request->user()->id;
                        $actual->fecha_revision = now();
                    } elseif ($estatusSolicitado !== DocumentoAlumno::ESTATUS_EN_REVISION) {
                        $actual->usuario_reviso_id = null;
                        $actual->fecha_revision = null;
                    }
                }

                $actual->save();

                return $actual;
            }, 3);
        } catch (\Throwable $e) {
            $this->privateFiles->delete($archivoGuardado['path'] ?? null);
            throw $e;
        }

        if ($archivoGuardado && $pathAnterior && $pathAnterior !== $documentoActualizado->archivo_path) {
            $this->privateFiles->delete($pathAnterior);
        }

        $this->bitacora(
            'Actualizar Documento Alumno',
            "Se actualizó el documento {$documentoActualizado->tipo_documento} del alumno {$alumno->nombre_completo}. Estatus: {$documentoActualizado->estatus}.",
            'Documentos de Alumnos',
            $documentoActualizado,
            $alumno->id
        );

        return redirect()
            ->route('alumnos.documentos.index', $alumno)
            ->with('success', $archivoGuardado ? 'El nuevo archivo se guardó y el anterior se retiró después de confirmar la actualización.' : 'Documento actualizado correctamente.');
    }

    public function download(Request $request, Alumno $alumno, DocumentoAlumno $documento)
    {
        $this->validarDocumentoDelAlumno($alumno, $documento);
        abort_unless($documento->puedeDescargar($request->user()), 403);

        $path = $this->privateFiles->ensurePrivate($documento->archivo_path);

        if (! $path) {
            $this->registrarIncidenteArchivo($documento, $alumno, 'El archivo referenciado no existe en almacenamiento privado ni en el almacenamiento público heredado.');

            return back()->with('error', 'El archivo no está disponible. El incidente quedó registrado para revisión del área de Sistemas.');
        }

        $sha256 = $this->privateFiles->sha256($path);

        if ($documento->archivo_sha256 && (! $sha256 || ! hash_equals($documento->archivo_sha256, $sha256))) {
            $this->registrarIncidenteArchivo($documento, $alumno, 'La huella SHA-256 del archivo no coincide con la registrada.');

            return back()->with('error', 'El archivo no superó la validación de integridad y no puede descargarse. Contacta al área de Sistemas.');
        }

        if (! $documento->archivo_sha256 && $sha256) {
            $documento->forceFill([
                'archivo_sha256' => $sha256,
                'archivo_verificado_at' => now(),
            ])->saveQuietly();
        } else {
            $documento->forceFill(['archivo_verificado_at' => now()])->saveQuietly();
        }

        $nombreDescarga = $documento->nombre_original
            ?: Str::slug($documento->tipo_documento, '_').'.'.($documento->extension ?: 'pdf');

        $this->bitacora(
            'Descargar Documento Alumno',
            "Se descargó el documento {$documento->tipo_documento} del alumno {$alumno->nombre_completo}. Clasificación: {$documento->clasificacion()}.",
            'Documentos de Alumnos',
            $documento,
            $alumno->id
        );

        return $this->privateFiles->download($path, $nombreDescarga);
    }

    public function destroy(Request $request, Alumno $alumno, DocumentoAlumno $documento)
    {
        $this->validarDocumentoDelAlumno($alumno, $documento);
        abort_unless($request->user()?->tienePermiso('documentos.eliminar'), 403);

        $tipo = $documento->tipo_documento;
        $teniaArchivo = filled($documento->archivo_path);

        $documento->delete();

        $this->bitacora(
            'Archivar Documento Alumno',
            "Se archivó el documento {$tipo} del alumno {$alumno->nombre_completo}. Archivo conservado: ".($teniaArchivo ? 'sí' : 'no').'.',
            'Documentos de Alumnos',
            $documento,
            $alumno->id
        );

        return redirect()
            ->route('alumnos.documentos.index', $alumno)
            ->with('success', 'Documento archivado correctamente. El archivo privado se conserva como evidencia.');
    }

    private function aplicarMetadatosArchivo(DocumentoAlumno $documento, array $archivo): void
    {
        $documento->nombre_original = $archivo['original_name'];
        $documento->archivo_path = $archivo['path'];
        $documento->mime_type = $archivo['mime_type'];
        $documento->extension = $archivo['extension'];
        $documento->tamano_bytes = $archivo['size'];
        $documento->archivo_sha256 = $archivo['sha256'];
        $documento->archivo_verificado_at = now();
        $documento->fecha_entrega = now();
        $documento->usuario_subio_id = Auth::id();
    }

    private function mensajesValidacionDocumento(): array
    {
        return [
            'tipo_documento.required_without' => 'Selecciona un requisito del catálogo o indica el tipo de documento que estás registrando.',
            'archivo.mimes' => 'El archivo debe ser PDF, JPG, JPEG o PNG.',
            'archivo.mimetypes' => 'El contenido del archivo no coincide con un formato permitido. Verifica que sea PDF o imagen válida.',
            'archivo.max' => 'El archivo no debe ser mayor a 5 MB.',
        ];
    }

    private function atributosDocumento(): array
    {
        return [
            'requisito_documental_id' => 'requisito documental',
            'tipo_documento' => 'tipo de documento',
            'estatus' => 'estatus',
            'fecha_documento' => 'fecha del documento',
            'archivo' => 'archivo',
            'observaciones' => 'observaciones',
            'motivo_rechazo' => 'motivo de rechazo',
        ];
    }

    private function validarDocumentoDelAlumno(Alumno $alumno, DocumentoAlumno $documento): void
    {
        abort_if((int) $documento->alumno_id !== (int) $alumno->id, 404);
    }

    private function obtenerRequisitoValido(Alumno $alumno, ?int $requisitoId): ?RequisitoDocumental
    {
        if (! $requisitoId) {
            return null;
        }

        $requisito = RequisitoDocumental::findOrFail($requisitoId);

        $esValido = RequisitoDocumental::paraAlumno($alumno)
            ->whereKey($requisito->id)
            ->exists();

        if (! $esValido) {
            throw ValidationException::withMessages([
                'requisito_documental_id' => 'El requisito documental seleccionado no aplica para este alumno.',
            ]);
        }

        return $requisito;
    }

    private function registrarIncidenteArchivo(DocumentoAlumno $documento, Alumno $alumno, string $detalle): void
    {
        $this->bitacora(
            'Incidente Archivo Privado',
            "Documento #{$documento->id} ({$documento->tipo_documento}) del alumno {$alumno->nombre_completo}: {$detalle}",
            'Seguridad de Archivos',
            $documento,
            $alumno->id
        );
    }
}
