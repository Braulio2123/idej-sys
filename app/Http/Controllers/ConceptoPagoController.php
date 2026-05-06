<?php

namespace App\Http\Controllers;

use App\Models\ConceptoPago;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Traits\RegistraBitacora;

class ConceptoPagoController extends Controller
{
    use RegistraBitacora;

    /**
     * Listado de conceptos
     * NO aplica bitácora
     */
    public function index()
    {
        $conceptos = ConceptoPago::latest()->get();
        return view('conceptos.index', compact('conceptos'));
    }

    /**
     * Formulario crear
     * NO aplica bitácora
     */
    public function create()
    {
        return view('conceptos.create');
    }

    /**
     * Guardar nuevo concepto
     * ✔ Sí aplica bitácora
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|unique:conceptos_pagos,nombre',
            'monto_base'  => 'required|numeric|min:0',
            'es_becable'  => 'nullable|boolean',
        ]);

        // Si no viene marcado, se fuerza a 0
        $validated['es_becable'] = $request->has('es_becable') ? 1 : 0;

        $nuevo = ConceptoPago::create($validated);

        // 🔥 BITÁCORA
        $this->bitacora(
            'Crear Concepto de Pago',
            "Se creó el concepto '{$nuevo->nombre}', monto base: {$nuevo->monto_base}, becable: {$nuevo->es_becable}."
        );

        return redirect()
            ->route('conceptos.index')
            ->with('success', 'Concepto creado correctamente.');
    }

    /**
     * Formulario editar
     * NO aplica bitácora
     */
    public function edit(ConceptoPago $concepto)
    {
        return view('conceptos.edit', compact('concepto'));
    }

    /**
     * Actualizar concepto
     * ✔ Sí aplica bitácora
     */
    public function update(Request $request, ConceptoPago $concepto)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                Rule::unique('conceptos_pagos', 'nombre')->ignore($concepto->id),
            ],
            'monto_base' => 'required|numeric|min:0',
            'es_becable' => 'nullable|boolean',
        ]);

        $validated['es_becable'] = $request->has('es_becable') ? 1 : 0;

        $concepto->update($validated);

        // 🔥 BITÁCORA
        $this->bitacora(
            'Actualizar Concepto de Pago',
            "Se actualizó el concepto '{$concepto->nombre}' (ID {$concepto->id})."
        );

        return redirect()
            ->route('conceptos.index')
            ->with('success', 'Concepto actualizado correctamente.');
    }

    /**
     * Eliminar concepto
     * ✔ Sí aplica bitácora
     */
    public function destroy(ConceptoPago $concepto)
    {
        $nombre = $concepto->nombre;

        $concepto->delete();

        // 🔥 BITÁCORA
        $this->bitacora(
            'Eliminar Concepto de Pago',
            "Se eliminó el concepto '{$nombre}'."
        );

        return redirect()
            ->route('conceptos.index')
            ->with('success', 'Concepto eliminado correctamente.');
    }
}
