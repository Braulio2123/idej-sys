<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\RegistraBitacora;

class ProgramaController extends Controller
{
    use RegistraBitacora;

    public function index()
    {
        $programas = Programa::orderBy('nombre')->paginate(15);
        return view('programas.index', compact('programas'));
    }

    public function create()
    {
        $programa = new Programa(['activo' => true]);
        return view('programas.create', compact('programa'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clave' => 'nullable|string|max:30',
            'nombre' => 'required|string|max:255|unique:programas,nombre',
            'nivel' => ['required', Rule::in(['Licenciatura', 'Maestría', 'Doctorado'])],
            'modalidad' => 'nullable|string|max:50',
            'duracion_periodos' => 'required|integer|min:1|max:20',
            'descripcion' => 'nullable|string|max:3000',
            'activo' => 'nullable|boolean',
        ], [
            'nivel.required' => 'Selecciona el nivel de Educación Programática.',
            'duracion_periodos.required' => 'Captura la duración en semestres.',
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        $programa = Programa::create($validated);

        $this->bitacora(
            'Registrar Educación Programática',
            "Se registró Educación Programática: {$programa->nombre}."
        );

        return redirect()
            ->route('programas.index')
            ->with('success', 'Educación Programática registrada correctamente.');
    }

    public function edit(Programa $programa)
    {
        return view('programas.edit', compact('programa'));
    }

    public function update(Request $request, Programa $programa)
    {
        $validated = $request->validate([
            'clave' => 'nullable|string|max:30',
            'nombre' => 'required|string|max:255|unique:programas,nombre,' . $programa->id,
            'nivel' => ['required', Rule::in(['Licenciatura', 'Maestría', 'Doctorado'])],
            'modalidad' => 'nullable|string|max:50',
            'duracion_periodos' => 'required|integer|min:1|max:20',
            'descripcion' => 'nullable|string|max:3000',
            'activo' => 'nullable|boolean',
        ], [
            'nivel.required' => 'Selecciona el nivel de Educación Programática.',
            'duracion_periodos.required' => 'Captura la duración en semestres.',
        ]);

        $validated['activo'] = $request->boolean('activo');

        $programa->update($validated);

        $this->bitacora(
            'Actualizar Educación Programática',
            "Se actualizó Educación Programática: {$programa->nombre}."
        );

        return redirect()
            ->route('programas.index')
            ->with('success', 'Educación Programática actualizada correctamente.');
    }

    public function destroy(Programa $programa)
    {
        $nombre = $programa->nombre;

        $tieneHistorial = $programa->materias()->exists()
            || $programa->prospectos()->exists()
            || $programa->requisitosDocumentales()->exists()
            || Grupo::where('programa_id', $programa->id)->exists();

        if ($tieneHistorial) {
            $programa->forceFill(['activo' => false])->save();

            $this->bitacora(
                'Inactivar Educación Programática',
                "Se inactivó Educación Programática {$nombre} porque ya tiene historial relacionado."
            );

            return redirect()
                ->route('programas.index')
                ->with('success', 'La Educación Programática se inactivó para conservar su historial institucional.');
        }

        $programa->delete();

        $this->bitacora(
            'Eliminar Educación Programática',
            "Se eliminó Educación Programática: {$nombre}."
        );

        return redirect()
            ->route('programas.index')
            ->with('success', 'Educación Programática eliminada correctamente.');
    }
}
