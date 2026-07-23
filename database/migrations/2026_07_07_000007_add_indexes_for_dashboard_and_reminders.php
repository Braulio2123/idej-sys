<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->index(['estatus_financiero', 'estatus_academico'], 'idx_alumnos_estado_fin_acad');
            $table->index(['grupo_id', 'estatus_academico'], 'idx_alumnos_grupo_estado');
        });

        Schema::table('cargos', function (Blueprint $table) {
            $table->index(['estatus', 'fecha_vencimiento'], 'idx_cargos_estado_vencimiento');
            $table->index(['alumno_id', 'estatus'], 'idx_cargos_alumno_estado');
            $table->index(['concepto_id', 'estatus'], 'idx_cargos_concepto_estado');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->index(['fecha_pago', 'metodo_pago'], 'idx_pagos_fecha_metodo');
            $table->index(['alumno_id', 'fecha_pago'], 'idx_pagos_alumno_fecha');
            $table->index(['usuario_id', 'fecha_pago'], 'idx_pagos_usuario_fecha');
        });

        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            $table->index(['estatus', 'fecha_limite_pago'], 'idx_solicitudes_estado_limite');
            $table->index(['creado_por_id', 'estatus'], 'idx_solicitudes_creador_estado');
        });

        Schema::table('notificaciones_internas', function (Blueprint $table) {
            $table->index(['created_at', 'archivada_at', 'leida_at'], 'idx_notificaciones_feed_estado');
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones_internas', function (Blueprint $table) {
            $table->dropIndex('idx_notificaciones_feed_estado');
        });

        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            $table->dropIndex('idx_solicitudes_estado_limite');
            $table->dropIndex('idx_solicitudes_creador_estado');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex('idx_pagos_fecha_metodo');
            $table->dropIndex('idx_pagos_alumno_fecha');
            $table->dropIndex('idx_pagos_usuario_fecha');
        });

        Schema::table('cargos', function (Blueprint $table) {
            $table->dropIndex('idx_cargos_estado_vencimiento');
            $table->dropIndex('idx_cargos_alumno_estado');
            $table->dropIndex('idx_cargos_concepto_estado');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropIndex('idx_alumnos_estado_fin_acad');
            $table->dropIndex('idx_alumnos_grupo_estado');
        });
    }
};
