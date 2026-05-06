<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\Convenio;
use App\Models\ParcialidadConvenio;
use App\Traits\RegistraBitacora;
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
     * Eliminar convenio y reactivar todos los cargos que lo originaron.
     */
    public function destroy(Alumno $alumno, Convenio $convenio)
    {
        $this->verificarRelacion($alumno, $convenio);

        if ($convenio->estatus === 'Finalizado') {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('info', 'Este convenio ya ha sido completado y no puede eliminarse.');
        }

        $tienePagos = $convenio->parcialidades()
            ->whereIn('estatus', ['Pagado', 'Parcialmente Pagado'])
            ->exists();

        if ($tienePagos) {
            return redirect()
                ->route('alumnos.show', $alumno)
                ->with('error', 'No se puede eliminar un convenio con pagos registrados.');
        }

        DB::transaction(function () use ($alumno, $convenio) {
            $convenio->load('cargos');

            foreach ($convenio->cargos as $cargo) {
                $cargo->update([
                    'estatus' => $cargo->pivot->estatus_original ?: 'Pendiente',
                    'monto_adeudo' => $cargo->pivot->monto_adeudo_original,
                ]);
            }

            // Compatibilidad con convenios creados antes de la tabla pivote.
            if ($convenio->cargos->isEmpty() && $convenio->cargoOriginal) {
                $convenio->cargoOriginal->update(['estatus' => 'Pendiente']);
            }

            $convenioId = $convenio->id;
            $convenio->parcialidades()->delete();
            $convenio->cargos()->detach();
            $convenio->delete();

            $this->recalcularEstadoAlumno($alumno);

            $this->bitacora(
                'Eliminar Convenio',
                "Se eliminó el convenio ID {$convenioId} del alumno {$alumno->nombre_completo} y se reactivaron sus cargos relacionados."
            );
        }, 3);

        return redirect()
            ->route('alumnos.show', $alumno)
            ->with('success', 'Convenio eliminado correctamente y cargos reactivados.');
    }

    public function show($alumno_id, $convenio_id)
    {
        $alumno = Alumno::findOrFail($alumno_id);
        $convenio = $alumno->convenios()
            ->with(['parcialidades', 'cargos.concepto'])
            ->findOrFail($convenio_id);

        return view('convenios.show', [
            'convenio' => $convenio,
            'alumno' => $alumno,
        ]);
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
