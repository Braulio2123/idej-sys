<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Beca;
use App\Models\Rol;
use App\Models\Usuario;
use App\Traits\RegistraBitacora;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BecaController extends Controller
{
    use RegistraBitacora;

    public function index(Request $request)
    {
        $this->actualizarEstatusesPorFecha();

        $query = Beca::with(['alumno.grupo.programa', 'autorizadoPor', 'registradoPor'])
            ->orderByRaw("FIELD(estatus, 'Activa', 'Programada', 'Vencida', 'Cancelada')")
            ->orderByDesc('fecha_inicio');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('alumno', function ($q) use ($buscar) {
                $q->where('nombre_completo', 'like', "%{$buscar}%")
                  ->orWhere('matricula', 'like', "%{$buscar}%")
                  ->orWhere('correo', 'like', "%{$buscar}%");
            });
        }

        $becas = $query->paginate(15)->appends($request->query());
        $estatusDisponibles = Beca::estatusDisponibles();

        return view('becas.index', compact('becas', 'estatusDisponibles'));
    }

    public function alumnoIndex(Alumno $alumno)
    {
        $this->actualizarEstatusesPorFecha($alumno);
        $this->sincronizarBecaActual($alumno->fresh());

        $becas = $alumno->becas()
            ->with(['autorizadoPor', 'registradoPor', 'canceladoPor', 'cargos.concepto'])
            ->orderByRaw("FIELD(estatus, 'Activa', 'Programada', 'Vencida', 'Cancelada')")
            ->orderByDesc('fecha_inicio')
            ->paginate(10);

        return view('alumnos.becas_index', compact('alumno', 'becas'));
    }

    public function create(Alumno $alumno)
    {
        $usuariosAutorizadores = Usuario::whereHas('rol', function ($query) {
                $query->whereIn('clave', [Rol::ADMIN, Rol::DIRECCION, Rol::CADMIN, Rol::FINANZAS]);
            })
            ->orderBy('nombre')
            ->get();

        $tipos = Beca::tiposDisponibles();

        return view('becas.create', compact('alumno', 'usuariosAutorizadores', 'tipos'));
    }

    public function store(Request $request, Alumno $alumno)
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:80',
            'porcentaje' => 'required|integer|min:1|max:100',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string|max:2000',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'autorizado_por_id' => 'nullable|exists:usuarios,id',
        ]);

        $beca = DB::transaction(function () use ($validated, $alumno) {
            $fechaInicio = Carbon::parse($validated['fecha_inicio'])->startOfDay();
            $fechaFin = ! empty($validated['fecha_fin']) ? Carbon::parse($validated['fecha_fin'])->endOfDay() : null;
            $hoy = now()->startOfDay();

            $estatus = $fechaInicio->greaterThan($hoy)
                ? Beca::ESTATUS_PROGRAMADA
                : Beca::ESTATUS_ACTIVA;

            if ($fechaFin && $fechaFin->lt($hoy)) {
                $estatus = Beca::ESTATUS_VENCIDA;
            }

            // Evita becas activas/programadas traslapadas para el mismo alumno.
            $nuevaFechaInicio = $validated['fecha_inicio'];
            $nuevaFechaFin = $validated['fecha_fin'] ?? null;

            $alumno->becas()
                ->whereIn('estatus', [Beca::ESTATUS_ACTIVA, Beca::ESTATUS_PROGRAMADA])
                ->where(function ($query) use ($nuevaFechaInicio, $nuevaFechaFin) {
                    $query->whereNull('fecha_fin')
                        ->orWhereDate('fecha_fin', '>=', $nuevaFechaInicio);
                })
                ->when($nuevaFechaFin, function ($query) use ($nuevaFechaFin) {
                    $query->whereDate('fecha_inicio', '<=', $nuevaFechaFin);
                })
                ->update([
                    'estatus' => Beca::ESTATUS_CANCELADA,
                    'cancelado_por_id' => Auth::id(),
                    'fecha_cancelacion' => now(),
                    'motivo_cancelacion' => 'Sustituida por una nueva beca institucional con vigencia traslapada.',
                ]);

            $beca = $alumno->becas()->create([
                'tipo' => $validated['tipo'],
                'porcentaje' => $validated['porcentaje'],
                'motivo' => $validated['motivo'],
                'observaciones' => $validated['observaciones'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'] ?? null,
                'estatus' => $estatus,
                'autorizado_por_id' => $validated['autorizado_por_id'] ?? null,
                'registrado_por_id' => Auth::id(),
            ]);

            $this->sincronizarBecaActual($alumno->fresh());

            return $beca;
        });

        $this->bitacora(
            'Registrar Beca',
            "Se registró una beca {$beca->porcentaje}% para el alumno {$alumno->nombre_completo}. Motivo: {$beca->motivo}."
        );

        return redirect()
            ->route('alumnos.becas.index', $alumno)
            ->with('success', 'Beca registrada correctamente. Se aplicará a cargos becables creados durante su vigencia.');
    }

    public function confirmarCancelacion(Alumno $alumno, Beca $beca)
    {
        $this->validarBecaDelAlumno($alumno, $beca);

        if ($beca->estaCancelada()) {
            return redirect()
                ->route('alumnos.becas.index', $alumno)
                ->with('error', 'La beca ya está cancelada.');
        }

        return view('becas.cancelar', compact('alumno', 'beca'));
    }

    public function cancelar(Request $request, Alumno $alumno, Beca $beca)
    {
        $this->validarBecaDelAlumno($alumno, $beca);

        if ($beca->estaCancelada()) {
            return redirect()
                ->route('alumnos.becas.index', $alumno)
                ->with('error', 'La beca ya está cancelada.');
        }

        $validated = $request->validate([
            'motivo_cancelacion' => 'required|string|min:10|max:2000',
        ]);

        DB::transaction(function () use ($alumno, $beca, $validated) {
            $beca->update([
                'estatus' => Beca::ESTATUS_CANCELADA,
                'cancelado_por_id' => Auth::id(),
                'fecha_cancelacion' => now(),
                'motivo_cancelacion' => $validated['motivo_cancelacion'],
            ]);

            $this->sincronizarBecaActual($alumno->fresh());
        });

        $this->bitacora(
            'Cancelar Beca',
            "Se canceló la beca ID {$beca->id} del alumno {$alumno->nombre_completo}. Motivo: {$validated['motivo_cancelacion']}"
        );

        return redirect()
            ->route('alumnos.becas.index', $alumno)
            ->with('success', 'Beca cancelada correctamente. Los cargos ya generados conservan su historial de descuento aplicado.');
    }

    public function sincronizar(Request $request)
    {
        $this->actualizarEstatusesPorFecha();

        $total = 0;

        Alumno::with('becas')->chunk(100, function ($alumnos) use (&$total) {
            foreach ($alumnos as $alumno) {
                $this->sincronizarBecaActual($alumno);
                $total++;
            }
        });

        return redirect()
            ->route('becas.index')
            ->with('success', "Sincronización completada para {$total} alumnos.");
    }

    private function actualizarEstatusesPorFecha(?Alumno $alumno = null): void
    {
        $hoy = now()->toDateString();

        $activar = Beca::query();
        $vencer = Beca::query();

        if ($alumno) {
            $activar->where('alumno_id', $alumno->id);
            $vencer->where('alumno_id', $alumno->id);
        }

        $activar
            ->where('estatus', Beca::ESTATUS_PROGRAMADA)
            ->whereDate('fecha_inicio', '<=', $hoy)
            ->where(function ($query) use ($hoy) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $hoy);
            })
            ->update(['estatus' => Beca::ESTATUS_ACTIVA]);

        $vencer
            ->whereIn('estatus', [Beca::ESTATUS_ACTIVA, Beca::ESTATUS_PROGRAMADA])
            ->whereNotNull('fecha_fin')
            ->whereDate('fecha_fin', '<', $hoy)
            ->update(['estatus' => Beca::ESTATUS_VENCIDA]);
    }

    private function validarBecaDelAlumno(Alumno $alumno, Beca $beca): void
    {
        abort_unless((int) $beca->alumno_id === (int) $alumno->id, 404);
    }

    private function sincronizarBecaActual(Alumno $alumno): void
    {
        $becaVigente = $alumno->becas()->vigentes()->orderByDesc('fecha_inicio')->first();

        if ($becaVigente) {
            $alumno->forceFill([
                'beca_porcentaje' => $becaVigente->porcentaje,
                'condicion_alumno' => 'Becado',
            ])->save();

            return;
        }

        if ($alumno->condicion_alumno !== 'En Convenio') {
            $alumno->forceFill([
                'beca_porcentaje' => 0,
                'condicion_alumno' => 'Normal',
            ])->save();
        } else {
            $alumno->forceFill(['beca_porcentaje' => 0])->save();
        }
    }
}
