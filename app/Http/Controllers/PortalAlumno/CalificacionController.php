<?php

namespace App\Http\Controllers\PortalAlumno;

use App\Http\Controllers\Controller;
use App\Models\CalendarioMateria;
use App\Models\HorarioAcademico;
use App\Models\PortalAlumno\AlumnoPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Portal Alumno PWA - Christian
 *
 * Sección de calificaciones del alumno.
 *
 * IMPORTANTE:
 * Este controlador pertenece exclusivamente al Portal Alumno.
 * No modifica ni reemplaza controladores administrativos del área académica.
 *
 * Estado actual:
 * El sistema base aún no contiene una tabla formal de calificaciones del alumno.
 * Por eso esta primera versión muestra las materias reales asociadas al grupo
 * del alumno y deja preparado el espacio visual para conectar calificaciones
 * cuando el módulo académico las tenga disponibles.
 */
class CalificacionController extends Controller
{
    /**
     * Muestra la vista de calificaciones del alumno autenticado.
     */
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Alumno autenticado del Portal Alumno
        |--------------------------------------------------------------------------
        |
        | Se obtiene el ID desde el guard separado "portal_alumno" y después
        | se consulta con el modelo AlumnoPortal. Esto evita mezclar el portal
        | con el modelo administrativo App\Models\Alumno.
        |
        */
        $alumno = AlumnoPortal::query()
            ->with(['grupo.programa'])
            ->findOrFail(Auth::guard('portal_alumno')->id());

        $materias = collect();

        /*
        |--------------------------------------------------------------------------
        | Materias asociadas al grupo del alumno
        |--------------------------------------------------------------------------
        |
        | Se consultan dos fuentes existentes del sistema:
        |
        | 1. Calendario académico.
        | 2. Horarios académicos.
        |
        | Después se normalizan como arreglos simples para evitar conflictos
        | entre colecciones Eloquent y colecciones base.
        |
        */
        if ($alumno->grupo_id) {
            $desdeCalendario = collect(
                CalendarioMateria::query()
                    ->with(['materia', 'docente', 'calendario'])
                    ->whereHas('calendario', function ($query) use ($alumno) {
                        $query->where('grupo_id', $alumno->grupo_id);
                    })
                    ->where('estatus', '!=', CalendarioMateria::ESTATUS_CANCELADA)
                    ->orderBy('orden')
                    ->get()
                    ->map(function (CalendarioMateria $calendarioMateria) {
                        return [
                            'nombre' => $calendarioMateria->nombre_materia ?? 'Materia sin nombre',
                            'docente' => $calendarioMateria->nombre_docente ?? 'Docente pendiente',
                            'origen' => 'calendario',
                        ];
                    })
                    ->all()
            );

            $desdeHorario = collect(
                HorarioAcademico::query()
                    ->with(['materia', 'docente'])
                    ->activos()
                    ->where('grupo_id', $alumno->grupo_id)
                    ->get()
                    ->unique('materia_id')
                    ->map(function (HorarioAcademico $horario) {
                        return [
                            'nombre' => $horario->materia->nombre ?? 'Materia sin nombre',
                            'docente' => $horario->docente->nombre_completo ?? 'Docente pendiente',
                            'origen' => 'horario',
                        ];
                    })
                    ->all()
            );

            $materias = $desdeCalendario
                ->concat($desdeHorario)
                ->unique('nombre')
                ->values();
        }

        return view('portal_alumno.calificaciones.index', compact('alumno', 'materias'));
    }
}
