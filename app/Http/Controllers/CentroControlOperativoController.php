<?php

namespace App\Http\Controllers;

use App\Models\CalendarioMateria;
use App\Models\CalendarioSesion;
use App\Models\CursoEducacionContinua;
use App\Models\CursoSesion;
use App\Models\SolicitudPagoDocente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CentroControlOperativoController extends Controller
{
    public function index(Request $request)
    {
        [$fechaInicio, $fechaFin, $rangoSeleccionado] = $this->resolverRango($request);

        $origen = $request->input('origen', 'todos');
        $soloCriticos = $request->boolean('solo_criticos');

        $sesiones = $this->sesionesOperativas($fechaInicio, $fechaFin, $origen);

        $conflictosDocente = $this->detectarConflictos($sesiones, 'docente_key', 'Conflicto de docente', 'alta');
        $conflictosLugar = $this->detectarConflictos($sesiones, 'lugar_key', 'Conflicto de aula/liga', 'alta');
        $sesionesIncompletas = $this->detectarSesionesIncompletas($sesiones);
        $cancelacionesSinReposicion = $this->cancelacionesSinReposicion($fechaInicio, $fechaFin, $origen);
        $cursosSinSesiones = $this->cursosSinSesiones();
        $alertasSolicitudes = $this->alertasSolicitudesDocentes($fechaInicio, $fechaFin, $origen);
        $pendientesGenerarSolicitud = $this->pendientesGenerarSolicitud($fechaInicio, $fechaFin, $origen);

        $alertas = collect()
            ->merge($conflictosDocente)
            ->merge($conflictosLugar)
            ->merge($sesionesIncompletas)
            ->merge($cancelacionesSinReposicion)
            ->merge($cursosSinSesiones)
            ->merge($alertasSolicitudes)
            ->merge($pendientesGenerarSolicitud)
            ->sortByDesc(fn (array $alerta) => $this->pesoSeveridad($alerta['severidad'] ?? 'baja'))
            ->values();

        if ($soloCriticos) {
            $alertas = $alertas->filter(fn (array $alerta) => ($alerta['severidad'] ?? null) === 'alta')->values();
        }

        $resumen = [
            'sesiones_revisadas' => $sesiones->count(),
            'alertas_total' => $alertas->count(),
            'criticas' => $alertas->where('severidad', 'alta')->count(),
            'medias' => $alertas->where('severidad', 'media')->count(),
            'bajas' => $alertas->where('severidad', 'baja')->count(),
            'conflictos_docente' => $conflictosDocente->count(),
            'conflictos_lugar' => $conflictosLugar->count(),
            'incompletas' => $sesionesIncompletas->count(),
            'cancelaciones_sin_reposicion' => $cancelacionesSinReposicion->count(),
            'solicitudes' => $alertasSolicitudes->count(),
            'pendientes_generar_solicitud' => $pendientesGenerarSolicitud->count(),
        ];

        $alertasPorTipo = $alertas->countBy('tipo')->sortKeys();
        $alertasPorSeveridad = $alertas->countBy('severidad');

        $rangos = [
            'hoy' => 'Hoy',
            'manana' => 'Mañana',
            'semana' => 'Esta semana',
            '15_dias' => 'Próximos 15 días',
            'mes' => 'Próximo mes',
            'personalizado' => 'Personalizado',
        ];

        $origenes = [
            'todos' => 'Todo',
            'principal' => 'Calendarios principales',
            'educacion_continua' => 'Educación Continua',
        ];

        return view('centro_control.index', compact(
            'alertas',
            'alertasPorTipo',
            'alertasPorSeveridad',
            'resumen',
            'fechaInicio',
            'fechaFin',
            'rangoSeleccionado',
            'rangos',
            'origen',
            'origenes',
            'soloCriticos'
        ));
    }

    private function resolverRango(Request $request): array
    {
        $hoy = Carbon::today();
        $rango = $request->input('rango', '15_dias');

        if ($rango === 'personalizado') {
            $inicio = $request->date('fecha_inicio')?->startOfDay() ?? $hoy->copy();
            $fin = $request->date('fecha_fin')?->endOfDay() ?? $inicio->copy()->addDays(15)->endOfDay();

            if ($fin->lt($inicio)) {
                $fin = $inicio->copy()->endOfDay();
            }

            return [$inicio, $fin, $rango];
        }

        return match ($rango) {
            'hoy' => [$hoy->copy(), $hoy->copy()->endOfDay(), $rango],
            'manana' => [$hoy->copy()->addDay(), $hoy->copy()->addDay()->endOfDay(), $rango],
            'semana' => [$hoy->copy(), $hoy->copy()->endOfWeek()->endOfDay(), $rango],
            'mes' => [$hoy->copy(), $hoy->copy()->addMonth()->endOfDay(), $rango],
            default => [$hoy->copy(), $hoy->copy()->addDays(15)->endOfDay(), '15_dias'],
        };
    }

    private function sesionesOperativas(Carbon $inicio, Carbon $fin, string $origen): Collection
    {
        $sesionesPrincipales = in_array($origen, ['todos', 'principal'], true)
            ? $this->sesionesCalendarioPrincipal($inicio, $fin)
            : collect();

        $sesionesEducacionContinua = in_array($origen, ['todos', 'educacion_continua'], true)
            ? $this->sesionesEducacionContinua($inicio, $fin)
            : collect();

        return $sesionesPrincipales
            ->merge($sesionesEducacionContinua)
            ->sortBy(fn (array $sesion) => $sesion['fecha']->format('Y-m-d').' '.$sesion['hora_inicio'])
            ->values();
    }

    private function sesionesCalendarioPrincipal(Carbon $inicio, Carbon $fin): Collection
    {
        return CalendarioSesion::query()
            ->with([
                'calendarioMateria.calendario.grupo.programa',
                'calendarioMateria.materia',
                'calendarioMateria.docente',
            ])
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->whereNotIn('estatus', [CalendarioSesion::ESTATUS_CANCELADA, CalendarioSesion::ESTATUS_SUSPENDIDA])
            ->whereHas('calendarioMateria.calendario', fn ($q) => $q->operativos())
            ->get()
            ->map(function (CalendarioSesion $sesion) {
                $materiaCalendario = $sesion->calendarioMateria;
                $calendario = $materiaCalendario?->calendario;
                $grupo = $calendario?->grupo;
                $programa = $grupo?->programa;
                $docenteKey = $materiaCalendario?->docente_id
                    ? 'docente_id:'.$materiaCalendario->docente_id
                    : $this->normalizarRecurso($materiaCalendario?->nombre_docente);

                return [
                    'origen' => 'principal',
                    'origen_label' => 'Calendario principal',
                    'modelo' => CalendarioSesion::class,
                    'id' => $sesion->id,
                    'fecha' => $sesion->fecha,
                    'hora_inicio' => $this->horaCorta($sesion->hora_inicio),
                    'hora_fin' => $this->horaCorta($sesion->hora_fin),
                    'inicio_min' => $this->minutos($sesion->hora_inicio),
                    'fin_min' => $this->minutos($sesion->hora_fin),
                    'titulo' => $materiaCalendario?->nombre_materia ?? 'Materia sin nombre',
                    'subtitulo' => $calendario?->nombre ?? 'Calendario académico',
                    'grupo_curso' => trim(($programa?->nombre ? $programa->nombre.' · ' : '').($grupo?->nombre ?? 'Grupo no asignado')),
                    'docente' => $materiaCalendario?->nombre_docente ?? 'Docente no asignado',
                    'docente_key' => $docenteKey,
                    'lugar' => $sesion->aula ?: 'Aula/liga pendiente',
                    'lugar_key' => $this->normalizarRecurso($sesion->aula),
                    'modalidad' => $sesion->modalidad ?: ($calendario?->modalidad ?? 'Sin modalidad'),
                    'estatus' => $sesion->estatus,
                    'url' => $calendario ? route('calendarios_academicos.show', $calendario) : null,
                ];
            });
    }

    private function sesionesEducacionContinua(Carbon $inicio, Carbon $fin): Collection
    {
        return CursoSesion::query()
            ->with(['curso', 'docente'])
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->whereNotIn('estatus', [CursoSesion::ESTATUS_CANCELADA])
            ->whereHas('curso', fn ($q) => $q->operativos())
            ->get()
            ->map(function (CursoSesion $sesion) {
                $curso = $sesion->curso;
                $docenteKey = $sesion->docente_id
                    ? 'docente_id:'.$sesion->docente_id
                    : $this->normalizarRecurso($sesion->expositor_nombre);

                return [
                    'origen' => 'educacion_continua',
                    'origen_label' => 'Educación Continua',
                    'modelo' => CursoSesion::class,
                    'id' => $sesion->id,
                    'fecha' => $sesion->fecha,
                    'hora_inicio' => $this->horaCorta($sesion->hora_inicio),
                    'hora_fin' => $this->horaCorta($sesion->hora_fin),
                    'inicio_min' => $this->minutos($sesion->hora_inicio),
                    'fin_min' => $this->minutos($sesion->hora_fin),
                    'titulo' => $curso?->nombre ?? 'Curso sin nombre',
                    'subtitulo' => $curso?->tipo ?? 'Educación Continua',
                    'grupo_curso' => $curso?->nombre ?? 'Curso',
                    'docente' => $sesion->expositor,
                    'docente_key' => $docenteKey,
                    'lugar' => $sesion->aula_liga ?: 'Aula/liga pendiente',
                    'lugar_key' => $this->normalizarRecurso($sesion->aula_liga),
                    'modalidad' => $sesion->modalidad ?: ($curso?->modalidad ?? 'Sin modalidad'),
                    'estatus' => $sesion->estatus,
                    'url' => $curso ? route('educacion_continua.show', $curso) : null,
                ];
            });
    }

    private function detectarConflictos(Collection $sesiones, string $campo, string $tipo, string $severidad): Collection
    {
        $alertas = collect();

        $sesiones
            ->filter(fn (array $sesion) => ! empty($sesion[$campo]) && $sesion['inicio_min'] !== null && $sesion['fin_min'] !== null)
            ->groupBy(fn (array $sesion) => $sesion['fecha']->toDateString().'|'.$sesion[$campo])
            ->each(function (Collection $grupo) use ($alertas, $tipo, $severidad, $campo) {
                $items = $grupo->values();

                for ($i = 0; $i < $items->count(); $i++) {
                    for ($j = $i + 1; $j < $items->count(); $j++) {
                        $a = $items[$i];
                        $b = $items[$j];

                        if (! $this->horariosSeTraslapan($a, $b)) {
                            continue;
                        }

                        $recurso = $campo === 'docente_key' ? $a['docente'] : $a['lugar'];

                        $alertas->push([
                            'tipo' => $tipo,
                            'severidad' => $severidad,
                            'titulo' => $tipo.' detectado',
                            'detalle' => 'El recurso “'.$recurso.'” está asignado en dos sesiones que se empalman.',
                            'fecha' => $a['fecha'],
                            'hora' => $a['hora_inicio'].' - '.$a['hora_fin'],
                            'recurso' => $recurso,
                            'items' => [$a, $b],
                            'acciones' => ['Revisar agenda', 'Reprogramar una sesión', 'Cambiar docente/aula/liga'],
                        ]);
                    }
                }
            });

        return $alertas;
    }

    private function detectarSesionesIncompletas(Collection $sesiones): Collection
    {
        return $sesiones
            ->flatMap(function (array $sesion) {
                $alertas = [];

                if ($sesion['inicio_min'] === null || $sesion['fin_min'] === null || $sesion['fin_min'] <= $sesion['inicio_min']) {
                    $alertas[] = $this->alertaSesion(
                        'Sesión con horario incompleto',
                        'alta',
                        'La sesión no tiene horario válido de inicio y fin.',
                        $sesion,
                        ['Capturar hora de inicio', 'Capturar hora de fin', 'Guardar nuevamente']
                    );
                }

                if (empty($sesion['docente_key']) || Str::contains(mb_strtolower($sesion['docente']), ['no asignado', 'no disponible'])) {
                    $alertas[] = $this->alertaSesion(
                        'Sesión sin docente/expositor',
                        'media',
                        'La sesión todavía no tiene docente o expositor confiable asignado.',
                        $sesion,
                        ['Asignar docente', 'Confirmar expositor', 'Actualizar agenda']
                    );
                }

                if (empty($sesion['lugar_key'])) {
                    $alertas[] = $this->alertaSesion(
                        'Sesión sin aula/liga',
                        'media',
                        'La sesión no tiene aula física ni liga virtual capturada.',
                        $sesion,
                        ['Asignar aula', 'Capturar liga virtual', 'Avisar a Recepción/Sistemas']
                    );
                }

                $modalidad = mb_strtolower((string) $sesion['modalidad']);
                $lugar = mb_strtolower((string) $sesion['lugar']);

                if ((str_contains($modalidad, 'virtual') || str_contains($modalidad, 'mixta')) && ! str_contains($lugar, 'zoom') && ! str_contains($lugar, 'meet') && ! str_contains($lugar, 'http')) {
                    $alertas[] = $this->alertaSesion(
                        'Sesión virtual/mixta sin liga clara',
                        'baja',
                        'La modalidad indica clase virtual o mixta, pero el aula/liga no parece contener una liga o plataforma.',
                        $sesion,
                        ['Capturar liga Zoom/Meet', 'Confirmar aula virtual', 'Validar con Sistemas']
                    );
                }

                return $alertas;
            })
            ->values();
    }

    private function cancelacionesSinReposicion(Carbon $inicio, Carbon $fin, string $origen): Collection
    {
        if (! in_array($origen, ['todos', 'principal'], true)) {
            return collect();
        }

        return CalendarioSesion::query()
            ->with([
                'calendarioMateria.calendario.grupo.programa',
                'calendarioMateria.materia',
                'calendarioMateria.docente',
            ])
            ->withCount('reposiciones')
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->whereIn('estatus', [CalendarioSesion::ESTATUS_CANCELADA, CalendarioSesion::ESTATUS_SUSPENDIDA])
            ->whereNull('sesion_origen_id')
            ->having('reposiciones_count', '=', 0)
            ->get()
            ->map(function (CalendarioSesion $sesion) {
                $materiaCalendario = $sesion->calendarioMateria;
                $calendario = $materiaCalendario?->calendario;

                return [
                    'tipo' => 'Cancelación sin reposición',
                    'severidad' => 'media',
                    'titulo' => 'Clase cancelada/suspendida sin reposición vinculada',
                    'detalle' => 'La sesión fue cancelada o suspendida y no tiene una reposición registrada desde el sistema.',
                    'fecha' => $sesion->fecha,
                    'hora' => $this->horaCorta($sesion->hora_inicio).' - '.$this->horaCorta($sesion->hora_fin),
                    'recurso' => $materiaCalendario?->nombre_materia ?? 'Materia sin nombre',
                    'items' => [[
                        'origen_label' => 'Calendario principal',
                        'titulo' => $materiaCalendario?->nombre_materia ?? 'Materia sin nombre',
                        'subtitulo' => $calendario?->nombre ?? 'Calendario académico',
                        'grupo_curso' => $calendario?->grupo?->nombre ?? 'Grupo no asignado',
                        'docente' => $materiaCalendario?->nombre_docente ?? 'Docente no asignado',
                        'lugar' => $sesion->aula ?: 'Aula/liga pendiente',
                        'modalidad' => $sesion->modalidad,
                        'estatus' => $sesion->estatus,
                        'url' => $calendario ? route('calendarios_academicos.show', $calendario) : null,
                    ]],
                    'acciones' => ['Registrar reposición', 'Documentar motivo', 'Validar impacto académico'],
                ];
            });
    }

    private function cursosSinSesiones(): Collection
    {
        return CursoEducacionContinua::query()
            ->operativos()
            ->withCount(['sesiones as sesiones_activas_count' => fn ($q) => $q->whereNotIn('estatus', [CursoSesion::ESTATUS_CANCELADA])])
            ->having('sesiones_activas_count', '=', 0)
            ->get()
            ->map(fn (CursoEducacionContinua $curso) => [
                'tipo' => 'Curso sin sesiones',
                'severidad' => 'media',
                'titulo' => 'Curso operativo sin sesiones activas',
                'detalle' => 'El curso está activo/planeado, pero no tiene sesiones activas registradas.',
                'fecha' => $curso->fecha_inicio,
                'hora' => 'Pendiente',
                'recurso' => $curso->nombre,
                'items' => [[
                    'origen_label' => 'Educación Continua',
                    'titulo' => $curso->nombre,
                    'subtitulo' => $curso->tipo,
                    'grupo_curso' => $curso->nombre,
                    'docente' => 'Responsable pendiente de revisar',
                    'lugar' => 'Pendiente',
                    'modalidad' => $curso->modalidad,
                    'estatus' => $curso->estatus,
                    'url' => route('educacion_continua.show', $curso),
                ]],
                'acciones' => ['Generar sesiones', 'Definir horarios', 'Confirmar responsable'],
            ]);
    }

    private function alertasSolicitudesDocentes(Carbon $inicio, Carbon $fin, string $origen): Collection
    {
        $query = SolicitudPagoDocente::query()
            ->with(['docente', 'calendarioMateria.calendario', 'curso', 'cursoSesion'])
            ->whereNotIn('estatus', [SolicitudPagoDocente::ESTATUS_CANCELADA, SolicitudPagoDocente::ESTATUS_PAGADA])
            ->where(function ($q) use ($inicio, $fin) {
                $q->whereBetween('fecha_solicitud', [$inicio->toDateString(), $fin->toDateString()])
                    ->orWhereBetween('fecha_limite_pago', [$inicio->toDateString(), $fin->toDateString()])
                    ->orWhere('fecha_limite_pago', '<', now()->toDateString());
            });

        if ($origen === 'principal') {
            $query->where('origen', SolicitudPagoDocente::ORIGEN_CALENDARIO);
        } elseif ($origen === 'educacion_continua') {
            $query->where('origen', SolicitudPagoDocente::ORIGEN_EDUCACION_CONTINUA);
        }

        return $query->get()
            ->map(function (SolicitudPagoDocente $solicitud) {
                $vencida = $solicitud->fecha_limite_pago && $solicitud->fecha_limite_pago->lt(now()->startOfDay());
                $autorizada = $solicitud->estatus === SolicitudPagoDocente::ESTATUS_AUTORIZADA;
                $observada = $solicitud->estatus === SolicitudPagoDocente::ESTATUS_OBSERVADA;

                return [
                    'tipo' => 'Solicitud docente pendiente',
                    'severidad' => $vencida && $autorizada ? 'alta' : ($observada ? 'media' : 'baja'),
                    'titulo' => $vencida && $autorizada ? 'Solicitud docente autorizada vencida sin pago' : 'Solicitud docente requiere seguimiento',
                    'detalle' => 'La solicitud '.$solicitud->folio.' está en estatus '.$solicitud->estatus.'.',
                    'fecha' => $solicitud->fecha_limite_pago ?? $solicitud->fecha_solicitud,
                    'hora' => 'No aplica',
                    'recurso' => $solicitud->docente?->nombre_completo ?? 'Docente no disponible',
                    'items' => [[
                        'origen_label' => $solicitud->origen,
                        'titulo' => $solicitud->folio ?: 'Solicitud sin folio',
                        'subtitulo' => $solicitud->materia_actividad ?: $solicitud->concepto_pago,
                        'grupo_curso' => $solicitud->programa_grupo ?: $solicitud->periodo,
                        'docente' => $solicitud->docente?->nombre_completo ?? 'Docente no disponible',
                        'lugar' => 'Administración/Finanzas',
                        'modalidad' => $solicitud->modalidad ?: 'No aplica',
                        'estatus' => $solicitud->estatus,
                        'url' => route('solicitudes_pago.show', $solicitud),
                    ]],
                    'acciones' => $vencida && $autorizada
                        ? ['Registrar pago', 'Verificar comprobante', 'Avisar a Finanzas']
                        : ['Revisar solicitud', 'Atender observación o autorización', 'Dar seguimiento'],
                ];
            });
    }

    private function pendientesGenerarSolicitud(Carbon $inicio, Carbon $fin, string $origen): Collection
    {
        $alertas = collect();

        if (in_array($origen, ['todos', 'principal'], true)) {
            $materiasConSesiones = CalendarioMateria::query()
                ->with(['calendario.grupo.programa', 'materia', 'docente'])
                ->whereHas('sesiones', function ($q) use ($inicio, $fin) {
                    $q->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
                        ->whereNotIn('estatus', [CalendarioSesion::ESTATUS_CANCELADA, CalendarioSesion::ESTATUS_SUSPENDIDA]);
                })
                ->whereDoesntHave('calendario', fn ($q) => $q->whereIn('estatus', ['Cancelado', 'Finalizado']))
                ->whereDoesntHave('solicitudesPagoDocenteOperativas')
                ->limit(50)
                ->get();

            $alertas = $alertas->merge($materiasConSesiones->map(function (CalendarioMateria $materiaCalendario) {
                $calendario = $materiaCalendario->calendario;
                $grupo = $calendario?->grupo;
                $programa = $grupo?->programa;

                return [
                    'tipo' => 'Solicitud docente por generar',
                    'severidad' => 'baja',
                    'titulo' => 'Materia con sesiones sin solicitud docente vinculada',
                    'detalle' => 'La materia tiene sesiones operativas, pero no se encontró solicitud de pago docente vinculada.',
                    'fecha' => $calendario?->fecha_inicio,
                    'hora' => 'No aplica',
                    'recurso' => $materiaCalendario->nombre_docente,
                    'items' => [[
                        'origen_label' => 'Calendario principal',
                        'titulo' => $materiaCalendario->nombre_materia,
                        'subtitulo' => $calendario?->nombre ?? 'Calendario académico',
                        'grupo_curso' => trim(($programa?->nombre ? $programa->nombre.' · ' : '').($grupo?->nombre ?? 'Grupo no asignado')),
                        'docente' => $materiaCalendario->nombre_docente,
                        'lugar' => 'Académica',
                        'modalidad' => $calendario?->modalidad ?? 'No definida',
                        'estatus' => $materiaCalendario->estatus,
                        'url' => $calendario ? route('calendarios_academicos.show', $calendario) : null,
                    ]],
                    'acciones' => ['Validar si aplica pago docente', 'Crear solicitud si corresponde', 'Documentar excepción si no aplica'],
                ];
            }));
        }

        if (in_array($origen, ['todos', 'educacion_continua'], true)) {
            $sesionesSinSolicitud = CursoSesion::query()
                ->with(['curso', 'docente'])
                ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
                ->whereNotIn('estatus', [CursoSesion::ESTATUS_CANCELADA])
                ->whereHas('curso', fn ($q) => $q->operativos())
                ->whereDoesntHave('solicitudesPagoDocenteOperativas')
                ->limit(50)
                ->get();

            $alertas = $alertas->merge($sesionesSinSolicitud->map(fn (CursoSesion $sesion) => [
                'tipo' => 'Solicitud docente por generar',
                'severidad' => 'baja',
                'titulo' => 'Sesión de Educación Continua sin solicitud docente vinculada',
                'detalle' => 'La sesión está programada, pero no tiene solicitud docente operativa relacionada.',
                'fecha' => $sesion->fecha,
                'hora' => $this->horaCorta($sesion->hora_inicio).' - '.$this->horaCorta($sesion->hora_fin),
                'recurso' => $sesion->expositor,
                'items' => [[
                    'origen_label' => 'Educación Continua',
                    'titulo' => $sesion->curso?->nombre ?? 'Curso sin nombre',
                    'subtitulo' => $sesion->curso?->tipo ?? 'Sesión',
                    'grupo_curso' => $sesion->curso?->nombre ?? 'Curso',
                    'docente' => $sesion->expositor,
                    'lugar' => $sesion->aula_liga ?: 'Aula/liga pendiente',
                    'modalidad' => $sesion->modalidad,
                    'estatus' => $sesion->estatus,
                    'url' => $sesion->curso ? route('educacion_continua.show', $sesion->curso) : null,
                ]],
                'acciones' => ['Validar si aplica pago docente', 'Crear solicitud si corresponde', 'Documentar excepción si no aplica'],
            ]));
        }

        return $alertas->values();
    }

    private function alertaSesion(string $tipo, string $severidad, string $detalle, array $sesion, array $acciones): array
    {
        return [
            'tipo' => $tipo,
            'severidad' => $severidad,
            'titulo' => $tipo,
            'detalle' => $detalle,
            'fecha' => $sesion['fecha'],
            'hora' => $sesion['hora_inicio'].' - '.$sesion['hora_fin'],
            'recurso' => $sesion['titulo'],
            'items' => [$sesion],
            'acciones' => $acciones,
        ];
    }

    private function normalizarRecurso(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        $normalizado = Str::of($valor)
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->replace(['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'u', 'n'])
            ->trim()
            ->toString();

        if ($normalizado === '' || str_contains($normalizado, 'pendiente') || str_contains($normalizado, 'no asignado') || str_contains($normalizado, 'no disponible')) {
            return null;
        }

        return $normalizado;
    }

    private function horariosSeTraslapan(array $a, array $b): bool
    {
        return $a['inicio_min'] < $b['fin_min'] && $a['fin_min'] > $b['inicio_min'];
    }

    private function horaCorta($hora): string
    {
        return $hora ? substr((string) $hora, 0, 5) : '--:--';
    }

    private function minutos($hora): ?int
    {
        if (! $hora) {
            return null;
        }

        $partes = explode(':', (string) $hora);

        if (count($partes) < 2) {
            return null;
        }

        return ((int) $partes[0] * 60) + (int) $partes[1];
    }

    private function pesoSeveridad(string $severidad): int
    {
        return match ($severidad) {
            'alta' => 3,
            'media' => 2,
            default => 1,
        };
    }
}
