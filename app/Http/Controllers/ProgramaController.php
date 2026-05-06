<?php

namespace App\Http\Controllers;

use App\Models\Programa;
use Illuminate\Http\Request;
use App\Traits\RegistraBitacora;

class ProgramaController extends Controller
{
    use RegistraBitacora;

    /**
     * Mostrar listado de programas académicos.
     * NO aplica bitácora.
     */
    public function index()
    {
        $programas = Programa::orderBy('nombre')->paginate(15);
        return view('programas.index', compact('programas'));
    }

    /**
     * Formulario crear programa.
     * NO aplica bitácora.
     */
    public function create()
    {
        $programa = new Programa();
        return view('programas.create', compact('programa'));
    }

    /**
     * Guardar nuevo programa.
     * SÍ aplica bitácora.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:programas,nombre',
            'nivel' => 'nullable|string|max:80',
        ]);

        $programa = Programa::create($validated);

        // BITÁCORA
        $this->bitacora(
            'Crear Programa',
            "Se creó el programa académico: {$programa->nombre}"
        );

        return redirect()
            ->route('programas.index')
            ->with('success', 'Programa académico creado correctamente.');
    }

    /**
     * Formulario editar programa.
     * NO aplica bitácora.
     */
    public function edit(Programa $programa)
    {
        return view('programas.edit', compact('programa'));
    }

    /**
     * Actualizar programa.
     * SÍ aplica bitácora.
     */
    public function update(Request $request, Programa $programa)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:programas,nombre,' . $programa->id,
            'nivel' => 'nullable|string|max:80',
        ]);

        $programa->update($validated);

        // BITÁCORA
        $this->bitacora(
            'Actualizar Programa',
            "Se actualizó el programa académico: {$programa->nombre}"
        );

        return redirect()
            ->route('programas.index')
            ->with('success', 'Programa académico actualizado correctamente.');
    }

    /**
     * Eliminar programa.
     * SÍ aplica bitácora.
     */
    public function destroy(Programa $programa)
    {
        $nombre = $programa->nombre;
        $programa->delete();

        // BITÁCORA
        $this->bitacora(
            'Eliminar Programa',
            "Se eliminó el programa académico: {$nombre}"
        );

        return redirect()
            ->route('programas.index')
            ->with('success', 'Programa académico eliminado correctamente.');
    }
}
