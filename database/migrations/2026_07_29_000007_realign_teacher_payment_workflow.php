<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            $table->string('tipo_clase', 40)->nullable()->after('origen');
            $table->json('fechas_clase')->nullable()->after('curso_sesion_ids');
            $table->string('esquema_pago', 30)->nullable()->after('horas_totales');
            $table->decimal('tarifa_unitaria', 10, 2)->nullable()->after('esquema_pago');

            $table->foreignId('valorado_por_id')->nullable()->after('creado_por_id')
                ->constrained('usuarios')->nullOnDelete();
            $table->foreignId('rechazado_por_id')->nullable()->after('cancelado_por_id')
                ->constrained('usuarios')->nullOnDelete();

            $table->dateTime('fecha_valoracion')->nullable()->after('fecha_autorizacion');
            $table->dateTime('fecha_rechazo')->nullable()->after('fecha_cancelacion');
            $table->text('motivo_rechazo')->nullable()->after('motivo_cancelacion');

            $table->index(['tipo_clase', 'estatus'], 'idx_spd_tipo_estado');
            $table->index(['fecha_tentativa_pago', 'estatus'], 'idx_spd_tentativa_estado');
        });

        DB::table('solicitudes_pago_docentes')
            ->whereNull('tipo_clase')
            ->update([
                'tipo_clase' => DB::raw("CASE
                    WHEN nivel LIKE '%Doctorado%' THEN 'Doctorado'
                    WHEN nivel LIKE '%Maestr%' THEN 'Maestría'
                    WHEN nivel LIKE '%Licenciatura%' THEN 'Licenciatura'
                    WHEN programa_grupo LIKE '%Diplomado%' OR materia_actividad LIKE '%Diplomado%' THEN 'Diplomado'
                    WHEN origen = 'Educación continua' THEN 'Curso'
                    ELSE 'Curso'
                END"),
            ]);

        DB::table('solicitudes_pago_docentes')
            ->whereNull('fechas_clase')
            ->whereNotNull('fecha_inicio_periodo')
            ->update([
                'fechas_clase' => DB::raw("JSON_ARRAY(DATE_FORMAT(fecha_inicio_periodo, '%Y-%m-%d'))"),
            ]);

        DB::table('solicitudes_pago_docentes')
            ->whereIn('estatus', ['Autorizada', 'Pagada'])
            ->whereNull('fecha_valoracion')
            ->update([
                'fecha_valoracion' => DB::raw('COALESCE(fecha_autorizacion, updated_at)'),
                'valorado_por_id' => DB::raw('autorizado_por_id'),
                'esquema_pago' => 'Monto fijo',
                'tarifa_unitaria' => DB::raw('monto'),
            ]);
    }

    public function down(): void
    {
        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            $table->dropIndex('idx_spd_tipo_estado');
            $table->dropIndex('idx_spd_tentativa_estado');
            $table->dropConstrainedForeignId('valorado_por_id');
            $table->dropConstrainedForeignId('rechazado_por_id');
            $table->dropColumn([
                'tipo_clase',
                'fechas_clase',
                'esquema_pago',
                'tarifa_unitaria',
                'fecha_valoracion',
                'fecha_rechazo',
                'motivo_rechazo',
            ]);
        });
    }
};
