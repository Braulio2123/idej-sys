<?php

namespace App\Traits;

use App\Models\Bitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait RegistraBitacora
{
    /**
     * Registrar evento de auditoría del sistema.
     */
    public function bitacora(
        string $accion,
        ?string $descripcion = null,
        ?string $modulo = null,
        ?Model $modelo = null,
        ?int $alumnoId = null
    ): void {
        try {
            $request = request();

            Bitacora::create([
                'usuario_id' => Auth::id(),

                // Campo heredado. Se conserva para compatibilidad con BD previa.
                'tipo' => 'Visita',

                'accion' => Str::limit($accion, 120, ''),
                'modulo' => $modulo ?? $this->inferirModulo($accion),
                'descripcion' => $descripcion,
                'alumno_id' => $alumnoId,
                'modelo_type' => $modelo ? get_class($modelo) : null,
                'modelo_id' => $modelo?->getKey(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'url' => $request?->fullUrl(),
                'metodo_http' => $request?->method(),
                'fecha_evento' => now(),
            ]);
        } catch (\Throwable $e) {
            logger()->error('Error al registrar bitácora: '.$e->getMessage(), [
                'accion' => $accion,
                'descripcion' => $descripcion,
            ]);
        }
    }

    private function inferirModulo(string $accion): string
    {
        $accionNormalizada = Str::lower($accion);

        return match (true) {
            Str::contains($accionNormalizada, 'prospecto') => 'Prospectos',
            Str::contains($accionNormalizada, 'alumno') => 'Alumnos',
            Str::contains($accionNormalizada, 'cargo') => 'Cargos',
            Str::contains($accionNormalizada, 'pago') => 'Pagos',
            Str::contains($accionNormalizada, 'convenio') => 'Convenios',
            Str::contains($accionNormalizada, 'seguimiento') => 'Seguimientos',
            Str::contains($accionNormalizada, 'documento') => 'Documentos',
            Str::contains($accionNormalizada, 'parcialidad') => 'Parcialidades',
            Str::contains($accionNormalizada, 'docente') => 'Docentes',
            Str::contains($accionNormalizada, 'educación continua') || Str::contains($accionNormalizada, 'educacion continua') || Str::contains($accionNormalizada, 'curso') => 'Educación Continua',
            Str::contains($accionNormalizada, 'horario') || Str::contains($accionNormalizada, 'asignación') || Str::contains($accionNormalizada, 'asignacion') => 'Horarios Académicos',
            Str::contains($accionNormalizada, 'materia') => 'Materias',
            Str::contains($accionNormalizada, 'grupo') => 'Grupos',
            Str::contains($accionNormalizada, 'programa') => 'Programas',
            Str::contains($accionNormalizada, 'ciclo') => 'Ciclos Escolares',
            Str::contains($accionNormalizada, 'concepto') => 'Conceptos de Pago',
            Str::contains($accionNormalizada, 'mantenimiento') || Str::contains($accionNormalizada, 'caché') || Str::contains($accionNormalizada, 'cache') || Str::contains($accionNormalizada, 'backup') || Str::contains($accionNormalizada, 'respaldo') => 'Mantenimiento',
            Str::contains($accionNormalizada, 'configuración') || Str::contains($accionNormalizada, 'configuracion') => 'Configuración',
            Str::contains($accionNormalizada, 'usuario') => 'Usuarios',
            default => 'Sistema',
        };
    }
}
