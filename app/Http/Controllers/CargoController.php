<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\ConceptoPago;
use App\Models\Cargo;
use App\Traits\RegistraBitacora;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    use RegistraBitacora;

    public function create(Alumno $alumno)
    {
        $conceptos = ConceptoPago::orderBy('nombre')->get();
        $becaActiva = $alumno->becaVigente();

        return view('cargos.create', compact('alumno', 'conceptos', 'becaActiva'));
    }

    public function store(Request $request, Alumno $alumno)
    {
        $validated = $request->validate([
            'concepto_id'       => 'required|exists:conceptos_pagos,id',
            'descripcion_cargo' => 'required|string|max:255',
            'monto_original'    => 'required|numeric|min:0',
            'fecha_vencimiento' => 'required|date',
        ]);

        $concepto = ConceptoPago::findOrFail($validated['concepto_id']);
        $becaActiva = $alumno->becaVigente();

        $montoBase = round((float) $validated['monto_original'], 2);
        $becaPorcentaje = 0;
        $becaMonto = 0.00;
        $becaId = null;

        if ($becaActiva && $concepto->es_becable) {
            $becaPorcentaje = (int) $becaActiva->porcentaje;
            $becaMonto = round($montoBase * ($becaPorcentaje / 100), 2);
            $becaId = $becaActiva->id;
        }

        $montoAdeudo = max(round($montoBase - $becaMonto, 2), 0);
        $estatus = 'Pendiente';

        if ((float) $alumno->saldo_a_favor > 0 && $montoAdeudo > 0) {
            $montoAplicarDelSaldo = min((float) $alumno->saldo_a_favor, $montoAdeudo);
            $montoAdeudo = round($montoAdeudo - $montoAplicarDelSaldo, 2);
            $alumno->decrement('saldo_a_favor', $montoAplicarDelSaldo);

            $estatus = $montoAdeudo <= 0 ? 'Pagado' : 'Parcialmente Pagado';
            $montoAdeudo = max($montoAdeudo, 0);
        }

        $cargo = Cargo::create([
            'alumno_id'         => $alumno->id,
            'concepto_id'       => $concepto->id,
            'beca_id'           => $becaId,
            'descripcion_cargo' => $validated['descripcion_cargo'],
            'monto_original'    => $montoBase,
            'beca_porcentaje_aplicado' => $becaPorcentaje,
            'beca_monto_aplicado' => $becaMonto,
            'monto_adeudo'      => $montoAdeudo,
            'fecha_vencimiento' => $validated['fecha_vencimiento'],
            'estatus'           => $estatus,
        ]);

        $alumno->refresh();
        $alumno->estatus_financiero = $alumno->cargos()
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado', 'En Convenio'])
            ->where('monto_adeudo', '>', 0)
            ->exists()
                ? 'Con Adeudo'
                : 'Al Corriente';
        $alumno->save();

        $detalleBeca = $becaPorcentaje > 0
            ? " Beca aplicada: {$becaPorcentaje}% (-$" . number_format($becaMonto, 2) . ")."
            : ' Sin beca aplicada.';

        $this->bitacora(
            'Crear Cargo',
            "Se creó un cargo para el alumno {$alumno->nombre_completo}. Concepto: {$concepto->nombre}, Monto original: $" . number_format($montoBase, 2) . ", Adeudo final: $" . number_format($montoAdeudo, 2) . ".{$detalleBeca}"
        );

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with('success', 'Cargo registrado correctamente. Becas vigentes y saldo a favor aplicados automáticamente.');
    }
}
