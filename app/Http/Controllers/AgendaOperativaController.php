<?php

namespace App\Http\Controllers;

use App\Models\CalendarioSesion;
use App\Models\CursoSesion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AgendaOperativaController extends Controller
{
    public function index(Request $request)
    {
        [$fechaInicio, $fechaFin, $rangoSeleccionado] = $this->resolverRango($request);

        $tipo = $request->input('tipo', 'todos');
        $modalidad = $request->input('modalidad', 'todas');
        $soloEquipo = $request->boolean('solo_equipo');
        $busqueda = trim((string) $request->input('q', ''));

        $eventosPrincipales = in_array($tipo, ['todos', 'principal'], true)
            ? $this->eventosCalendariosPrincipales($fechaInicio, $fechaFin)
            : collect();

        $eventosEducacionContinua = in_array($tipo, ['todos', 'educacion_continua'], true)
            ? $this->eventosEducacionContinua($fechaInicio, $fechaFin)
            : collect();

        $eventos = $eventosPrincipales
            ->merge($eventosEducacionContinua)
            ->filter(function (array $evento) use ($modalidad, $soloEquipo, $busqueda) {
                if ($modalidad !== 'todas' && $evento['modalidad'] !== $modalidad) {
                    return false;
                }

                if ($soloEquipo && empty($evento['equipo'])) {
                    return false;
                }

                if ($busqueda !== '') {
                    $texto = mb_strtolower(implode(' ', [
                        $evento['titulo'],
                        $evento['subtitulo'],
                        $evento['docente'],
                        $evento['grupo_curso'],
                        $evento['lugar'],
                        $evento['modalidad'],
                        implode(' ', $evento['equipo']),
                    ]));

                    if (! str_contains($texto, mb_strtolower($busqueda))) {
                        return false;
                    }
                }

                return true;
            })
            ->sortBy(fn (array $evento) => $evento['fecha']->format('Y-m-d').' '.$evento['hora_inicio'])
            ->values();

        $eventosPorDia = $eventos->groupBy(fn (array $evento) => $evento['fecha']->toDateString());

        $resumen = [
            'total' => $eventos->count(),
            'principales' => $eventos->where('origen', 'principal')->count(),
            'educacion_continua' => $eventos->where('origen', 'educacion_continua')->count(),
            'requieren_equipo' => $eventos->filter(fn (array $evento) => ! empty($evento['equipo']))->count(),
            'virtuales_mixtas' => $eventos->filter(fn (array $evento) => in_array($evento['modalidad'], ['Virtual', 'Mixta'], true))->count(),
        ];

        $equipoResumen = $eventos
            ->flatMap(fn (array $evento) => $evento['equipo'])
            ->filter()
            ->countBy()
            ->sortDesc();

        $modalidades = ['todas', 'Presencial', 'Virtual', 'Mixta'];
        $tipos = ['todos', 'principal', 'educacion_continua'];
        $rangos = [
            'hoy' => 'Hoy',
            'manana' => 'Mañana',
            'semana' => 'Esta semana',
            '15_dias' => 'Próximos 15 días',
            'mes' => 'Próximo mes',
            'personalizado' => 'Personalizado',
        ];

        return view('agenda_operativa.index', compact(
            'eventos',
            'eventosPorDia',
            'resumen',
            'equipoResumen',
            'fechaInicio',
            'fechaFin',
            'rangoSeleccionado',
            'tipo',
            'modalidad',
            'soloEquipo',
            'busqueda',
            'modalidades',
            'tipos',
            'rangos'
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

    private function eventosCalendariosPrincipales(Carbon $inicio, Carbon $fin): Collection
    {
        return CalendarioSesion::activos()
            ->with([
                'calendarioMateria.calendario.grupo.programa',
                'calendarioMateria.calendario.cicloEscolar',
                'calendarioMateria.materia',
                'calendarioMateria.docente',
            ])
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->whereHas('calendarioMateria.calendario', fn ($q) => $q->operativos())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->map(function (CalendarioSesion $sesion) {
                $materiaCalendario = $sesion->calendarioMateria;
                $calendario = $materiaCalendario?->calendario;
                $grupo = $calendario?->grupo;
                $programa = $grupo?->programa;

                $modalidad = $sesion->modalidad ?: ($calendario?->modalidad ?? 'Sin modalidad');

                return [
                    'origen' => 'principal',
                    'tipo_label' => 'Clase principal',
                    'badge' => 'Académico',
                    'fecha' => $sesion->fecha,
                    'hora_inicio' => substr((string) $sesion->hora_inicio, 0, 5),
                    'hora_fin' => substr((string) $sesion->hora_fin, 0, 5),
                    'titulo' => $materiaCalendario?->nombre_materia ?? 'Materia sin nombre',
                    'subtitulo' => $calendario?->nombre ?? 'Calendario académico',
                    'grupo_curso' => trim(($programa?->nombre ? $programa->nombre.' · ' : '').($grupo?->nombre ?? 'Grupo no asignado')),
                    'docente' => $materiaCalendario?->nombre_docente ?? 'Docente no asignado',
                    'modalidad' => $modalidad,
                    'lugar' => $sesion->aula ?: 'Aula/liga pendiente',
                    'estatus' => $sesion->estatus,
                    'tipo_sesion' => $sesion->tipo_sesion ?: 'Clase',
                    'equipo' => $this->equipoSugeridoParaClasePrincipal($modalidad, $sesion->aula),
                    'observaciones' => $sesion->observaciones,
                    'url' => $calendario ? route('calendarios_academicos.show', $calendario) : null,
                ];
            });
    }

    private function eventosEducacionContinua(Carbon $inicio, Carbon $fin): Collection
    {
        return CursoSesion::activas()
            ->with(['curso', 'docente'])
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->whereHas('curso', fn ($q) => $q->operativos())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->map(function (CursoSesion $sesion) {
                $curso = $sesion->curso;
                $equipoSesion = $sesion->equipo_requerido ?: [];
                $equipoCurso = $curso?->equipo_requerido ?: [];
                $equipo = array_values(array_unique(array_filter(array_merge($equipoCurso, $equipoSesion))));

                return [
                    'origen' => 'educacion_continua',
                    'tipo_label' => 'Educación continua',
                    'badge' => $curso?->tipo ?? 'Curso',
                    'fecha' => $sesion->fecha,
                    'hora_inicio' => substr((string) $sesion->hora_inicio, 0, 5),
                    'hora_fin' => substr((string) $sesion->hora_fin, 0, 5),
                    'titulo' => $curso?->nombre ?? 'Curso sin nombre',
                    'subtitulo' => $curso?->tipo ?? 'Educación continua',
                    'grupo_curso' => $curso?->nombre ?? 'Curso',
                    'docente' => $sesion->expositor,
                    'modalidad' => $sesion->modalidad ?: ($curso?->modalidad ?? 'Sin modalidad'),
                    'lugar' => $sesion->aula_liga ?: 'Aula/liga pendiente',
                    'estatus' => $sesion->estatus,
                    'tipo_sesion' => 'Sesión',
                    'equipo' => $equipo,
                    'observaciones' => $sesion->observaciones,
                    'url' => $curso ? route('educacion_continua.show', $curso) : null,
                ];
            });
    }

    private function equipoSugeridoParaClasePrincipal(?string $modalidad, ?string $aula): array
    {
        $equipo = [];
        $modalidad = $modalidad ?: '';

        if (in_array($modalidad, ['Virtual', 'Mixta'], true)) {
            $equipo = ['Zoom', 'Cámara', 'Micrófono'];
        }

        if (str_contains(mb_strtolower((string) $aula), 'zoom') || str_contains(mb_strtolower((string) $aula), 'virtual')) {
            $equipo = array_merge($equipo, ['Zoom']);
        }

        return array_values(array_unique($equipo));
    }
}
