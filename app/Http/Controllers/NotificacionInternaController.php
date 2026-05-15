<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\NotificacionInterna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
