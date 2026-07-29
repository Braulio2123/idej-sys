<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\NotificacionInterna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificacionInternaController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $estado = $request->input('estado', 'pendientes');
        $severidad = $request->input('severidad');

        $query = NotificacionInterna::query()
            ->visiblesPara($usuario)
            ->noArchivadas()
            ->latest();

        if ($estado === 'leidas') {
            $query->whereNotNull('leida_at');
        } elseif ($estado === 'todas') {
            // Sin filtro adicional.
        } else {
            $query->whereNull('leida_at');
            $estado = 'pendientes';
        }

        if (in_array($severidad, [
            NotificacionInterna::SEVERIDAD_BAJA,
            NotificacionInterna::SEVERIDAD_MEDIA,
            NotificacionInterna::SEVERIDAD_ALTA,
            NotificacionInterna::SEVERIDAD_CRITICA,
        ], true)) {
            $query->where('severidad', $severidad);
        } else {
            $severidad = null;
        }

        $notificaciones = $query->paginate(15)->withQueryString();

        $resumen = [
            'pendientes' => NotificacionInterna::query()->visiblesPara($usuario)->noArchivadas()->noLeidas()->count(),
            'criticas' => NotificacionInterna::query()->visiblesPara($usuario)->noArchivadas()->noLeidas()->where('severidad', NotificacionInterna::SEVERIDAD_CRITICA)->count(),
            'altas' => NotificacionInterna::query()->visiblesPara($usuario)->noArchivadas()->noLeidas()->where('severidad', NotificacionInterna::SEVERIDAD_ALTA)->count(),
            'todas' => NotificacionInterna::query()->visiblesPara($usuario)->noArchivadas()->count(),
        ];

        return view('notificaciones.index', compact('notificaciones', 'resumen', 'estado', 'severidad'));
    }


    public function resumen(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $pendientesQuery = NotificacionInterna::query()
            ->visiblesPara($usuario)
            ->noArchivadas()
            ->noLeidas();

        $recientes = NotificacionInterna::query()
            ->visiblesPara($usuario)
            ->noArchivadas()
            ->noLeidas()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (NotificacionInterna $notificacion) => [
                'titulo' => $notificacion->titulo,
                'mensaje' => $notificacion->mensaje,
                'severidad' => $notificacion->severidad,
                'url' => $notificacion->urlSegura(),
                'fecha' => $notificacion->created_at?->diffForHumans(),
                'id' => $notificacion->id,
                'created_at' => $notificacion->created_at?->toIso8601String(),
            ]);

        $response = response()->json([
            'resumen' => [
                'pendientes' => (clone $pendientesQuery)->count(),
                'criticas' => (clone $pendientesQuery)->where('severidad', NotificacionInterna::SEVERIDAD_CRITICA)->count(),
                'altas' => (clone $pendientesQuery)->where('severidad', NotificacionInterna::SEVERIDAD_ALTA)->count(),
                'todas' => NotificacionInterna::query()->visiblesPara($usuario)->noArchivadas()->count(),
            ],
            'recientes' => $recientes,
            'ultima_id' => (int) ($recientes->max('id') ?? 0),
            'server_time' => now()->toIso8601String(),
        ]);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        return $response;
    }


    public function probar(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $notificacion = NotificacionInterna::create([
            'usuario_id' => $usuario->id,
            'tipo' => 'prueba_tiempo_real',
            'modulo' => 'Notificaciones internas',
            'titulo' => 'Prueba de notificación en tiempo real',
            'mensaje' => 'Esta alerta se generó para comprobar que la campana se actualiza sin recargar la página.',
            'url' => route('notificaciones.index', [], false),
            'severidad' => NotificacionInterna::SEVERIDAD_MEDIA,
            'referencia_tipo' => 'prueba_manual',
            'referencia_id' => $usuario->id,
            'hash' => sha1('prueba-notificacion|'.$usuario->id.'|'.Str::uuid()),
            'metadata' => [
                'generada_por' => $usuario->email,
                'fecha' => now()->toDateTimeString(),
            ],
        ]);

        $this->registrarBitacora($request, 'Generar notificación de prueba', $notificacion);

        return back()->with('success', 'Notificación de prueba generada. La campana debe actualizarse sin recargar la página.');
    }

    public function sincronizarOperativas(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->tieneRol(\App\Models\Rol::ADMIN, \App\Models\Rol::SISTEMAS), 403, 'Solo Administración o Sistemas puede ejecutar esta prueba.');

        Artisan::call('idej:notificaciones-operativas');

        Bitacora::create([
            'usuario_id' => $request->user()?->id,
            'tipo' => 'Sistema',
            'accion' => 'Sincronizar notificaciones operativas manualmente',
            'modulo' => 'Notificaciones internas',
            'descripcion' => trim(Artisan::output()) ?: 'Sincronización ejecutada.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'metodo_http' => $request->method(),
            'fecha_evento' => now(),
        ]);

        return back()->with('success', 'Alertas operativas sincronizadas. Revisa la campana y el listado de notificaciones.');
    }

    public function marcarLeida(Request $request, NotificacionInterna $notificacion): RedirectResponse
    {
        $this->autorizar($request, $notificacion);
        $notificacion->marcarComoLeida();

        $this->registrarBitacora($request, 'Marcar notificación como leída', $notificacion);

        return back()->with('success', 'Notificación marcada como leída.');
    }

    public function marcarNoLeida(Request $request, NotificacionInterna $notificacion): RedirectResponse
    {
        $this->autorizar($request, $notificacion);
        $notificacion->marcarComoNoLeida();

        $this->registrarBitacora($request, 'Marcar notificación como no leída', $notificacion);

        return back()->with('success', 'Notificación marcada como no leída.');
    }

    public function marcarTodasLeidas(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $actualizadas = NotificacionInterna::query()
            ->visiblesPara($usuario)
            ->noArchivadas()
            ->noLeidas()
            ->update(['leida_at' => now(), 'updated_at' => now()]);

        Bitacora::create([
            'usuario_id' => $usuario->id,
            'tipo' => 'Visita',
            'accion' => 'Marcar todas las notificaciones como leídas',
            'modulo' => 'Notificaciones internas',
            'descripcion' => "Notificaciones actualizadas: {$actualizadas}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'metodo_http' => $request->method(),
            'fecha_evento' => now(),
        ]);

        return back()->with('success', 'Todas tus notificaciones pendientes fueron marcadas como leídas.');
    }

    public function archivar(Request $request, NotificacionInterna $notificacion): RedirectResponse
    {
        $this->autorizar($request, $notificacion);
        $notificacion->archivar();

        $this->registrarBitacora($request, 'Archivar notificación interna', $notificacion);

        return back()->with('success', 'Notificación archivada.');
    }

    private function autorizar(Request $request, NotificacionInterna $notificacion): void
    {
        abort_unless($notificacion->puedeVer($request->user()), 403, 'No tienes autorización para gestionar esta notificación.');
    }

    private function registrarBitacora(Request $request, string $accion, NotificacionInterna $notificacion): void
    {
        Bitacora::create([
            'usuario_id' => $request->user()?->id,
            'tipo' => 'Visita',
            'accion' => $accion,
            'modulo' => 'Notificaciones internas',
            'descripcion' => $notificacion->titulo,
            'modelo_type' => NotificacionInterna::class,
            'modelo_id' => $notificacion->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'metodo_http' => $request->method(),
            'fecha_evento' => now(),
        ]);
    }
}
