<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\RegistraBitacora;

class DocenteController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $query = Docente::query();

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre_completo', 'like', '%' . $request->buscar . '%')
                  ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $docentes = $query->orderBy('nombre_completo')->paginate(10);

        return view('docentes.index', compact('docentes'));
    }

    public function create()
    {
        return view('docentes.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $validated['creado_por_id'] = Auth::id();
        $validated = array_merge($validated, $this->guardarDocumentos($request));
        $validated['rfc'] = isset($validated['rfc']) ? mb_strtoupper(trim($validated['rfc'])) : null;

        $docente = Docente::create($validated);
        $docente->estatus = $docente->calcularEstatus();
        $docente->save();

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
        $validated = array_merge($validated, $this->guardarDocumentos($request));
        $validated['rfc'] = isset($validated['rfc']) ? mb_strtoupper(trim($validated['rfc'])) : null;

        $docente->update($validated);
        $docente->estatus = $docente->calcularEstatus();
        $docente->save();

        $this->bitacora('Actualizar Docente', "Se actualizó el docente {$docente->nombre_completo} (ID {$docente->id}).");

        return redirect()->route('docentes.index')->with('success', 'Docente actualizado correctamente.');
    }

    public function destroy(Docente $docente)
    {
        $id = $docente->id;
        $nombre = $docente->nombre_completo;

        $docente->delete();
        $this->bitacora('Eliminar Docente', "Se eliminó el docente {$nombre} (ID {$id}).");

        return redirect()->route('docentes.index')->with('success', 'Docente eliminado correctamente.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:30',
            'area_especialidad' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'numero_cuenta' => 'nullable|string|max:30',
            'banco' => 'nullable|string|max:120',
            'curriculum' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'titulo_cedula' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'constancia_fiscal' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'rfc.max' => 'El RFC no debe exceder 13 caracteres.',
        ]);
    }

    private function guardarDocumentos(Request $request): array
    {
        $paths = [];
        foreach ([
            'curriculum' => 'curriculum_path',
            'titulo_cedula' => 'titulo_cedula_path',
            'constancia_fiscal' => 'constancia_fiscal_path',
        ] as $input => $column) {
            if ($request->hasFile($input)) {
                $paths[$column] = $request->file($input)->store('docentes/documentos', 'local');
            }
        }
        return $paths;
    }
}
