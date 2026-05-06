<?php

namespace App\Http\Controllers;

use App\Models\CalendarioAcademico;
use App\Models\CalendarioMateria;
use App\Models\CalendarioSesion;
use App\Models\DiaNoLaboral;
use App\Models\Docente;
use App\Models\Materia;
use App\Traits\RegistraBitacora;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarioMateriaController extends Controller
{
    use RegistraBitacora;

    public function create(CalendarioAcademico $calendarioAcademico)
    {
        $calendarioAcademico->load(['grupo.programa', 'cicloEscolar']);

        return view('calendarios_academicos.materias.create', $this->formData($calendarioAcademico, new CalendarioMateria()));
    }

    public function store(Request $request, CalendarioAcademico $calendarioAcademico)
    {
        $validated = $this->validar($request);
        $sesionesData = $this->obtenerSesiones($request, $calendarioAcademico, $validated);

        if (empty($sesionesData)) {
            throw ValidationException::withMessages([
                'sesiones' => 'Selecciona al menos una fecha desde el planeador del calendario.',
            ]);
        }

        $fechas = array_values(array_unique(array_column($sesionesData, 'fecha')));
        $this->validarDiasNoLaborales($fechas, $request->boolean('permitir_no_laboral'));
        $this->validarFechasDentroRangoPlaneacion($calendarioAcademico, $fechas);
        $this->validarDiasPermitidosPorTipo($calendarioAcademico, $fechas);
        $this->validarFechasDisponiblesEnCalendario($calendarioAcademico, $sesionesData);
        $this->validarConflictos($calendarioAcademico, $validated, $sesionesData);

        $materia = Materia::findOrFail($validated['materia_id']);
        $docente = Docente::findOrFail($validated['docente_id']);

        $calendarioMateria = DB::transaction(function () use ($calendarioAcademico, $validated, $sesionesData, $materia, $docente) {
            $calendarioMateria = CalendarioMateria::create([
                'calendario_academico_id' => $calendarioAcademico->id,
                'materia_id' => $materia->id,
                'docente_id' => $docente->id,
                'orden' => $validated['orden'],
                'nombre_materia_snapshot' => $materia->nombre,
                'docente_snapshot' => $docente->nombre_completo,
                'estatus' => $validated['estatus'],
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            $this->guardarSesiones($calendarioMateria, $validated, $sesionesData);

            return $calendarioMateria;
        });

        $this->bitacora(
            'Agregar materia a calendario',
            "Se agregó {$materia->nombre} con {$docente->nombre_completo} al calendario {$calendarioAcademico->nombre}.",
            'Área Académica',
            $calendarioMateria
        );

        return redirect()->route('calendarios_academicos.show', $calendarioAcademico)->with('success', 'Materia y sesiones agregadas correctamente.');
    }

    public function edit(CalendarioAcademico $calendarioAcademico, CalendarioMateria $calendarioMateria)
    {
        abort_unless($calendarioMateria->calendario_academico_id === $calendarioAcademico->id, 404);

        $calendarioAcademico->load(['grupo.programa', 'cicloEscolar']);
        $calendarioMateria->load('sesiones');

        return view('calendarios_academicos.materias.edit', $this->formData($calendarioAcademico, $calendarioMateria));
    }

    public function update(Request $request, CalendarioAcademico $calendarioAcademico, CalendarioMateria $calendarioMateria)
    {
        abort_unless($calendarioMateria->calendario_academico_id === $calendarioAcademico->id, 404);

        $validated = $this->validar($request);
        $sesionesData = $this->obtenerSesiones($request, $calendarioAcademico, $validated);

        if (empty($sesionesData)) {
            throw ValidationException::withMessages([
                'sesiones' => 'Selecciona al menos una fecha desde el planeador del calendario.',
            ]);
        }

        $fechas = array_values(array_unique(array_column($sesionesData, 'fecha')));
        $this->validarDiasNoLaborales($fechas, $request->boolean('permitir_no_laboral'));
        $this->validarFechasDentroRangoPlaneacion($calendarioAcademico, $fechas);
        $this->validarDiasPermitidosPorTipo($calendarioAcademico, $fechas);
        $this->validarFechasDisponiblesEnCalendario($calendarioAcademico, $sesionesData, $calendarioMateria->id);
        $this->validarConflictos($calendarioAcademico, $validated, $sesionesData, $calendarioMateria->id);

        $materia = Materia::findOrFail($validated['materia_id']);
        $docente = Docente::findOrFail($validated['docente_id']);

        DB::transaction(function () use ($calendarioAcademico, $calendarioMateria, $validated, $sesionesData, $materia, $docente) {
            $calendarioMateria->update([
                'materia_id' => $materia->id,
                'docente_id' => $docente->id,
                'orden' => $validated['orden'],
                'nombre_materia_snapshot' => $materia->nombre,
                'docente_snapshot' => $docente->nombre_completo,
                'estatus' => $validated['estatus'],
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            $calendarioMateria->sesiones()->delete();
            $this->guardarSesiones($calendarioMateria, $validated, $sesionesData);
        });

        $this->bitacora(
            'Actualizar materia de calendario',
            "Se actualizó {$materia->nombre} en el calendario {$calendarioAcademico->nombre}.",
            'Área Académica',
            $calendarioMateria
        );

        return redirect()->route('calendarios_academicos.show', $calendarioAcademico)->with('success', 'Materia y fechas actualizadas correctamente.');
    }

    public function destroy(CalendarioAcademico $calendarioAcademico, CalendarioMateria $calendarioMateria)
    {
        abort_unless($calendarioMateria->calendario_academico_id === $calendarioAcademico->id, 404);

        $descripcion = "Se eliminó {$calendarioMateria->nombre_materia} del calendario {$calendarioAcademico->nombre}.";
        $calendarioMateria->delete();

        $this->bitacora('Eliminar materia de calendario', $descripcion, 'Área Académica');

        return redirect()->route('calendarios_academicos.show', $calendarioAcademico)->with('success', 'Materia eliminada del calendario.');
    }

    private function formData(CalendarioAcademico $calendarioAcademico, CalendarioMateria $calendarioMateria): array
    {
        $aula = $calendarioAcademico->grupo->aula ?? '';
        $modalidad = $calendarioAcademico->modalidad;
        $tipoSesion = CalendarioSesion::TIPO_CLASE;
        $sesionesSeleccionadas = [];

        if ($calendarioMateria->exists) {
            $sesiones = $calendarioMateria->sesiones()->orderBy('fecha')->orderBy('hora_inicio')->get();
            $primeraSesion = $sesiones->first();

            $aula = $primeraSesion?->aula ?? $aula;
            $modalidad = $primeraSesion?->modalidad ?? $modalidad;
            $tipoSesion = $primeraSesion?->tipo_sesion ?? $tipoSesion;

            $sesionesSeleccionadas = $sesiones->map(fn ($s) => [
                'fecha' => $s->fecha->toDateString(),
                'hora_inicio' => substr((string) $s->hora_inicio, 0, 5),
                'hora_fin' => substr((string) $s->hora_fin, 0, 5),
                'observaciones' => $s->observaciones,
            ])->values()->all();
        }

        $previewSesionesPorFecha = $this->resumenSesionesParaPlaneador($calendarioAcademico, $calendarioMateria);

        $conteosPorFecha = collect($previewSesionesPorFecha)
            ->map(fn ($sesiones) => count($sesiones))
            ->toArray();

        $fechasBloqueadasMismoCalendario = collect($previewSesionesPorFecha)
            ->filter(fn ($sesiones) => collect($sesiones)->contains(fn ($s) => (bool) ($s['mismo_calendario'] ?? false)))
            ->keys()
            ->values()
            ->all();

        return [
            'calendario' => $calendarioAcademico,
            'calendarioMateria' => $calendarioMateria,
            'materias' => Materia::activas()->orderBy('nombre')->get(),
            'docentes' => Docente::where('estatus', 'Activo')->orderBy('nombre_completo')->get(),
            'modalidades' => [
                CalendarioAcademico::MODALIDAD_PRESENCIAL,
                CalendarioAcademico::MODALIDAD_VIRTUAL,
                CalendarioAcademico::MODALIDAD_MIXTA,
            ],
            'tiposSesion' => [
                CalendarioSesion::TIPO_CLASE,
                CalendarioSesion::TIPO_COLOQUIO,
                CalendarioSesion::TIPO_CONFERENCIA,
                CalendarioSesion::TIPO_EXAMEN,
                CalendarioSesion::TIPO_OTRO,
            ],
            'estatusesMateria' => [
                CalendarioMateria::ESTATUS_PROGRAMADA,
                CalendarioMateria::ESTATUS_CONFIRMADA,
                CalendarioMateria::ESTATUS_IMPARTIDA,
                CalendarioMateria::ESTATUS_CANCELADA,
            ],
            'aula' => $aula,
            'modalidadSeleccionada' => $modalidad,
            'tipoSesionSeleccionado' => $tipoSesion,
            'sesionesSeleccionadas' => $sesionesSeleccionadas,
            'mesesPlaneador' => $this->mesesPlaneador($calendarioAcademico, $sesionesSeleccionadas),
            'rangoPlaneacionTexto' => $this->textoRangoPlaneacion($calendarioAcademico, $sesionesSeleccionadas),
            'horariosPredefinidos' => $this->horariosPredefinidos(),
            'conteosPorFecha' => $conteosPorFecha,
            'previewSesionesPorFecha' => $previewSesionesPorFecha,
            'fechasBloqueadasMismoCalendario' => $fechasBloqueadasMismoCalendario,
            'diasNoLaborales' => DiaNoLaboral::activos()->get()->mapWithKeys(fn ($d) => [
                $d->fecha->toDateString() => $d->nombre,
            ])->toArray(),
            'diasPermitidosTipo' => CalendarioAcademico::diasPermitidosPorTipo($calendarioAcademico->tipo_calendario),
            'textoDiasPermitidosTipo' => CalendarioAcademico::textoDiasPermitidosPorTipo($calendarioAcademico->tipo_calendario),
        ];
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'materia_id' => 'required|exists:materias,id',
            'docente_id' => 'required|exists:docentes,id',
            'orden' => 'required|integer|min:1|max:100',
            'estatus' => 'required|in:Programada,Confirmada,Impartida,Cancelada',
            'observaciones' => 'nullable|string|max:3000',
            'aula' => 'nullable|string|max:100',
            'modalidad' => 'required|in:Presencial,Virtual,Mixta',
            'tipo_sesion' => 'required|in:Clase,Coloquio,Conferencia,Examen,Otro',
            'permitir_no_laboral' => 'nullable|boolean',
            'usar_horario_idej' => 'nullable|boolean',
            'sesiones' => 'nullable|array',
            'sesiones.*.fecha' => 'nullable|date',
            'sesiones.*.hora_inicio' => 'nullable|date_format:H:i',
            'sesiones.*.hora_fin' => 'nullable|date_format:H:i',
            'sesiones.*.observaciones' => 'nullable|string|max:1000',
            'fechas_texto' => 'nullable|string|max:5000',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i',
        ]);
    }

    private function obtenerSesiones(Request $request, CalendarioAcademico $calendario, array $data): array
    {
        $sesiones = [];
        $usarReglasIdej = $request->boolean('usar_horario_idej', true);

        foreach ((array) $request->input('sesiones', []) as $index => $sesion) {
            if (empty($sesion['fecha'])) {
                continue;
            }

            $fecha = Carbon::parse($sesion['fecha'])->toDateString();
            $horaInicio = $sesion['hora_inicio'] ?? null;
            $horaFin = $sesion['hora_fin'] ?? null;

            if ((!$horaInicio || !$horaFin) && $usarReglasIdej) {
                $horario = $this->horarioIdejParaFecha($calendario, $fecha, 'sesiones');
                $horaInicio = $horaInicio ?: $horario['inicio'];
                $horaFin = $horaFin ?: $horario['fin'];
            }

            $this->validarHorarioSesion($fecha, $horaInicio, $horaFin, "sesiones.{$index}.hora_inicio");

            $sesiones[] = [
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'observaciones' => $sesion['observaciones'] ?? null,
            ];
        }

        if (!empty($sesiones)) {
            return $this->ordenarSesionesUnicas($sesiones);
        }

        if (!empty($data['fechas_texto'])) {
            $fechas = $this->parsearFechas($data['fechas_texto'], $this->anioBase($calendario));
            if (empty($fechas)) {
                return [];
            }

            foreach ($fechas as $fecha) {
                if ($usarReglasIdej) {
                    $horario = $this->horarioIdejParaFecha($calendario, $fecha, 'fechas_texto');
                    $horaInicio = $horario['inicio'];
                    $horaFin = $horario['fin'];
                    $observaciones = $horario['nota'] ?? null;
                } else {
                    $horaInicio = $data['hora_inicio'] ?? null;
                    $horaFin = $data['hora_fin'] ?? null;
                    $observaciones = null;
                }

                $this->validarHorarioSesion($fecha, $horaInicio, $horaFin, 'hora_inicio');

                $sesiones[] = [
                    'fecha' => $fecha,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'observaciones' => $observaciones,
                ];
            }
        }

        return $this->ordenarSesionesUnicas($sesiones);
    }

    private function guardarSesiones(CalendarioMateria $calendarioMateria, array $validated, array $sesionesData): void
    {
        foreach ($sesionesData as $sesion) {
            CalendarioSesion::create([
                'calendario_materia_id' => $calendarioMateria->id,
                'fecha' => $sesion['fecha'],
                'hora_inicio' => $sesion['hora_inicio'],
                'hora_fin' => $sesion['hora_fin'],
                'aula' => $validated['aula'] ?? null,
                'modalidad' => $validated['modalidad'],
                'tipo_sesion' => $validated['tipo_sesion'],
                'estatus' => CalendarioSesion::ESTATUS_PROGRAMADA,
                'observaciones' => $sesion['observaciones'] ?? null,
            ]);
        }
    }

    private function validarHorarioSesion(string $fecha, ?string $horaInicio, ?string $horaFin, string $campo): void
    {
        if (!$horaInicio || !$horaFin) {
            throw ValidationException::withMessages([
                $campo => 'Cada fecha seleccionada debe tener hora de inicio y hora de fin.',
            ]);
        }

        if ($horaFin <= $horaInicio) {
            throw ValidationException::withMessages([
                $campo => 'La hora fin debe ser posterior a la hora inicio en la fecha '.Carbon::parse($fecha)->format('d/m/Y').'.',
            ]);
        }
    }

    private function ordenarSesionesUnicas(array $sesiones): array
    {
        $unicas = [];

        foreach ($sesiones as $sesion) {
            $key = $sesion['fecha'].'|'.$sesion['hora_inicio'].'|'.$sesion['hora_fin'];
            $unicas[$key] = $sesion;
        }

        $sesiones = array_values($unicas);
        usort($sesiones, fn ($a, $b) => [$a['fecha'], $a['hora_inicio']] <=> [$b['fecha'], $b['hora_inicio']]);

        return $sesiones;
    }

    private function horarioIdejParaFecha(CalendarioAcademico $calendario, string $fecha, string $campoError = 'sesiones'): array
    {
        $carbon = Carbon::parse($fecha);
        $dia = $carbon->dayOfWeekIso; // 1 lunes, 5 viernes, 6 sábado
        $tipo = $calendario->tipo_calendario ?: CalendarioAcademico::TIPO_PERSONALIZADO;
        $fechaVista = $carbon->format('d/m/Y');

        return match ($tipo) {
            CalendarioAcademico::TIPO_POSGRADO_VIERNES_SABADO => match ($dia) {
                5 => ['inicio' => '17:00', 'fin' => '21:00', 'nota' => 'Horario IDEJ posgrado viernes.'],
                6 => ['inicio' => '08:00', 'fin' => '13:00', 'nota' => 'Horario IDEJ posgrado sábado.'],
                default => throw ValidationException::withMessages([$campoError => "{$fechaVista} no corresponde a viernes o sábado para posgrado."]),
            },
            CalendarioAcademico::TIPO_LICENCIATURA_SABATINA => match ($dia) {
                6 => ['inicio' => '08:00', 'fin' => '13:00', 'nota' => 'Horario IDEJ licenciatura sabatina.'],
                default => throw ValidationException::withMessages([$campoError => "{$fechaVista} no corresponde a sábado para licenciatura sabatina."]),
            },
            CalendarioAcademico::TIPO_LICENCIATURA_MATUTINA => match ($dia) {
                1, 2, 3, 4, 5 => ['inicio' => '09:00', 'fin' => '12:00', 'nota' => 'Horario IDEJ licenciatura matutina.'],
                default => throw ValidationException::withMessages([$campoError => "{$fechaVista} no corresponde a lunes a viernes para licenciatura matutina."]),
            },
            CalendarioAcademico::TIPO_LICENCIATURA_VESPERTINA => match ($dia) {
                1, 2, 3, 4, 5 => ['inicio' => '18:00', 'fin' => '21:00', 'nota' => 'Horario IDEJ licenciatura vespertina.'],
                default => throw ValidationException::withMessages([$campoError => "{$fechaVista} no corresponde a lunes a viernes para licenciatura vespertina."]),
            },
            default => throw ValidationException::withMessages([
                $campoError => 'Este calendario es personalizado. Captura manualmente hora inicio y hora fin para cada fecha seleccionada.',
            ]),
        };
    }

    private function validarDiasNoLaborales(array $fechas, bool $permitir): void
    {
        $conflictos = DiaNoLaboral::activos()->whereIn('fecha', $fechas)->orderBy('fecha')->get();

        if ($conflictos->isNotEmpty() && !$permitir) {
            throw ValidationException::withMessages([
                'sesiones' => 'Hay fechas marcadas como no laborales en el catálogo: '.$conflictos->map(fn ($d) => $d->fecha->format('d/m/Y').' '.$d->nombre)->implode(', ').'. Si Coordinación Académica/Dirección autoriza esa excepción, marca la casilla de excepción.',
            ]);
        }
    }


    private function validarDiasPermitidosPorTipo(CalendarioAcademico $calendario, array $fechas): void
    {
        $permitidos = CalendarioAcademico::diasPermitidosPorTipo($calendario->tipo_calendario);

        if (count($permitidos) >= 7) {
            return;
        }

        $fuera = collect($fechas)
            ->map(fn ($fecha) => Carbon::parse($fecha))
            ->filter(fn ($fecha) => !in_array((int) $fecha->dayOfWeekIso, $permitidos, true))
            ->map(fn ($fecha) => $fecha->format('d/m/Y').' ('.$this->nombreDia((int) $fecha->dayOfWeekIso).')')
            ->values();

        if ($fuera->isNotEmpty()) {
            throw ValidationException::withMessages([
                'sesiones' => 'El tipo de calendario "'.($calendario->tipo_calendario ?: 'Personalizado').'" solo permite programar en '.CalendarioAcademico::textoDiasPermitidosPorTipo($calendario->tipo_calendario).'. Fechas no permitidas: '.$fuera->implode(', ').'. Para reposiciones fuera del patrón usa la opción Reprogramar sesión con autorización.',
            ]);
        }
    }

    private function nombreDia(int $diaIso): string
    {
        return [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miércoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sábado',
            7 => 'domingo',
        ][$diaIso] ?? 'día no identificado';
    }

    private function validarFechasDisponiblesEnCalendario(
        CalendarioAcademico $calendario,
        array $sesiones,
        ?int $ignorarCalendarioMateriaId = null
    ): void {
        $fechas = collect($sesiones)
            ->pluck('fecha')
            ->filter()
            ->unique()
            ->values();

        if ($fechas->isEmpty()) {
            return;
        }

        $ocupadas = CalendarioSesion::activos()
            ->with(['calendarioMateria.materia', 'calendarioMateria.docente'])
            ->whereIn('fecha', $fechas)
            ->whereHas('calendarioMateria', fn ($q) => $q
                ->where('calendario_academico_id', $calendario->id)
                ->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA])
            )
            ->when($ignorarCalendarioMateriaId, fn ($q) => $q->where('calendario_materia_id', '<>', $ignorarCalendarioMateriaId))
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        if ($ocupadas->isEmpty()) {
            return;
        }

        $detalle = $ocupadas
            ->groupBy(fn ($s) => $s->fecha->toDateString())
            ->map(function ($sesiones, $fecha) {
                $materias = $sesiones->map(function ($sesion) {
                    $materia = $sesion->calendarioMateria?->nombre_materia
                        ?? $sesion->calendarioMateria?->materia?->nombre
                        ?? 'Materia sin nombre';

                    $docente = $sesion->calendarioMateria?->nombre_docente
                        ?? $sesion->calendarioMateria?->docente?->nombre_completo
                        ?? 'Docente no asignado';

                    return "{$materia} con {$docente}";
                })->unique()->implode('; ');

                return Carbon::parse($fecha)->format('d/m/Y').": {$materias}";
            })
            ->values()
            ->implode(' | ');

        throw ValidationException::withMessages([
            'sesiones' => 'Estas fechas ya están ocupadas por otra materia dentro de este mismo calendario. Quita esas fechas o edita la materia que ya las tiene asignadas: '.$detalle,
        ]);
    }

    private function resumenSesionesParaPlaneador(
        CalendarioAcademico $calendarioAcademico,
        CalendarioMateria $calendarioMateria
    ): array {
        [$inicio, $finMes] = $this->rangoPlaneacion($calendarioAcademico);
        $fin = $finMes->copy()->endOfMonth();

        $sesiones = CalendarioSesion::activos()
            ->with([
                'calendarioMateria.calendario.grupo.programa',
                'calendarioMateria.materia',
                'calendarioMateria.docente',
            ])
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->whereHas('calendarioMateria.calendario', fn ($q) => $q->whereIn('tipo_calendario', $calendarioAcademico->tiposCompatiblesParaVistaPrevia()))
            ->when($calendarioMateria->exists, fn ($q) => $q->where('calendario_materia_id', '<>', $calendarioMateria->id))
            ->whereHas('calendarioMateria', fn ($q) => $q
                ->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA])
                ->whereHas('calendario', fn ($sub) => $sub
                    ->whereNotIn('estatus', [
                        CalendarioAcademico::ESTATUS_CANCELADO,
                        CalendarioAcademico::ESTATUS_FINALIZADO,
                    ])
                )
            )
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return $sesiones
            ->groupBy(fn ($s) => $s->fecha->toDateString())
            ->map(function ($items) use ($calendarioAcademico) {
                return $items->map(function ($s) use ($calendarioAcademico) {
                    $calendario = $s->calendarioMateria?->calendario;
                    $grupo = $calendario?->grupo;
                    $programa = $grupo?->programa;

                    return [
                        'id' => $s->id,
                        'mismo_calendario' => (int) ($s->calendarioMateria?->calendario_academico_id) === (int) $calendarioAcademico->id,
                        'calendario' => $calendario?->nombre ?? 'Calendario sin nombre',
                        'grupo' => trim(($programa?->nombre ? $programa->nombre.' · ' : '').($grupo?->nombre ?? 'Grupo sin nombre')),
                        'materia' => $s->calendarioMateria?->nombre_materia
                            ?? $s->calendarioMateria?->materia?->nombre
                            ?? 'Materia sin nombre',
                        'docente' => $s->calendarioMateria?->nombre_docente
                            ?? $s->calendarioMateria?->docente?->nombre_completo
                            ?? 'Docente no asignado',
                        'horario' => substr((string) $s->hora_inicio, 0, 5).' - '.substr((string) $s->hora_fin, 0, 5),
                        'aula' => $s->aula ?: 'Sin aula/liga',
                        'modalidad' => $s->modalidad ?: 'Sin modalidad',
                        'tipo_sesion' => $s->tipo_sesion ?: 'Clase',
                    ];
                })->values()->all();
            })
            ->toArray();
    }

    private function validarConflictos(CalendarioAcademico $calendario, array $data, array $sesiones, ?int $ignorarCalendarioMateriaId = null): void
    {
        foreach ($sesiones as $sesion) {
            $fecha = $sesion['fecha'];
            $horaInicio = $sesion['hora_inicio'];
            $horaFin = $sesion['hora_fin'];
            $fechaVista = Carbon::parse($fecha)->format('d/m/Y');

            $grupoTieneConflicto = CalendarioSesion::activos()
                ->whereDate('fecha', $fecha)
                ->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio)
                ->whereHas('calendarioMateria', fn ($q) => $q
                    ->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA])
                    ->whereHas('calendario', fn ($sub) => $sub->where('grupo_id', $calendario->grupo_id)->whereNotIn('estatus', [CalendarioAcademico::ESTATUS_CANCELADO, CalendarioAcademico::ESTATUS_FINALIZADO])))
                ->when($ignorarCalendarioMateriaId, fn ($q) => $q->where('calendario_materia_id', '<>', $ignorarCalendarioMateriaId))
                ->exists();

            if ($grupoTieneConflicto) {
                throw ValidationException::withMessages(['sesiones' => "El grupo ya tiene una sesión programada el {$fechaVista} en ese horario."]);
            }

            $docenteTieneConflicto = CalendarioSesion::activos()
                ->whereDate('fecha', $fecha)
                ->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio)
                ->whereHas('calendarioMateria', fn ($q) => $q->where('docente_id', $data['docente_id'])->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA]))
                ->when($ignorarCalendarioMateriaId, fn ($q) => $q->where('calendario_materia_id', '<>', $ignorarCalendarioMateriaId))
                ->exists();

            if ($docenteTieneConflicto) {
                throw ValidationException::withMessages(['docente_id' => "El docente ya tiene una sesión programada el {$fechaVista} en ese horario."]);
            }

            if (!empty($data['aula'])) {
                $aulaTieneConflicto = CalendarioSesion::activos()
                    ->whereDate('fecha', $fecha)
                    ->where('aula', $data['aula'])
                    ->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio)
                    ->when($ignorarCalendarioMateriaId, fn ($q) => $q->where('calendario_materia_id', '<>', $ignorarCalendarioMateriaId))
                    ->whereHas('calendarioMateria', fn ($q) => $q->whereNotIn('estatus', [CalendarioMateria::ESTATUS_CANCELADA]))
                    ->exists();

                if ($aulaTieneConflicto) {
                    throw ValidationException::withMessages(['aula' => "El aula ya está ocupada el {$fechaVista} en ese horario."]);
                }
            }
        }
    }

    private function mesesPlaneador(CalendarioAcademico $calendario, array $sesionesSeleccionadas = []): array
    {
        [$inicio, $fin] = $this->rangoPlaneacion($calendario, $sesionesSeleccionadas);

        $meses = [];
        $cursor = $inicio->copy()->startOfMonth();
        $fin = $fin->copy()->startOfMonth();
        $limite = 18;

        while ($cursor->lte($fin) && count($meses) < $limite) {
            $meses[] = $this->estructuraMes($cursor);
            $cursor->addMonth();
        }

        return $meses;
    }

    private function rangoPlaneacion(CalendarioAcademico $calendario, array $sesionesSeleccionadas = []): array
    {
        $rangoPeriodo = $this->rangoSugeridoPorPeriodo($calendario);
        $inicio = $calendario->fecha_inicio?->copy()->startOfMonth();
        $fin = $calendario->fecha_fin?->copy()->startOfMonth();

        /*
         * Regla IDEJ:
         * fecha_inicio/fecha_fin representan el periodo de planeación del calendario,
         * NO el rango de sesiones ya capturadas. En versiones anteriores se actualizaron
         * erróneamente con la primera materia guardada, dejando visible solo julio u otro mes.
         * Para calendarios IDEJ no personalizados recuperamos el rango por periodo cuando el
         * rango guardado está anormalmente comprimido.
         */
        if ($inicio && $fin) {
            $rangoMuyCorto = $inicio->diffInMonths($fin) < 2;

            if ($rangoMuyCorto && $rangoPeriodo) {
                [$inicio, $fin] = $rangoPeriodo;
            }
        } elseif ($rangoPeriodo) {
            [$inicio, $fin] = $rangoPeriodo;
        } else {
            $anio = $this->anioBase($calendario);
            $inicio = Carbon::create($anio, 3, 1)->startOfMonth();
            $fin = Carbon::create($anio, 9, 1)->startOfMonth();
        }

        $fechasSeleccionadas = collect($sesionesSeleccionadas)
            ->pluck('fecha')
            ->filter()
            ->map(fn ($fecha) => Carbon::parse($fecha)->startOfMonth());

        if ($fechasSeleccionadas->isNotEmpty()) {
            $min = $fechasSeleccionadas->min();
            $max = $fechasSeleccionadas->max();
            if ($min->lt($inicio)) {
                $inicio = $min;
            }
            if ($max->gt($fin)) {
                $fin = $max;
            }
        }

        return [$inicio->copy()->startOfMonth(), $fin->copy()->startOfMonth()];
    }

    private function rangoSugeridoPorPeriodo(CalendarioAcademico $calendario): ?array
    {
        $anio = $this->anioBase($calendario);
        $periodo = mb_strtoupper((string) $calendario->periodo, 'UTF-8');

        if (str_contains($periodo, 'B')) {
            return [
                Carbon::create($anio, 9, 1)->startOfMonth(),
                Carbon::create($anio + 1, 2, 1)->startOfMonth(),
            ];
        }

        if (str_contains($periodo, 'A') || preg_match('/20\d{2}/', $periodo)) {
            return [
                Carbon::create($anio, 3, 1)->startOfMonth(),
                Carbon::create($anio, 9, 1)->startOfMonth(),
            ];
        }

        return null;
    }

    private function textoRangoPlaneacion(CalendarioAcademico $calendario, array $sesionesSeleccionadas = []): string
    {
        [$inicio, $fin] = $this->rangoPlaneacion($calendario, $sesionesSeleccionadas);
        return $inicio->format('d/m/Y').' al '.$fin->copy()->endOfMonth()->format('d/m/Y');
    }

    private function validarFechasDentroRangoPlaneacion(CalendarioAcademico $calendario, array $fechas): void
    {
        [$inicio, $finMes] = $this->rangoPlaneacion($calendario);
        $fin = $finMes->copy()->endOfMonth();

        $fuera = collect($fechas)
            ->map(fn ($fecha) => Carbon::parse($fecha))
            ->filter(fn ($fecha) => $fecha->lt($inicio) || $fecha->gt($fin))
            ->map(fn ($fecha) => $fecha->format('d/m/Y'))
            ->values();

        if ($fuera->isNotEmpty()) {
            throw ValidationException::withMessages([
                'sesiones' => 'Estas fechas están fuera del rango de planeación del calendario ('.$inicio->format('d/m/Y').' al '.$fin->format('d/m/Y').'): '.$fuera->implode(', ').'. Edita el calendario si el periodo debe ampliarse.',
            ]);
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

    private function estructuraMes(Carbon $fecha): array
    {
        $inicio = $fecha->copy()->startOfMonth();
        $fin = $fecha->copy()->endOfMonth();
        $offset = $inicio->dayOfWeekIso - 1;
        $dias = [];

        for ($i = 0; $i < $offset; $i++) {
            $dias[] = null;
        }

        for ($dia = 1; $dia <= $fin->day; $dia++) {
            $actual = $inicio->copy()->day($dia);
            $dias[] = [
                'fecha' => $actual->toDateString(),
                'dia' => $dia,
                'dia_semana' => $actual->dayOfWeekIso,
            ];
        }

        return [
            'titulo' => $this->nombreMes($fecha->month).' '.$fecha->year,
            'dias' => $dias,
        ];
    }

    private function actualizarRangoCalendario(CalendarioAcademico $calendario): void
    {
        $fechas = CalendarioSesion::whereHas('calendarioMateria', fn ($q) => $q->where('calendario_academico_id', $calendario->id));

        $calendario->update([
            'fecha_inicio' => (clone $fechas)->min('fecha'),
            'fecha_fin' => (clone $fechas)->max('fecha'),
        ]);
    }

    private function anioBase(CalendarioAcademico $calendario): int
    {
        if (preg_match('/(20\d{2})/', (string) $calendario->periodo, $m)) {
            return (int) $m[1];
        }

        return (int) ($calendario->fecha_inicio?->year ?? now()->year);
    }

    private function parsearFechas(string $texto, int $anioBase): array
    {
        $fechas = [];
        $texto = str_replace(['–', '—'], '-', $texto);

        if (preg_match_all('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/u', $texto, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                try {
                    $fechas[] = Carbon::createFromDate((int) $match[3], (int) $match[2], (int) $match[1])->toDateString();
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        $normalizado = $this->normalizarTexto($texto);
        $normalizado = preg_replace('/([A-Z]+)-(\d)/u', '$1 $2', $normalizado);
        $tokens = preg_split('/\s+/', str_replace([',', ';', '/', "\n", "\r", '.'], ' ', $normalizado));
        $pendientes = [];
        $meses = $this->meses();

        foreach ($tokens as $token) {
            $token = trim($token, " \t\n\r\0\x0B-");
            if ($token === '') {
                continue;
            }

            if (isset($meses[$token])) {
                foreach ($pendientes as $dia) {
                    try {
                        $fechas[] = Carbon::createFromDate($anioBase, $meses[$token], $dia)->toDateString();
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
                $pendientes = [];
                continue;
            }

            if (preg_match('/^\d{1,2}(?:-\d{1,2})*$/', $token)) {
                foreach (explode('-', $token) as $dia) {
                    $dia = (int) $dia;
                    if ($dia >= 1 && $dia <= 31) {
                        $pendientes[] = $dia;
                    }
                }
            }
        }

        $fechas = array_values(array_unique($fechas));
        sort($fechas);

        return $fechas;
    }

    private function normalizarTexto(string $texto): string
    {
        $texto = str_replace(['–', '—'], '-', $texto);
        $texto = mb_strtoupper($texto, 'UTF-8');
        $buscar = ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'];
        $reemplazar = ['A', 'E', 'I', 'O', 'U', 'U'];
        return str_replace($buscar, $reemplazar, $texto);
    }

    private function meses(): array
    {
        return [
            'ENE' => 1, 'ENERO' => 1,
            'FEB' => 2, 'FEBRERO' => 2,
            'MAR' => 3, 'MARZO' => 3,
            'ABR' => 4, 'ABRIL' => 4,
            'MAY' => 5, 'MAYO' => 5,
            'JUN' => 6, 'JUNIO' => 6,
            'JUL' => 7, 'JULIO' => 7,
            'AGO' => 8, 'AGOSTO' => 8,
            'SEP' => 9, 'SEPT' => 9, 'SEPTIEMBRE' => 9,
            'OCT' => 10, 'OCTUBRE' => 10,
            'NOV' => 11, 'NOVIEMBRE' => 11,
            'DIC' => 12, 'DICIEMBRE' => 12,
        ];
    }

    private function nombreMes(int $mes): string
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ][$mes];
    }
}
