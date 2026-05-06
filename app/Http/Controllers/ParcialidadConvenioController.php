<?php

namespace App\Http\Controllers;

use App\Models\Convenio;
use App\Models\ParcialidadConvenio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\RegistraBitacora;

class ParcialidadConvenioController extends Controller
{
    use RegistraBitacora;

    /**
     * Mostrar todas las parcialidades de un convenio.
     */
    public function index(Convenio $convenio)
    {
        $parcialidades = $convenio->parcialidades()
            ->orderBy('fecha_vencimiento')
            ->get();

        $alumno = $convenio->alumno; // relación definida en Convenio

        return view('convenios.parcialidades.index', [
            'convenio'      => $convenio,
            'alumno'        => $alumno,
            'parcialidades' => $parcialidades,
        ]);
    }

    /**
     * Formulario para crear una nueva parcialidad manual.
     */
    public function create(Convenio $convenio)
    {
        $parcialidad = new ParcialidadConvenio();
        $alumno      = $convenio->alumno;

        return view('convenios.parcialidades.create', compact(
            'convenio',
            'alumno',
            'parcialidad'
        ));
    }

    /**
     * Guardar una nueva parcialidad.
     */
    public function store(Request $request, Convenio $convenio)
    {
        $validated = $request->validate([
            'monto_parcialidad' => 'required|numeric|min:0.01',
            'fecha_vencimiento' => 'required|date',
        ]);

        // Validar que la suma de parcialidades no exceda el total reestructurado
        $sumaExistente = $convenio->parcialidades()->sum('monto_parcialidad');
        $nuevoTotal    = $sumaExistente + $validated['monto_parcialidad'];

        if ($nuevoTotal > $convenio->total_reestructurado) {
            return back()
                ->withErrors([
                    'monto_parcialidad' =>
                        'La suma de las parcialidades supera el total reestructurado del convenio ($' .
                        number_format($convenio->total_reestructurado, 2) . ').',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $convenio) {
            $parcialidad = ParcialidadConvenio::create([
                'convenio_id'       => $convenio->id,
                'monto_parcialidad' => $validated['monto_parcialidad'],
                'monto_adeudo'      => $validated['monto_parcialidad'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
                'estatus'           => 'Pendiente',
            ]);

            // 🧾 Bitácora
            $alumno = $convenio->alumno;

            $this->bitacora(
                'Crear Parcialidad de Convenio',
                "Se agregó una nueva parcialidad (ID {$parcialidad->id}) al convenio ID {$convenio->id} " .
                "del alumno {$alumno->nombre_completo}."
            );
        });

        return redirect()
            ->route('parcialidades.index', $convenio)
            ->with('success', 'Parcialidad creada correctamente.');
    }

    /**
     * Formulario para editar una parcialidad.
     */
    public function edit(Convenio $convenio, ParcialidadConvenio $parcialidad)
    {
        $this->verificarRelacion($convenio, $parcialidad);

        if ($parcialidad->estatus === 'Pagado') {
            return redirect()
                ->route('parcialidades.index', $convenio)
                ->with('info', 'No puedes editar una parcialidad ya pagada.');
        }

        $alumno = $convenio->alumno;

        return view('convenios.parcialidades.edit', compact(
            'convenio',
            'alumno',
            'parcialidad'
        ));
    }

    /**
     * Actualizar una parcialidad.
     */
    public function update(
        Request $request,
        Convenio $convenio,
        ParcialidadConvenio $parcialidad
    ) {
        $this->verificarRelacion($convenio, $parcialidad);

        if ($parcialidad->estatus === 'Pagado') {
            return redirect()
                ->route('parcialidades.index', $convenio)
                ->with('info', 'No puedes editar una parcialidad ya pagada.');
        }

        $validated = $request->validate([
            'monto_parcialidad' => 'required|numeric|min:0.01',
            'fecha_vencimiento' => 'required|date',
        ]);

        // Validar que la suma de parcialidades no exceda el total reestructurado
        $sumaOtras = $convenio->parcialidades()
            ->where('id', '!=', $parcialidad->id)
            ->sum('monto_parcialidad');

        $nuevoTotal = $sumaOtras + $validated['monto_parcialidad'];

        if ($nuevoTotal > $convenio->total_reestructurado) {
            return back()
                ->withErrors([
                    'monto_parcialidad' =>
                        'La suma de las parcialidades supera el total reestructurado del convenio ($' .
                        number_format($convenio->total_reestructurado, 2) . ').',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $convenio, $parcialidad) {

            // Si el monto adeudo es mayor al nuevo monto de la parcialidad,
            // lo acotamos para evitar montos negativos
            if ($parcialidad->monto_adeudo > $validated['monto_parcialidad']) {
                $parcialidad->monto_adeudo = $validated['monto_parcialidad'];
            }

            $parcialidad->update([
                'monto_parcialidad' => $validated['monto_parcialidad'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'],
            ]);

            // Si ya está en 0, aseguramos estatus Pagado/Pendiente
            if ($parcialidad->monto_adeudo <= 0) {
                $parcialidad->estatus = 'Pagado';
                $parcialidad->save();
            }

            // 🧾 Bitácora
            $alumno = $convenio->alumno;

            $this->bitacora(
                'Editar Parcialidad de Convenio',
                "Se editó la parcialidad ID {$parcialidad->id} del convenio ID {$convenio->id} " .
                "del alumno {$alumno->nombre_completo}."
            );
        });

        return redirect()
            ->route('parcialidades.index', $convenio)
            ->with('success', 'Parcialidad actualizada correctamente.');
    }

    /**
     * Eliminar una parcialidad (solo si no tiene pagos ligados).
     */
    public function destroy(Convenio $convenio, ParcialidadConvenio $parcialidad)
    {
        $this->verificarRelacion($convenio, $parcialidad);

        // Verificar si tiene registros en pago_parcialidad
        $tienePagos = DB::table('pago_parcialidad')
            ->where('parcialidad_id', $parcialidad->id)
            ->exists();

        if ($tienePagos) {
            return redirect()
                ->route('parcialidades.index', $convenio)
                ->with('error', 'No puedes eliminar una parcialidad que ya tiene pagos registrados.');
        }

        if ($parcialidad->estatus === 'Pagado') {
            return redirect()
                ->route('parcialidades.index', $convenio)
                ->with('error', 'No puedes eliminar una parcialidad pagada.');
        }

        DB::transaction(function () use ($convenio, $parcialidad) {

            $idParcialidad = $parcialidad->id;
            $alumno        = $convenio->alumno;

            $parcialidad->delete();

            // 🧾 Bitácora
            $this->bitacora(
                'Eliminar Parcialidad de Convenio',
                "Se eliminó la parcialidad ID {$idParcialidad} del convenio ID {$convenio->id} " .
                "del alumno {$alumno->nombre_completo}."
            );
        });

        return redirect()
            ->route('parcialidades.index', $convenio)
            ->with('success', 'Parcialidad eliminada correctamente.');
    }

    /**
     * Helper: asegurar que la parcialidad pertenece al convenio.
     */
    protected function verificarRelacion(Convenio $convenio, ParcialidadConvenio $parcialidad): void
    {
        if ($parcialidad->convenio_id !== $convenio->id) {
            abort(404);
        }
    }
}
