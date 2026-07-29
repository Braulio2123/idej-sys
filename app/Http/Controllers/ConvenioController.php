<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\Convenio;
use App\Models\ParcialidadConvenio;
use App\Traits\RegistraBitacora;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConvenioController extends Controller
{
    use RegistraBitacora;

    /**
     * Mostrar formulario para crear un convenio nuevo.
     */
    public function create(Alumno $alumno)
    {
        if ($alumno->estatus_academico !== 'Activo') {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('error', 'El alumno no está activo académicamente. No se pueden crear convenios nuevos hasta reactivarlo.');
        }

        if ($alumno->becaVigente()) {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('error', 'El alumno tiene una beca vigente. Cancela o vence la beca antes de crear un convenio.');
        }

        $cargosPendientes = Cargo::where('alumno_id', $alumno->id)
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
            ->with('concepto')
            ->orderBy('fecha_vencimiento')
            ->get();

        if ($cargosPendientes->isEmpty()) {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('error', 'El alumno no tiene cargos pendientes o parcialmente pagados.');
        }

        return view('convenios.create', compact('alumno', 'cargosPendientes'));
    }

    /**
     * Guardar convenio y generar parcialidades.
     */
    public function store(Request $request, Alumno $alumno)
    {
        if ($alumno->estatus_academico !== 'Activo') {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('error', 'El alumno no está activo académicamente. No se pueden crear convenios nuevos hasta reactivarlo.');
        }

        $validated = $request->validate([
            'cargos' => ['required', 'array', 'min:1'],
            'cargos.*' => ['integer', 'distinct'],
            'numero_parcialidades' => ['required', 'integer', 'min:1', 'max:60'],
            'fecha_inicio' => ['required', 'date'],
            'descripcion' => ['required', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($validated, $alumno) {
                $cargoIds = collect($validated['cargos'])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $cargosAConvenio = Cargo::where('alumno_id', $alumno->id)
                    ->whereIn('id', $cargoIds)
                    ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
                    ->orderBy('fecha_vencimiento')
                    ->lockForUpdate()
                    ->get();

                if ($cargosAConvenio->count() !== $cargoIds->count()) {
                    throw ValidationException::withMessages([
                        'cargos' => 'Uno o más cargos seleccionados no pertenecen al alumno o ya no están pendientes.',
                    ]);
                }

                $totalReestructurado = round((float) $cargosAConvenio->sum('monto_adeudo'), 2);

                if ($totalReestructurado <= 0) {
                    throw ValidationException::withMessages([
                        'cargos' => 'El total reestructurado no puede ser cero.',
                    ]);
                }

                $numeroParcialidades = (int) $validated['numero_parcialidades'];

                $convenio = Convenio::create([
                    'alumno_id' => $alumno->id,
                    // Se conserva para compatibilidad con datos/vistas anteriores, pero la relación formal ya es cargo_convenio.
                    'cargo_original_id' => $cargosAConvenio->first()->id,
                    'descripcion' => $validated['descripcion'],
                    'total_reestructurado' => $totalReestructurado,
                    'numero_parcialidades' => $numeroParcialidades,
                    'estatus' => 'Activo',
                ]);

                foreach ($cargosAConvenio as $cargo) {
                    $convenio->cargos()->attach($cargo->id, [
                        'monto_original' => $cargo->monto_original,
                        'monto_adeudo_original' => $cargo->monto_adeudo,
                        'estatus_original' => $cargo->estatus,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $cargo->update([
                        'estatus' => 'En Convenio',
                    ]);
                }

                $montosParcialidades = $this->calcularParcialidades($totalReestructurado, $numeroParcialidades);

                foreach ($montosParcialidades as $i => $montoParcialidad) {
                    $fechaVencimiento = Carbon::parse($validated['fecha_inicio'])->addMonths($i);

                    ParcialidadConvenio::create([
                        'convenio_id' => $convenio->id,
                        'monto_parcialidad' => $montoParcialidad,
                        'monto_adeudo' => $montoParcialidad,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'estatus' => 'Pendiente',
                    ]);
                }

                $alumno->update([
                    'estatus_financiero' => 'En Convenio',
                    'condicion_alumno' => 'En Convenio',
                    'beca_porcentaje' => 0,
                ]);

                $cargosTexto = $cargosAConvenio
                    ->map(fn (Cargo $cargo) => "#{$cargo->id} {$cargo->descripcion_cargo}")
                    ->implode(', ');

                $this->bitacora(
                    'Crear Convenio',
                    "Se creó un convenio para el alumno {$alumno->nombre_completo} " .
                    "por un total reestructurado de $ {$totalReestructurado} " .
                    "con {$numeroParcialidades} parcialidades. Cargos incluidos: {$cargosTexto}."
                );
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with('success', 'Convenio creado correctamente y parcialidades generadas automáticamente.');
    }

    /**
     * Editar datos generales del convenio.
     */
    public function edit(Alumno $alumno, Convenio $convenio)
    {
        $this->verificarRelacion($alumno, $convenio);

        if ($convenio->estatus !== 'Activo' || $convenio->parcialidades()->whereHas('pagos')->exists()) {
            return redirect()->route('alumnos.convenios.show', [$alumno, $convenio])
                ->with('error', 'El convenio ya tiene historial aplicado o no está activo; su información contractual no puede modificarse.');
        }

        $convenio->load(['cargos.concepto', 'parcialidades']);

        return view('convenios.edit', compact('alumno', 'convenio'));
    }

    /**
     * Actualizar solo la descripción del convenio.
     * El total y número de parcialidades no se editan aquí porque ya existen parcialidades generadas.
     */
    public function update(Request $request, Alumno $alumno, Convenio $convenio)
    {
        $this->verificarRelacion($alumno, $convenio);

        if ($convenio->estatus !== 'Activo' || $convenio->parcialidades()->whereHas('pagos')->exists()) {
            return redirect()->route('alumnos.convenios.show', [$alumno, $convenio])
                ->with('error', 'El convenio ya tiene historial aplicado o no está activo; su información contractual no puede modificarse.');
        }

        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:255'],
        ]);

        $convenio->update([
            'descripcion' => $validated['descripcion'],
        ]);

        $this->bitacora(
            'Actualizar Convenio',
            "Se actualizó la descripción del convenio ID {$convenio->id} del alumno {$alumno->nombre_completo}."
        );

        return redirect()
            ->route('alumnos.convenios.show', [$alumno, $convenio])
            ->with('success', 'Convenio actualizado correctamente.');
    }

    /**
     * Cancelar un convenio sin eliminar su historial contractual.
     */
    public function destroy(Request $request, Alumno $alumno, Convenio $convenio)
    {
        $this->verificarRelacion($alumno, $convenio);

        if ($convenio->estatus === 'Finalizado') {
            return redirect()->route('alumnos.convenios.show', [$alumno, $convenio])
                ->with('info', 'Un convenio finalizado forma parte del historial y no puede cancelarse.');
        }

        if ($convenio->estatus === 'Cancelado') {
            return redirect()->route('alumnos.convenios.show', [$alumno, $convenio])
                ->with('info', 'El convenio ya estaba cancelado.');
        }

        $validated = $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'motivo_cancelacion.required' => 'Explica por qué se cancela el convenio.',
            'motivo_cancelacion.min' => 'El motivo debe describir claramente la cancelación.',
        ]);

        $tienePagos = $convenio->parcialidades()
            ->whereHas('pagos')
            ->exists();

        if ($tienePagos) {
            return redirect()->route('alumnos.convenios.show', [$alumno, $convenio])
                ->with('error', 'No se puede cancelar este convenio porque ya tiene pagos aplicados. Debe realizarse un ajuste administrativo documentado.');
        }

        DB::transaction(function () use ($alumno, $convenio, $validated) {
            $convenioBloqueado = Convenio::whereKey($convenio->id)->lockForUpdate()->firstOrFail();
            $convenioBloqueado->load('cargos');

            if ($convenioBloqueado->estatus !== 'Activo') {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'El convenio cambió de estado durante la operación. Actualiza la página.',
                ]);
            }

            if ($convenioBloqueado->parcialidades()->whereHas('pagos')->exists()) {
                throw ValidationException::withMessages([
                    'motivo_cancelacion' => 'Se registró un pago mientras se preparaba la cancelación. El convenio se conserva activo y debe revisarse mediante un ajuste administrativo documentado.',
                ]);
            }

            foreach ($convenioBloqueado->cargos as $cargo) {
                $cargoBloqueado = Cargo::whereKey($cargo->id)->lockForUpdate()->first();
                if ($cargoBloqueado && $cargoBloqueado->estatus === 'En Convenio') {
                    $cargoBloqueado->update([
                        'estatus' => $cargo->pivot->estatus_original ?: 'Pendiente',
                        'monto_adeudo' => $cargo->pivot->monto_adeudo_original,
                    ]);
                }
            }

            if ($convenioBloqueado->cargos->isEmpty() && $convenioBloqueado->cargoOriginal) {
                $cargoOriginal = Cargo::whereKey($convenioBloqueado->cargoOriginal->id)->lockForUpdate()->first();
                if ($cargoOriginal && $cargoOriginal->estatus === 'En Convenio') {
                    $cargoOriginal->update(['estatus' => 'Pendiente']);
                }
            }

            $convenioBloqueado->parcialidades()
                ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
                ->update(['estatus' => ParcialidadConvenio::ESTATUS_CANCELADA]);

            $convenioBloqueado->update([
                'estatus' => 'Cancelado',
                'cancelado_por_id' => auth()->id(),
                'fecha_cancelacion' => now(),
                'motivo_cancelacion' => $validated['motivo_cancelacion'],
            ]);

            $this->recalcularEstadoAlumno($alumno);

            $this->bitacora(
                'Cancelar Convenio',
                "Se canceló el convenio ID {$convenioBloqueado->id} del alumno {$alumno->nombre_completo} sin eliminar parcialidades ni cargos relacionados. Motivo: {$validated['motivo_cancelacion']}",
                'Convenios',
                $convenioBloqueado,
                $alumno->id
            );
        }, 3);

        return redirect()->route('alumnos.convenios.show', [$alumno, $convenio])
            ->with('success', 'Convenio cancelado. Los cargos fueron reactivados y el historial contractual se conservó.');
    }

    public function show($alumno_id, $convenio_id)
    {
        $alumno = Alumno::findOrFail($alumno_id);
        $convenio = $alumno->convenios()
            ->with(['parcialidades', 'cargos.concepto', 'canceladoPor'])
            ->findOrFail($convenio_id);

        return view('convenios.show', [
            'convenio' => $convenio,
            'alumno' => $alumno,
            'tienePagosAplicados' => $convenio->parcialidades()->whereHas('pagos')->exists(),
        ]);
    }


    public function pdf(Alumno $alumno, Convenio $convenio)
    {
        $this->verificarRelacion($alumno, $convenio);

        $convenio->load(['parcialidades', 'cargos.concepto']);

        $this->bitacora(
            'Generar Formato de Convenio',
            "Se generó formato PDF del convenio #{$convenio->id} del alumno {$alumno->nombre_completo}.",
            'Convenios',
            $convenio,
            $alumno->id
        );

        $pdf = Pdf::loadView('convenios.pdf', [
            'alumno' => $alumno,
            'convenio' => $convenio,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('convenio_'.str_replace(['/', '\\', ' '], '_', $alumno->matricula ?: $alumno->id).'_'.$convenio->id.'.pdf');
    }

    private function verificarRelacion(Alumno $alumno, Convenio $convenio): void
    {
        if ((int) $convenio->alumno_id !== (int) $alumno->id) {
            abort(404);
        }
    }

    /**
     * Distribuye el total en parcialidades y ajusta la última para evitar diferencias por redondeo.
     */
    private function calcularParcialidades(float $total, int $numeroParcialidades): array
    {
        $montoBase = round($total / $numeroParcialidades, 2);
        $montos = [];
        $acumulado = 0;

        for ($i = 1; $i <= $numeroParcialidades; $i++) {
            if ($i === $numeroParcialidades) {
                $monto = round($total - $acumulado, 2);
            } else {
                $monto = $montoBase;
                $acumulado = round($acumulado + $monto, 2);
            }

            $montos[] = $monto;
        }

        return $montos;
    }

    private function recalcularEstadoAlumno(Alumno $alumno): void
    {
        $tieneParcialidadesPendientes = ParcialidadConvenio::whereHas('convenio', function ($query) use ($alumno) {
                $query->where('alumno_id', $alumno->id);
            })
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
            ->exists();

        $tieneCargosPendientes = Cargo::where('alumno_id', $alumno->id)
            ->whereIn('estatus', ['Pendiente', 'Parcialmente Pagado'])
            ->exists();

        if ($tieneParcialidadesPendientes) {
            $estatusFinanciero = 'En Convenio';
            $condicionAlumno = 'En Convenio';
        } elseif ($tieneCargosPendientes) {
            $estatusFinanciero = 'Con Adeudo';
            $condicionAlumno = 'Normal';
        } elseif ((int) $alumno->beca_porcentaje > 0) {
            $estatusFinanciero = 'Becado';
            $condicionAlumno = 'Normal';
        } else {
            $estatusFinanciero = 'Al Corriente';
            $condicionAlumno = 'Normal';
        }

        $alumno->forceFill([
            'estatus_financiero' => $estatusFinanciero,
            'condicion_alumno' => $condicionAlumno,
        ])->save();
    }
}
