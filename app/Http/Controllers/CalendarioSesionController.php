<?php

namespace App\Http\Controllers;

use App\Models\CalendarioAcademico;
use App\Models\CalendarioMateria;
use App\Models\CalendarioSesion;
use App\Models\DiaNoLaboral;
use App\Traits\RegistraBitacora;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarioSesionController extends Controller
{
    use RegistraBitacora;

    public function cancelar(CalendarioAcademico $calendarioAcademico, CalendarioSesion $calendarioSesion)
    {
        $this->validarSesionDelCalendario($calendarioAcademico, $calendarioSesion);
        $calendarioSesion->load(['calendarioMateria.materia', 'calendarioMateria.docente']);

        return view('calendarios_academicos.sesiones.cancelar', [
            'calendario' => $calendarioAcademico,
            'sesion' => $calendarioSesion,
        ]);
    }

    public function cancelarStore(Request $request, CalendarioAcademico $calendarioAcademico, CalendarioSesion $calendarioSesion)
    {
        $this->validarSesionDelCalendario($calendarioAcademico, $calendarioSesion);

        $data = $request->validate([
            'motivo_cancelacion' => 'required|string|min:5|max:3000',
        ]);

        if ($calendarioSesion->estatus === CalendarioSesion::ESTATUS_CANCELADA) {
            return redirect()->route('calendarios_academicos.show', $calendarioAcademico)
                ->with('info', 'La sesión ya estaba cancelada.');
        }

        $calendarioSesion->update([
            'estatus' => CalendarioSesion::ESTATUS_CANCELADA,
            'cancelada_por_id' => Auth::id(),
            'motivo_cancelacion' => $data['motivo_cancelacion'],
        ]);

        $this->bitacora(
            'Cancelar sesión académica',
            "Se canceló la sesión {$calendarioSesion->fecha->format('d/m/Y')} del calendario {$calendarioAcademico->nombre}. Motivo: {$data['motivo_cancelacion']}",
            'Área Académica',
            $calendarioSesion
        );

        return redirect()->route('calendarios_academicos.show', $calendarioAcademico)
            ->with('success', 'Sesión cancelada correctamente. Si requiere reposición, usa Reprogramar.');
    }

    public function reprogramar(CalendarioAcademico $calendarioAcademico, CalendarioSesion $calendarioSesion)
    {
        $this->validarSesionDelCalendario($calendarioAcademico, $calendarioSesion);
        $calendarioSesion->load(['calendarioMateria.materia', 'calendarioMateria.docente']);

        return view('calendarios_academicos.sesiones.reprogramar', [
            'calendario' => $calendarioAcademico,
            'sesion' => $calendarioSesion,
            'horariosPredefinidos' => $this->horariosPredefinidos(),
            'textoDiasPermitidos' => CalendarioAcademico::textoDiasPermitidosPorTipo($calendarioAcademico->tipo_calendario),
        ]);
    }

    public function reprogramarStore(Request $request, CalendarioAcademico $calendarioAcademico, CalendarioSesion $calendarioSesion)
    {
        $this->validarSesionDelCalendario($calendarioAcademico, $calendarioSesion);

        $data = $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'aula' => 'nullable|string|max:100',
            'modalidad' => 'required|in:Presencial,Virtual,Mixta',
            'motivo_reprogramacion' => 'required|string|min:5|max:3000',
            'permitir_no_laboral' => 'nullable|boolean',
            'permitir_fuera_patron' => 'nullable|boolean',
        ]);

        if ($calendarioSesion->estatus === CalendarioSesion::ESTATUS_CANCELADA && $calendarioSesion->reposiciones()->exists()) {
            return redirect()->route('calendarios_academicos.show', $calendarioAcademico)
                ->with('info', 'Esta sesión ya fue reprogramada previamente.');
        }

        $fecha = Carbon::parse($data['fecha']);
        $this->validarFechaDentroRango($calendarioAcademico, $fecha);
        $this->validarDiaPermitido($calendarioAcademico, $fecha, $request->boolean('permitir_fuera_patron'));
        $this->validarDiaNoLaboral($fecha, $request->boolean('permitir_no_laboral'));
        $this->validarConflictos($calendarioAcademico, $calendarioSesion, $data);

        $nuevaSesion = DB::transaction(function () use ($calendarioSesion, $data) {
            if ($calendarioSesion->estatus !== CalendarioSesion::ESTATUS_CANCELADA) {
                $calendarioSesion->update([
                    'estatus' => CalendarioSesion::ESTATUS_CANCELADA,
                    'cancelada_por_id' => Auth::id(),
                    'motivo_cancelacion' => 'Cancelada por reprogramación: '.$data['motivo_reprogramacion'],
                ]);
            }

            return CalendarioSesion::create([
                'sesion_origen_id' => $calendarioSesion->id,
                'calendario_materia_id' => $calendarioSesion->calendario_materia_id,
                'fecha' => Carbon::parse($data['fecha'])->toDateString(),
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'aula' => $data['aula'] ?? $calendarioSesion->aula,
                'modalidad' => $data['modalidad'],
                'tipo_sesion' => $calendarioSesion->tipo_sesion,
                'estatus' => CalendarioSesion::ESTATUS_PROGRAMADA,
                'observaciones' => 'Reposición de la sesión del '.$calendarioSesion->fecha->format('d/m/Y'),
                'reprogramada_por_id' => Auth::id(),
                'fecha_reprogramacion' => now(),
                'motivo_reprogramacion' => $data['motivo_reprogramacion'],
            ]);
        });

        $this->bitacora(
            'Reprogramar sesión académica',
            "Se reprogramó la sesión {$calendarioSesion->fecha->format('d/m/Y')} para {$nuevaSesion->fecha->format('d/m/Y')} en el calendario {$calendarioAcademico->nombre}. Motivo: {$data['motivo_reprogramacion']}",
            'Área Académica',
            $nuevaSesion
        );

        return redirect()->route('calendarios_academicos.show', $calendarioAcademico)
            ->with('success', 'Sesión reprogramada correctamente y con trazabilidad.');
    }

    private function validarSesionDelCalendario(CalendarioAcademico $calendario, CalendarioSesion $sesion): void
    {
        $sesion->loadMissing('calendarioMateria');
        abort_unless((int) $sesion->calendarioMateria?->calendario_academico_id === (int) $calendario->id, 404);
    }

    private function validarFechaDentroRango(CalendarioAcademico $calendario, Carbon $fecha): void
    {
        if (!$calendario->fecha_inicio || !$calendario->fecha_fin) {
            return;
        }

        if ($fecha->lt($calendario->fecha_inicio->copy()->startOfDay()) || $fecha->gt($calendario->fecha_fin->copy()->endOfDay())) {
            throw ValidationException::withMessages([
                'fecha' => 'La fecha de reposición está fuera del rango del calendario. Amplía el calendario o elige una fecha dentro del periodo.',
            ]);
        }
    }

    private function validarDiaPermitido(CalendarioAcademico $calendario, Carbon $fecha, bool $permitirFueraPatron): void
    {
        $permitidos = CalendarioAcademico::diasPermitidosPorTipo($calendario->tipo_calendario);

        if (count($permitidos) >= 7 || in_array((int) $fecha->dayOfWeekIso, $permitidos, true)) {
            return;
        }

        if ($permitirFueraPatron) {
            return;
        }

        throw ValidationException::withMessages([
            'fecha' => 'Esta fecha no corresponde al patrón del calendario. El patrón permite '.CalendarioAcademico::textoDiasPermitidosPorTipo($calendario->tipo_calendario).'. Marca la autorización de reposición fuera del patrón si fue validado por Coordinación Académica/Dirección.',
        ]);
    }

    private function validarDiaNoLaboral(Carbon $fecha, bool $permitir): void
    {
        $diaNoLaboral = DiaNoLaboral::activos()->whereDate('fecha', $fecha->toDateString())->first();

        if ($diaNoLaboral && !$permitir) {
            throw ValidationException::withMessages([
                'fecha' => 'La fecha seleccionada está marcada como día no laboral: '.$diaNoLaboral->nombre.'. Marca autorización si Dirección/Coordinación Académica validó la reposición.',
            ]);
        }
    }

    private function validarConflictos(CalendarioAcademico $calendario, CalendarioSesion $sesionOriginal, array $data): void
    {
        $fecha = Carbon::parse($data['fecha'])->toDateString();
        $horaInicio = $data['hora_inicio'];
        $horaFin = $data['hora_fin'];
        $materia = $sesionOriginal->calendarioMateria;

        $mismoCalendarioOcupado = CalendarioSesion::activos()
            ->whereDate('fecha', $fecha)
            ->whereHas('calendarioMateria', fn ($q) => $q
                ->where('calendario_academico_id', $calendario->id)
                ->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA])
            )
            ->where('id', '<>', $sesionOriginal->id)
            ->exists();

        if ($mismoCalendarioOcupado) {
            throw ValidationException::withMessages([
                'fecha' => 'Esta fecha ya está ocupada por otra materia dentro del mismo calendario. Elige otra fecha para la reposición.',
            ]);
        }

        $docenteTieneConflicto = CalendarioSesion::activos()
            ->whereDate('fecha', $fecha)
            ->where('hora_inicio', '<', $horaFin)
            ->where('hora_fin', '>', $horaInicio)
            ->whereHas('calendarioMateria', fn ($q) => $q->where('docente_id', $materia->docente_id)->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA]))
            ->where('id', '<>', $sesionOriginal->id)
            ->exists();

        if ($docenteTieneConflicto) {
            throw ValidationException::withMessages([
                'hora_inicio' => 'El docente ya tiene una clase programada en esa fecha y horario.',
            ]);
        }

        if (!empty($data['aula'])) {
            $aulaOcupada = CalendarioSesion::activos()
                ->whereDate('fecha', $fecha)
                ->where('aula', $data['aula'])
                ->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio)
                ->where('id', '<>', $sesionOriginal->id)
                ->exists();

            if ($aulaOcupada) {
                throw ValidationException::withMessages([
                    'aula' => 'El aula o liga ya está ocupada en esa fecha y horario.',
                ]);
            }
        }
    }

    private function horariosPredefinidos(): array
    {
        return [
            ['label' => '05:00 pm - 09:00 pm', 'inicio' => '17:00', 'fin' => '21:00'],
            ['label' => '08:00 am - 01:00 pm', 'inicio' => '08:00', 'fin' => '13:00'],
            ['label' => '05:00 pm - 08:00 pm', 'inicio' => '17:00', 'fin' => '20:00'],
            ['label' => '09:00 am - 11:00 am', 'inicio' => '09:00', 'fin' => '11:00'],
            ['label' => '09:00 am - 01:00 pm', 'inicio' => '09:00', 'fin' => '13:00'],
        ];
    }
}
