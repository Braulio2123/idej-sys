<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\DocumentoAlumno;
use App\Models\RequisitoDocumental;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentoAlumnoController extends Controller
{
    use RegistraBitacora;

    public function index(Alumno $alumno)
    {
        $alumno->load('grupo.programa');

        $documentos = $alumno->documentos()
            ->with(['usuarioSubio', 'usuarioReviso', 'requisitoDocumental.programa'])
            ->orderByRaw("FIELD(estatus, 'Rechazado', 'Pendiente', 'Entregado', 'En revisión', 'Aceptado')")
            ->orderBy('tipo_documento')
            ->paginate(15);

        $requisitosDisponibles = RequisitoDocumental::paraAlumno($alumno)
            ->with('programa')
            ->orderBy('orden')
            ->orderBy('tipo_documento')
            ->get();

        $tiposDocumento = DocumentoAlumno::tiposDisponibles();
        $estatusDocumento = DocumentoAlumno::estatusDisponibles();

        $resumen = [
            'total' => $alumno->documentos()->count(),
            'pendientes' => $alumno->documentos()->pendientes()->count(),
            'aceptados' => $alumno->documentos()->aceptados()->count(),
            'revision' => $alumno->documentos()->where('estatus', DocumentoAlumno::ESTATUS_EN_REVISION)->count(),
            'rechazados' => $alumno->documentos()->where('estatus', DocumentoAlumno::ESTATUS_RECHAZADO)->count(),
            'requisitos' => $requisitosDisponibles->count(),
        ];

        return view('alumnos.documentos_index', compact(
            'alumno',
            'documentos',
            'tiposDocumento',
            'estatusDocumento',
            'requisitosDisponibles',
            'resumen'
        ));
    }

    public function generarChecklist(Alumno $alumno)
    {
        $alumno->load('grupo.programa');

        $requisitos = RequisitoDocumental::paraAlumno($alumno)
            ->orderBy('orden')
            ->orderBy('tipo_documento')
            ->get();

        if ($requisitos->isEmpty()) {
            return redirect()
                ->route('alumnos.documentos.index', $alumno)
                ->with('error', 'No hay requisitos documentales activos para este alumno. Revisa el catálogo de requisitos.');
        }

        $creados = 0;

        DB::transaction(function () use ($alumno, $requisitos, &$creados) {
            foreach ($requisitos as $requisito) {
                $yaExiste = $alumno->documentos()
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
        });

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
            'estatus' => ['required', Rule::in(DocumentoAlumno::estatusDisponibles())],
            'fecha_documento' => ['nullable', 'date'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'motivo_rechazo' => ['nullable', 'string', 'max:5000'],
        ]);

        $requisito = $this->obtenerRequisitoValido($alumno, $validated['requisito_documental_id'] ?? null);

        if ($requisito && $alumno->documentos()->where('requisito_documental_id', $requisito->id)->exists()) {
            return back()
                ->withInput()
                ->with('error', 'Este requisito documental ya existe en el expediente del alumno. Puedes editar el registro existente.');
        }

        $archivo = $request->file('archivo');

        $documento = new DocumentoAlumno();
        $documento->alumno_id = $alumno->id;
        $documento->requisito_documental_id = $requisito?->id;
        $documento->usuario_subio_id = Auth::id();
        $documento->tipo_documento = $requisito?->tipo_documento ?? $validated['tipo_documento'];
        $documento->estatus = $validated['estatus'];
        $documento->fecha_documento = $validated['fecha_documento'] ?? null;
        $documento->observaciones = $validated['observaciones'] ?? $requisito?->descripcion;
        $documento->motivo_rechazo = $validated['motivo_rechazo'] ?? null;

        if ($archivo) {
            $this->asignarArchivo($documento, $archivo, $alumno);

            if ($documento->estatus === DocumentoAlumno::ESTATUS_PENDIENTE) {
                $documento->estatus = DocumentoAlumno::ESTATUS_ENTREGADO;
            }
        }

        if (in_array($documento->estatus, [DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO], true)) {
            $documento->usuario_reviso_id = Auth::id();
            $documento->fecha_revision = now();
        }

        $documento->save();

        $this->bitacora(
            'Registrar Documento Alumno',
            "Se registró el documento {$documento->tipo_documento} del alumno {$alumno->nombre_completo}.",
            'Documentos de Alumnos',
            $documento,
            $alumno->id
        );

        return redirect()
            ->route('alumnos.documentos.index', $alumno)
            ->with('success', 'Documento registrado correctamente.');
    }

    public function update(Request $request, Alumno $alumno, DocumentoAlumno $documento)
    {
        $this->validarDocumentoDelAlumno($alumno, $documento);

        $validated = $request->validate([
            'requisito_documental_id' => ['nullable', 'exists:requisitos_documentales,id'],
            'tipo_documento' => ['required_without:requisito_documental_id', 'nullable', 'string', 'max:120'],
            'estatus' => ['required', Rule::in(DocumentoAlumno::estatusDisponibles())],
            'fecha_documento' => ['nullable', 'date'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'motivo_rechazo' => ['nullable', 'string', 'max:5000'],
        ]);

        $requisito = $this->obtenerRequisitoValido($alumno, $validated['requisito_documental_id'] ?? null);

        if ($requisito) {
            $duplicado = $alumno->documentos()
                ->where('requisito_documental_id', $requisito->id)
                ->where('id', '!=', $documento->id)
                ->exists();

            if ($duplicado) {
                return back()
                    ->withInput()
                    ->with('error', 'Ese requisito ya está relacionado con otro documento del mismo alumno.');
            }
        }

        $estatusAnterior = $documento->estatus;

        $documento->requisito_documental_id = $requisito?->id;
        $documento->tipo_documento = $requisito?->tipo_documento ?? $validated['tipo_documento'];
        $documento->estatus = $validated['estatus'];
        $documento->fecha_documento = $validated['fecha_documento'] ?? null;
        $documento->observaciones = $validated['observaciones'] ?? null;
        $documento->motivo_rechazo = $validated['motivo_rechazo'] ?? null;

        if ($request->hasFile('archivo')) {
            if ($documento->archivo_path) {
                Storage::disk('public')->delete($documento->archivo_path);
            }

            $this->asignarArchivo($documento, $request->file('archivo'), $alumno);
        }

        if (
            in_array($documento->estatus, [DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO], true)
            && $estatusAnterior !== $documento->estatus
        ) {
            $documento->usuario_reviso_id = Auth::id();
            $documento->fecha_revision = now();
        }

        if (! in_array($documento->estatus, [DocumentoAlumno::ESTATUS_ACEPTADO, DocumentoAlumno::ESTATUS_RECHAZADO], true)) {
            $documento->usuario_reviso_id = null;
            $documento->fecha_revision = null;
        }

        $documento->save();

        $this->bitacora(
            'Actualizar Documento Alumno',
            "Se actualizó el documento {$documento->tipo_documento} del alumno {$alumno->nombre_completo}.",
            'Documentos de Alumnos',
            $documento,
            $alumno->id
        );

        return redirect()
            ->route('alumnos.documentos.index', $alumno)
            ->with('success', 'Documento actualizado correctamente.');
    }

    public function download(Alumno $alumno, DocumentoAlumno $documento)
    {
        $this->validarDocumentoDelAlumno($alumno, $documento);

        if (! $documento->archivo_path || ! Storage::disk('public')->exists($documento->archivo_path)) {
            return back()->with('error', 'El archivo no existe o todavía no ha sido cargado.');
        }

        $nombreDescarga = $documento->nombre_original ?: Str::slug($documento->tipo_documento, '_').'.'.($documento->extension ?: 'pdf');

        return Storage::disk('public')->download($documento->archivo_path, $nombreDescarga);
    }

    public function destroy(Alumno $alumno, DocumentoAlumno $documento)
    {
        $this->validarDocumentoDelAlumno($alumno, $documento);

        $tipo = $documento->tipo_documento;

        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }

        $documento->delete();

        $this->bitacora(
            'Eliminar Documento Alumno',
            "Se eliminó el documento {$tipo} del alumno {$alumno->nombre_completo}.",
            'Documentos de Alumnos',
            null,
            $alumno->id
        );

        return redirect()
            ->route('alumnos.documentos.index', $alumno)
            ->with('success', 'Documento eliminado correctamente.');
    }

    private function asignarArchivo(DocumentoAlumno $documento, $archivo, Alumno $alumno): void
    {
        $path = $archivo->store("documentos/alumnos/{$alumno->id}", 'public');

        $documento->nombre_original = $archivo->getClientOriginalName();
        $documento->archivo_path = $path;
        $documento->mime_type = $archivo->getClientMimeType();
        $documento->extension = $archivo->getClientOriginalExtension();
        $documento->tamano_bytes = $archivo->getSize();
        $documento->fecha_entrega = now();
        $documento->usuario_subio_id = Auth::id();
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

        abort_unless($esValido, 422, 'El requisito documental seleccionado no aplica para este alumno.');

        return $requisito;
    }
}
