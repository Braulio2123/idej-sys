<?php

namespace App\Http\Controllers;

use App\Models\DocumentoAlumno;
use App\Models\Programa;
use App\Models\RequisitoDocumental;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RequisitoDocumentalController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $programaId = $request->get('programa_id');
        $nivel = $request->get('nivel');
        $estatus = $request->get('estatus', 'activos');
        $search = trim((string) $request->get('search', ''));

        $requisitos = RequisitoDocumental::query()
            ->with('programa')
            ->when($programaId, fn ($query) => $query->where('programa_id', $programaId))
            ->when($nivel, fn ($query) => $query->where('nivel', $nivel))
            ->when($estatus === 'activos', fn ($query) => $query->where('activo', true))
            ->when($estatus === 'inactivos', fn ($query) => $query->where('activo', false))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('tipo_documento', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('CASE WHEN programa_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('nivel')
            ->orderBy('orden')
            ->orderBy('tipo_documento')
            ->paginate(15)
            ->appends($request->query());

        $programas = Programa::orderBy('nombre')->get();
        $niveles = RequisitoDocumental::nivelesDisponibles();

        return view('requisitos_documentales.index', compact(
            'requisitos',
            'programas',
            'niveles',
            'programaId',
            'nivel',
            'estatus',
            'search'
        ));
    }

    public function create()
    {
        $requisito = new RequisitoDocumental([
            'activo' => true,
            'obligatorio' => true,
            'orden' => 0,
        ]);

        return view('requisitos_documentales.create', [
            'requisito' => $requisito,
            'programas' => Programa::orderBy('nombre')->get(),
            'tiposDocumento' => DocumentoAlumno::tiposDisponibles(),
            'niveles' => RequisitoDocumental::nivelesDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $requisito = RequisitoDocumental::create($validated);

        $this->bitacora(
            'Crear Requisito Documental',
            "Se creó el requisito documental {$requisito->tipo_documento} ({$requisito->alcance}).",
            'Requisitos Documentales',
            $requisito
        );

        return redirect()
            ->route('requisitos_documentales.index')
            ->with('success', 'Requisito documental creado correctamente.');
    }

    public function edit(RequisitoDocumental $requisitoDocumental)
    {
        return view('requisitos_documentales.edit', [
            'requisito' => $requisitoDocumental,
            'programas' => Programa::orderBy('nombre')->get(),
            'tiposDocumento' => DocumentoAlumno::tiposDisponibles(),
            'niveles' => RequisitoDocumental::nivelesDisponibles(),
        ]);
    }

    public function update(Request $request, RequisitoDocumental $requisitoDocumental)
    {
        $validated = $this->validar($request, $requisitoDocumental);
        $requisitoDocumental->update($validated);

        $this->bitacora(
            'Actualizar Requisito Documental',
            "Se actualizó el requisito documental {$requisitoDocumental->tipo_documento} ({$requisitoDocumental->alcance}).",
            'Requisitos Documentales',
            $requisitoDocumental
        );

        return redirect()
            ->route('requisitos_documentales.index')
            ->with('success', 'Requisito documental actualizado correctamente.');
    }

    public function destroy(RequisitoDocumental $requisitoDocumental)
    {
        if ($requisitoDocumental->documentos()->exists()) {
            $requisitoDocumental->update(['activo' => false]);

            return redirect()
                ->route('requisitos_documentales.index')
                ->with('success', 'El requisito ya tenía documentos relacionados, por seguridad fue desactivado.');
        }

        $tipo = $requisitoDocumental->tipo_documento;
        $requisitoDocumental->delete();

        $this->bitacora(
            'Eliminar Requisito Documental',
            "Se eliminó el requisito documental {$tipo}.",
            'Requisitos Documentales'
        );

        return redirect()
            ->route('requisitos_documentales.index')
            ->with('success', 'Requisito documental eliminado correctamente.');
    }

    private function validar(Request $request, ?RequisitoDocumental $requisito = null): array
    {
        $validated = $request->validate([
            'programa_id' => ['nullable', 'exists:programas,id'],
            'nivel' => ['nullable', 'string', 'max:80', Rule::in(RequisitoDocumental::nivelesDisponibles())],
            'tipo_documento' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'obligatorio' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        if (! empty($validated['programa_id'])) {
            $validated['nivel'] = null;
        }

        $validated['obligatorio'] = $request->boolean('obligatorio');
        $validated['activo'] = $request->boolean('activo', true);
        $validated['orden'] = (int) ($validated['orden'] ?? 0);

        return $validated;
    }
}
