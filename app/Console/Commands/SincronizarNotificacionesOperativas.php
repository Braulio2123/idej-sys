<?php

namespace App\Console\Commands;

use App\Models\CalendarioSesion;
use App\Models\CorteCaja;
use App\Models\CursoSesion;
use App\Models\NotificacionInterna;
use App\Models\Rol;
use App\Models\SolicitudPagoDocente;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SincronizarNotificacionesOperativas extends Command
{
    protected $signature = 'idej:notificaciones-operativas';

    protected $description = 'Genera y actualiza notificaciones internas derivadas de caja, solicitudes docentes y agenda operativa.';

    private int $creadasOActualizadas = 0;

    public function handle(): int
    {
        $this->info('Sincronizando notificaciones operativas de IDEJ-SYS...');

        $this->notificarCajasAbiertasAntiguas();
        $this->notificarSolicitudesDocentes();
        $this->notificarSesionesIncompletas();

        $this->info("Notificaciones sincronizadas: {$this->creadasOActualizadas}");

        return self::SUCCESS;
    }

    private function notificarCajasAbiertasAntiguas(): void
    {
        $cortes = CorteCaja::query()
            ->with('usuario')
            ->where('estatus', CorteCaja::ESTATUS_ABIERTA)
            ->whereDate('fecha_apertura', '<', today())
            ->get();

        foreach ($cortes as $corte) {
            $fecha = optional($corte->fecha_apertura)->format('d/m/Y H:i') ?? 'fecha no registrada';
            $usuario = $corte->usuario?->nombre ?? 'Usuario no identificado';
            $url = route('cortes-caja.show', $corte);

            if ($corte->usuario_id) {
                $this->sincronizar([
                    'usuario_id' => $corte->usuario_id,
                    'tipo' => 'caja_abierta_antigua',
                    'modulo' => 'Caja',
                    'titulo' => 'Tienes una caja abierta de un día anterior',
                    'mensaje' => "La caja #{$corte->id} fue abierta el {$fecha}. Debe cerrarse o revisarse antes de seguir operando.",
                    'url' => $url,
                    'severidad' => NotificacionInterna::SEVERIDAD_ALTA,
                    'referencia_tipo' => CorteCaja::class,
                    'referencia_id' => $corte->id,
                    'metadata' => ['fecha_apertura' => (string) $corte->fecha_apertura],
                ]);
            }

            $this->notificarRoles([Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], [
                'tipo' => 'caja_abierta_antigua_supervision',
                'modulo' => 'Caja',
                'titulo' => 'Caja abierta pendiente de supervisión',
                'mensaje' => "{$usuario} tiene abierta la caja #{$corte->id} desde {$fecha}.",
                'url' => $url,
                'severidad' => NotificacionInterna::SEVERIDAD_ALTA,
                'referencia_tipo' => CorteCaja::class,
                'referencia_id' => $corte->id,
                'metadata' => ['usuario_id' => $corte->usuario_id],
            ]);
        }
    }

    private function notificarSolicitudesDocentes(): void
    {
        $pendientes = SolicitudPagoDocente::query()
            ->where('estatus', SolicitudPagoDocente::ESTATUS_PENDIENTE)
            ->whereDate('fecha_solicitud', '<=', today()->subDays(2))
            ->get();

        foreach ($pendientes as $solicitud) {
            $this->notificarRoles([Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], [
                'tipo' => 'solicitud_docente_pendiente_revision',
                'modulo' => 'Solicitudes docentes',
                'titulo' => 'Solicitud docente pendiente de revisión',
                'mensaje' => $this->resumenSolicitud($solicitud).' sigue pendiente desde '.($solicitud->fecha_solicitud?->format('d/m/Y') ?? 'fecha no registrada').'.',
                'url' => route('solicitudes_pago.show', $solicitud),
                'severidad' => NotificacionInterna::SEVERIDAD_MEDIA,
                'referencia_tipo' => SolicitudPagoDocente::class,
                'referencia_id' => $solicitud->id,
                'metadata' => ['estatus' => $solicitud->estatus],
            ]);
        }

        $observadas = SolicitudPagoDocente::query()
            ->where('estatus', SolicitudPagoDocente::ESTATUS_OBSERVADA)
            ->get();

        foreach ($observadas as $solicitud) {
            $datos = [
                'tipo' => 'solicitud_docente_observada',
                'modulo' => 'Solicitudes docentes',
                'titulo' => 'Solicitud docente observada',
                'mensaje' => $this->resumenSolicitud($solicitud).' requiere corrección o revisión por Académica.',
                'url' => route('solicitudes_pago.show', $solicitud),
                'severidad' => NotificacionInterna::SEVERIDAD_MEDIA,
                'referencia_tipo' => SolicitudPagoDocente::class,
                'referencia_id' => $solicitud->id,
                'metadata' => ['motivo_observacion' => $solicitud->motivo_observacion],
            ];

            if ($solicitud->creado_por_id) {
                $this->sincronizar(array_merge($datos, ['usuario_id' => $solicitud->creado_por_id]));
            }

            $this->notificarRoles([Rol::ADMIN, Rol::ACADEMICA], $datos);
        }

        $autorizadasVencidas = SolicitudPagoDocente::query()
            ->where('estatus', SolicitudPagoDocente::ESTATUS_AUTORIZADA)
            ->whereNotNull('fecha_limite_pago')
            ->whereDate('fecha_limite_pago', '<', today())
            ->get();

        foreach ($autorizadasVencidas as $solicitud) {
            $this->notificarRoles([Rol::ADMIN, Rol::CADMIN, Rol::FINANZAS], [
                'tipo' => 'solicitud_docente_autorizada_vencida',
                'modulo' => 'Solicitudes docentes',
                'titulo' => 'Solicitud docente autorizada vencida sin pago',
                'mensaje' => $this->resumenSolicitud($solicitud).' tenía fecha límite '.($solicitud->fecha_limite_pago?->format('d/m/Y') ?? 'fecha no registrada').'.',
                'url' => route('solicitudes_pago.show', $solicitud),
                'severidad' => NotificacionInterna::SEVERIDAD_ALTA,
                'referencia_tipo' => SolicitudPagoDocente::class,
                'referencia_id' => $solicitud->id,
                'metadata' => ['fecha_limite_pago' => (string) $solicitud->fecha_limite_pago],
            ]);
        }
    }

    private function notificarSesionesIncompletas(): void
    {
        $inicio = today();
        $fin = today()->addDays(7);

        $principalesSinAula = CalendarioSesion::query()
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->whereNotIn('estatus', [CalendarioSesion::ESTATUS_CANCELADA, CalendarioSesion::ESTATUS_SUSPENDIDA])
            ->where(function ($query) {
                $query->whereNull('aula')
                    ->orWhere('aula', '')
                    ->orWhereNull('hora_inicio')
                    ->orWhereNull('hora_fin');
            })
            ->count();

        $educacionContinuaSinLugar = CursoSesion::query()
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->whereNotIn('estatus', [CursoSesion::ESTATUS_CANCELADA])
            ->where(function ($query) {
                $query->whereNull('aula_liga')
                    ->orWhere('aula_liga', '')
                    ->orWhereNull('hora_inicio')
                    ->orWhereNull('hora_fin');
            })
            ->count();

        $total = $principalesSinAula + $educacionContinuaSinLugar;

        if ($total <= 0) {
            return;
        }

        $this->notificarRoles([Rol::ADMIN, Rol::SISTEMAS, Rol::ACADEMICA, Rol::RECEPCION, Rol::DIRECCION], [
            'tipo' => 'sesiones_incompletas_semana',
            'modulo' => 'Agenda operativa',
            'titulo' => 'Sesiones incompletas en los próximos 7 días',
            'mensaje' => "Hay {$total} sesión(es) próximas con aula/liga u horario pendiente. Revísalas antes de la operación diaria.",
            'url' => route('centro-control.index', ['rango' => 'semana']),
            'severidad' => $total >= 3 ? NotificacionInterna::SEVERIDAD_ALTA : NotificacionInterna::SEVERIDAD_MEDIA,
            'referencia_tipo' => 'agenda_operativa',
            'referencia_id' => 0,
            'metadata' => [
                'calendario_principal' => $principalesSinAula,
                'educacion_continua' => $educacionContinuaSinLugar,
                'fecha_inicio' => $inicio->toDateString(),
                'fecha_fin' => $fin->toDateString(),
            ],
        ]);
    }

    private function notificarRoles(array $roles, array $datos): void
    {
        foreach ($roles as $rol) {
            $this->sincronizar(array_merge($datos, ['rol_clave' => $rol]));
        }
    }

    private function sincronizar(array $datos): void
    {
        NotificacionInterna::sincronizar($datos);
        $this->creadasOActualizadas++;
    }

    private function resumenSolicitud(SolicitudPagoDocente $solicitud): string
    {
        $folio = $solicitud->folio ?: 'Sin folio';
        $actividad = $solicitud->materia_actividad ?: ($solicitud->concepto_pago ?: 'servicio docente');

        return "{$folio} · {$actividad}";
    }
}
