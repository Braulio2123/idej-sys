<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Rol;
use App\Services\PrivateFileService;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DocenteController extends Controller
{
    use RegistraBitacora;

    private const DOCUMENTOS = [
        'curriculum' => [
            'path' => 'curriculum_path',
            'original' => 'curriculum_original',
            'sha256' => 'curriculum_sha256',
            'nombre' => 'curriculum',
            'roles' => [Rol::ADMIN, Rol::ACADEMICA],
        ],
        'titulo_cedula' => [
            'path' => 'titulo_cedula_path',
            'original' => 'titulo_cedula_original',
            'sha256' => 'titulo_cedula_sha256',
            'nombre' => 'titulo-y-cedula',
            'roles' => [Rol::ADMIN, Rol::ACADEMICA],
        ],
        'constancia_fiscal' => [
            'path' => 'constancia_fiscal_path',
            'original' => 'constancia_fiscal_original',
            'sha256' => 'constancia_fiscal_sha256',
            'nombre' => 'constancia-fiscal',
            'roles' => [Rol::ADMIN, Rol::CADMIN],
        ],
    ];

    public function __construct(private readonly PrivateFileService $privateFiles)
    {
    }

    public function index(Request $request)
    {
        $query = Docente::query();

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre_completo', 'like', '%'.$request->buscar.'%')
                    ->orWhere('email', 'like', '%'.$request->buscar.'%');
            });
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $docentes = $query->orderBy('nombre_completo')->paginate(10);

        return view('docentes.index', [
            'docentes' => $docentes,
            'estatuses' => Docente::estatuses(),
        ]);
    }

    public function create()
    {
        return view('docentes.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $archivosNuevos = $this->guardarDocumentos($request);

        try {
            $docente = DB::transaction(function () use ($validated, $archivosNuevos) {
                $datos = array_merge($validated, $this->metadatosDocumentos($archivosNuevos));
                $datos['creado_por_id'] = Auth::id();
                $datos['rfc'] = isset($datos['rfc']) ? mb_strtoupper(trim($datos['rfc'])) : null;

                $docente = Docente::create($datos);
                $docente->estatus = $docente->calcularEstatus();
                $docente->save();

                return $docente;
            }, 3);
        } catch (\Throwable $e) {
            $this->eliminarArchivos($archivosNuevos);
            throw $e;
        }

        $this->bitacora('Crear Docente', "Se registró un nuevo docente: {$docente->nombre_completo} (ID {$docente->id}).");

        return redirect()->route('docentes.index')->with('success', 'Docente registrado correctamente.');
    }

    public function show(Docente $docente)
    {
        $docente->load(['calendarioMaterias.calendario.grupo.programa', 'calendarioMaterias.materia', 'calendarioMaterias.sesiones']);

        return view('docentes.show', compact('docente'));
    }

    public function edit(Docente $docente)
    {
        return view('docentes.edit', compact('docente'));
    }

    public function update(Request $request, Docente $docente)
    {
        $validated = $this->validar($request);
        $archivosNuevos = $this->guardarDocumentos($request);
        $pathsAnteriores = $this->pathsReemplazados($docente, $archivosNuevos);

        try {
            DB::transaction(function () use ($validated, $archivosNuevos, $docente) {
                $actual = Docente::whereKey($docente->id)->lockForUpdate()->firstOrFail();
                $datos = array_merge($validated, $this->metadatosDocumentos($archivosNuevos));

                if (array_key_exists('rfc', $datos)) {
                    $datos['rfc'] = $datos['rfc'] ? mb_strtoupper(trim($datos['rfc'])) : null;
                }

                $actual->update($datos);
                $actual->estatus = $actual->calcularEstatus();
                $actual->save();
            }, 3);
        } catch (\Throwable $e) {
            $this->eliminarArchivos($archivosNuevos);
            throw $e;
        }

        foreach ($pathsAnteriores as $path) {
            $this->eliminarArchivoSeguro($path);
        }

        $docente->refresh();
        $this->bitacora('Actualizar Docente', "Se actualizó el docente {$docente->nombre_completo} (ID {$docente->id}).");

        return redirect()->route('docentes.index')->with('success', 'Docente actualizado correctamente.');
    }

    public function editFinanciero(Docente $docente)
    {
        return view('docentes.edit_financiero', compact('docente'));
    }

    public function updateFinanciero(Request $request, Docente $docente)
    {
        $validated = $request->validate([
            'rfc' => ['nullable', 'string', 'max:13'],
            'numero_cuenta' => ['nullable', 'string', 'max:30'],
            'banco' => ['nullable', 'string', 'max:120'],
            'constancia_fiscal' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:5120'],
        ], [
            'rfc.max' => 'El RFC no debe exceder 13 caracteres.',
            'constancia_fiscal.mimes' => 'La constancia debe ser PDF, JPG o PNG.',
            'constancia_fiscal.mimetypes' => 'El contenido de la constancia no corresponde al formato permitido.',
            'constancia_fiscal.max' => 'La constancia puede pesar como máximo 5 MB.',
        ]);

        $archivoNuevo = null;
        $pathAnterior = $docente->constancia_fiscal_path;

        if ($request->hasFile('constancia_fiscal')) {
            $archivoNuevo = $this->privateFiles->store(
                $request->file('constancia_fiscal'),
                'docentes/documentos',
                PrivateFileService::DOCUMENT_MIMES,
                'constancia_fiscal'
            );
        }

        try {
            DB::transaction(function () use ($docente, $validated, $archivoNuevo) {
                $actual = Docente::whereKey($docente->id)->lockForUpdate()->firstOrFail();
                $datos = [
                    'rfc' => filled($validated['rfc'] ?? null) ? mb_strtoupper(trim($validated['rfc'])) : null,
                    'numero_cuenta' => $validated['numero_cuenta'] ?? null,
                    'banco' => $validated['banco'] ?? null,
                ];

                if ($archivoNuevo) {
                    $datos['constancia_fiscal_path'] = $archivoNuevo['path'];
                    $datos['constancia_fiscal_original'] = $archivoNuevo['original_name'];
                    $datos['constancia_fiscal_sha256'] = $archivoNuevo['sha256'];
                }

                $actual->update($datos);
            }, 3);
        } catch (\Throwable $e) {
            if ($archivoNuevo) {
                $this->eliminarArchivoSeguro($archivoNuevo['path']);
            }
            throw $e;
        }

        if ($archivoNuevo && $pathAnterior && $pathAnterior !== $archivoNuevo['path']) {
            $this->eliminarArchivoSeguro($pathAnterior);
        }

        $this->bitacora(
            'Actualizar Datos Financieros Docente',
            "Coordinación Administrativa actualizó los datos fiscales/bancarios del docente {$docente->nombre_completo} (ID {$docente->id}).",
            'Docentes',
            $docente
        );

        return redirect()->route('docentes.show', $docente)
            ->with('success', 'Datos fiscales y bancarios actualizados correctamente.');
    }

    public function descargarDocumento(Request $request, Docente $docente, string $tipo)
    {
        $configuracion = self::DOCUMENTOS[$tipo] ?? null;
        abort_unless($configuracion, 404);
        abort_unless($request->user()?->tieneRol(...$configuracion['roles']), 403);

        $path = $docente->{$configuracion['path']};
        abort_unless($path, 404);

        $privatePath = $this->privateFiles->ensurePrivate($path);

        if (! $privatePath) {
            $this->bitacora(
                'Incidente Documento Docente',
                "El archivo {$tipo} del docente #{$docente->id} no existe en almacenamiento.",
                'Seguridad de Archivos',
                $docente
            );

            return back()->with('error', 'El documento no está disponible. El incidente quedó registrado para revisión de Sistemas.');
        }

        $sha256 = $this->privateFiles->sha256($privatePath);
        $shaRegistrado = $docente->{$configuracion['sha256']};

        if ($shaRegistrado && (! $sha256 || ! hash_equals($shaRegistrado, $sha256))) {
            $this->bitacora(
                'Incidente Integridad Documento Docente',
                "El archivo {$tipo} del docente #{$docente->id} no coincide con su huella registrada.",
                'Seguridad de Archivos',
                $docente
            );

            return back()->with('error', 'El documento no superó la validación de integridad. Contacta al área de Sistemas.');
        }

        if (! $shaRegistrado && $sha256) {
            $docente->forceFill([$configuracion['sha256'] => $sha256])->saveQuietly();
        }

        $this->bitacora(
            'Descargar Documento Docente',
            "Se descargó {$tipo} del docente #{$docente->id}.",
            'Docentes',
            $docente
        );

        $nombre = $docente->{$configuracion['original']} ?: $configuracion['nombre'].'-'.$docente->id;

        return $this->privateFiles->download($privatePath, $nombre);
    }

    public function destroy(Docente $docente)
    {
        $id = $docente->id;
        $nombre = $docente->nombre_completo;

        if (
            $docente->solicitudesPago()->exists()
            || $docente->horariosAcademicos()->exists()
            || $docente->calendarioMaterias()->exists()
            || $docente->cursoSesiones()->exists()
        ) {
            $docente->update(['estatus' => Docente::ESTATUS_INACTIVO]);
            $this->bitacora('Inactivar Docente', "Se inactivó el docente {$nombre} (ID {$id}) para conservar su historial.");

            return redirect()->route('docentes.index')->with('success', 'El docente se inactivó porque tiene historial relacionado.');
        }

        $paths = collect(self::DOCUMENTOS)
            ->map(fn (array $configuracion) => $docente->{$configuracion['path']})
            ->filter()
            ->values()
            ->all();

        DB::transaction(function () use ($docente) {
            Docente::whereKey($docente->id)->lockForUpdate()->firstOrFail()->delete();
        }, 3);

        foreach ($paths as $path) {
            $this->eliminarArchivoSeguro($path);
        }

        $this->bitacora('Eliminar Docente', "Se eliminó el docente {$nombre} (ID {$id}) sin historial y se retiraron sus archivos privados.");

        return redirect()->route('docentes.index')->with('success', 'Docente eliminado correctamente.');
    }

    private function validar(Request $request): array
    {
        $puedeGestionarDatosFiscales = $request->user()?->tieneRol(Rol::ADMIN, Rol::CADMIN) ?? false;

        return $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'area_especialidad' => ['required', 'string', 'max:255'],
            'rfc' => [Rule::prohibitedIf(! $puedeGestionarDatosFiscales), 'nullable', 'string', 'max:13'],
            'numero_cuenta' => [Rule::prohibitedIf(! $puedeGestionarDatosFiscales), 'nullable', 'string', 'max:30'],
            'banco' => [Rule::prohibitedIf(! $puedeGestionarDatosFiscales), 'nullable', 'string', 'max:120'],
            'curriculum' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:5120'],
            'titulo_cedula' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:5120'],
            'constancia_fiscal' => [Rule::prohibitedIf(! $puedeGestionarDatosFiscales), 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,application/x-pdf,image/jpeg,image/png', 'max:5120'],
        ], [
            'rfc.max' => 'El RFC no debe exceder 13 caracteres.',
            'rfc.prohibited' => 'Tu rol no puede modificar información fiscal del docente.',
            'numero_cuenta.prohibited' => 'Tu rol no puede modificar datos bancarios del docente.',
            'banco.prohibited' => 'Tu rol no puede modificar datos bancarios del docente.',
            'constancia_fiscal.prohibited' => 'Tu rol no puede cargar la constancia fiscal del docente.',
            '*.mimes' => 'El documento debe ser PDF, JPG o PNG.',
            '*.mimetypes' => 'El contenido del documento no corresponde al formato permitido.',
            '*.max' => 'Cada documento puede pesar como máximo 5 MB.',
        ]);
    }

    private function guardarDocumentos(Request $request): array
    {
        $archivos = [];

        foreach (array_keys(self::DOCUMENTOS) as $input) {
            if ($request->hasFile($input)) {
                $archivos[$input] = $this->privateFiles->store(
                    $request->file($input),
                    'docentes/documentos',
                    PrivateFileService::DOCUMENT_MIMES,
                    $input
                );
            }
        }

        return $archivos;
    }

    private function metadatosDocumentos(array $archivos): array
    {
        $datos = [];

        foreach ($archivos as $tipo => $archivo) {
            $configuracion = self::DOCUMENTOS[$tipo];
            $datos[$configuracion['path']] = $archivo['path'];
            $datos[$configuracion['original']] = $archivo['original_name'];
            $datos[$configuracion['sha256']] = $archivo['sha256'];
        }

        return $datos;
    }

    private function pathsReemplazados(Docente $docente, array $archivos): array
    {
        $paths = [];

        foreach (array_keys($archivos) as $tipo) {
            $path = $docente->{self::DOCUMENTOS[$tipo]['path']};

            if ($path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function eliminarArchivos(array $archivos): void
    {
        foreach ($archivos as $archivo) {
            $this->eliminarArchivoSeguro($archivo['path'] ?? null);
        }
    }

    private function eliminarArchivoSeguro(?string $path): void
    {
        try {
            $this->privateFiles->delete($path);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
