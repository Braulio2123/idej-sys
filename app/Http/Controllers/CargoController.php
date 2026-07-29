<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\ConceptoPago;
use App\Models\Cargo;
use App\Traits\RegistraBitacora;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CargoController extends Controller
{
    use RegistraBitacora;

    public function create(Alumno $alumno)
    {
        if ($alumno->estatus_academico !== 'Activo') {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('error', 'El alumno no está activo académicamente. No se pueden generar cargos nuevos hasta reactivarlo. Los pagos de adeudos existentes sí pueden registrarse desde el expediente financiero.');
        }

        $conceptos = ConceptoPago::orderBy('nombre')->get();
        $becaActiva = $alumno->becaVigente();

        return view('cargos.create', compact('alumno', 'conceptos', 'becaActiva'));
    }

    public function store(Request $request, Alumno $alumno)
    {
        $validated = $request->validate([
            'operacion_uuid' => ['required', 'uuid'],
            'concepto_id' => ['required', 'exists:conceptos_pagos,id'],
            'descripcion_cargo' => ['required', 'string', 'max:255'],
            'monto_original' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'fecha_vencimiento' => ['required', 'date'],
        ]);

        $cargoExistente = Cargo::where('operacion_uuid', $validated['operacion_uuid'])->first();

        if ($cargoExistente && (int) $cargoExistente->alumno_id === (int) $alumno->id) {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('info', "El cargo #{$cargoExistente->id} ya había sido registrado. Se evitó duplicar la operación.");
        }

        if ($cargoExistente) {
            throw ValidationException::withMessages([
                'operacion_uuid' => 'La operación ya fue utilizada. Recarga el formulario antes de volver a intentarlo.',
            ]);
        }

        try {
            $resultado = DB::transaction(function () use ($validated, $alumno) {
                $alumnoActual = Alumno::whereKey($alumno->id)->lockForUpdate()->firstOrFail();

                if ($alumnoActual->estatus_academico !== 'Activo') {
                    throw ValidationException::withMessages([
                        'concepto_id' => 'El alumno dejó de estar activo académicamente. No se generó el cargo.',
                    ]);
                }

                $concepto = ConceptoPago::findOrFail($validated['concepto_id']);
                $becaActiva = $alumnoActual->becaVigente();

                $montoCapturado = round((float) $validated['monto_original'], 2);
                $montoBase = $concepto->montoConIvaEducacionContinua($montoCapturado);

                if ($montoBase > 99999999.99) {
                    throw ValidationException::withMessages([
                        'monto_original' => 'El monto final con impuestos excede el límite permitido.',
                    ]);
                }

                $becaPorcentaje = 0;
                $becaMonto = 0.00;
                $becaId = null;

                if ($becaActiva && $concepto->es_becable) {
                    $becaPorcentaje = (int) $becaActiva->porcentaje;
                    $becaMonto = round($montoBase * ($becaPorcentaje / 100), 2);
                    $becaId = $becaActiva->id;
                }

                $montoAdeudo = max(round($montoBase - $becaMonto, 2), 0);
                $saldoFavorAplicado = 0.00;
                $estatus = $montoAdeudo <= 0 ? 'Pagado' : 'Pendiente';

                if ((float) $alumnoActual->saldo_a_favor > 0 && $montoAdeudo > 0) {
                    $saldoFavorAplicado = round(min((float) $alumnoActual->saldo_a_favor, $montoAdeudo), 2);
                    $montoAdeudo = max(round($montoAdeudo - $saldoFavorAplicado, 2), 0);

                    $alumnoActual->forceFill([
                        'saldo_a_favor' => max(round((float) $alumnoActual->saldo_a_favor - $saldoFavorAplicado, 2), 0),
                    ])->save();

                    $estatus = $montoAdeudo <= 0 ? 'Pagado' : 'Parcialmente Pagado';
                }

                $cargo = Cargo::create([
                    'operacion_uuid' => $validated['operacion_uuid'],
                    'alumno_id' => $alumnoActual->id,
                    'concepto_id' => $concepto->id,
                    'beca_id' => $becaId,
                    'descripcion_cargo' => $concepto->aplicaIvaEducacionContinua()
                        ? $validated['descripcion_cargo'].' (incluye IVA 16% por Educación Continua)'
                        : $validated['descripcion_cargo'],
                    'monto_original' => $montoBase,
                    'beca_porcentaje_aplicado' => $becaPorcentaje,
                    'beca_monto_aplicado' => $becaMonto,
                    'saldo_favor_aplicado' => $saldoFavorAplicado,
                    'monto_adeudo' => $montoAdeudo,
                    'fecha_vencimiento' => $validated['fecha_vencimiento'],
                    'estatus' => $estatus,
                ]);

                $alumnoActual->forceFill([
                    'estatus_financiero' => $alumnoActual->cargos()
                        ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado', 'En Convenio'])
                        ->where('monto_adeudo', '>', 0)
                        ->exists()
                            ? 'Con Adeudo'
                            : 'Al Corriente',
                ])->save();

                $detalleIva = $concepto->aplicaIvaEducacionContinua()
                    ? ' IVA Educación Continua 16% aplicado sobre $' . number_format($montoCapturado, 2) . '. Monto con IVA: $' . number_format($montoBase, 2) . '.'
                    : '';

                $detalleBeca = $becaPorcentaje > 0
                    ? " Beca aplicada: {$becaPorcentaje}% (-$" . number_format($becaMonto, 2) . ")."
                    : ' Sin beca aplicada.';

                $detalleSaldo = $saldoFavorAplicado > 0
                    ? ' Saldo a favor aplicado: $' . number_format($saldoFavorAplicado, 2) . '.'
                    : '';

                $this->bitacora(
                    'Crear Cargo',
                    "Se creó el cargo #{$cargo->id} para el alumno {$alumnoActual->nombre_completo}. Concepto: {$concepto->nombre}, Monto original: $" . number_format($montoBase, 2) . ", Adeudo final: $" . number_format($montoAdeudo, 2) . ".{$detalleIva}{$detalleBeca}{$detalleSaldo}",
                    'Cargos',
                    $cargo,
                    $alumnoActual->id
                );

                return [
                    'cargo' => $cargo,
                    'saldo_favor_aplicado' => $saldoFavorAplicado,
                ];
            }, 3);
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                $cargoExistente = Cargo::where('operacion_uuid', $validated['operacion_uuid'])->first();

                if ($cargoExistente && (int) $cargoExistente->alumno_id === (int) $alumno->id) {
                    return redirect()
                        ->route('alumnos.show', $alumno)
                        ->with('info', "El cargo #{$cargoExistente->id} ya había sido registrado. Se evitó duplicar la operación.");
                }
            }

            throw $e;
        }

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with(
                'success',
                $resultado['saldo_favor_aplicado'] > 0
                    ? 'Cargo registrado correctamente. Se aplicaron $'.number_format($resultado['saldo_favor_aplicado'], 2).' del saldo a favor del alumno.'
                    : 'Cargo registrado correctamente. La beca vigente se aplicó cuando correspondía.'
            );
    }
}
