<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            $table->string('folio')->nullable()->unique()->after('id');

            $table->foreignId('autorizado_por_id')
                ->nullable()
                ->after('procesado_por_id')
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->foreignId('cancelado_por_id')
                ->nullable()
                ->after('autorizado_por_id')
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->foreignId('calendario_materia_id')
                ->nullable()
                ->after('cancelado_por_id')
                ->constrained('calendario_materias')
                ->nullOnDelete();

            $table->foreignId('curso_id')
                ->nullable()
                ->after('calendario_materia_id')
                ->constrained('cursos_educacion_continua')
                ->nullOnDelete();

            $table->foreignId('curso_sesion_id')
                ->nullable()
                ->after('curso_id')
                ->constrained('curso_sesiones')
                ->nullOnDelete();

            $table->string('origen', 80)->default('Manual')->after('curso_sesion_id');
            $table->string('concepto_pago', 120)->nullable()->after('origen');
            $table->string('programa_grupo', 180)->nullable()->after('nivel');
            $table->string('materia_actividad', 220)->nullable()->after('programa_grupo');
            $table->string('periodo', 120)->nullable()->after('materia_actividad');
            $table->string('modalidad', 60)->nullable()->after('periodo');
            $table->unsignedInteger('numero_sesiones')->nullable()->after('modalidad');
            $table->decimal('horas_totales', 8, 2)->nullable()->after('numero_sesiones');
            $table->decimal('tarifa_hora', 10, 2)->nullable()->after('horas_totales');
            $table->date('fecha_inicio_periodo')->nullable()->after('fecha_solicitud');
            $table->date('fecha_fin_periodo')->nullable()->after('fecha_inicio_periodo');
            $table->date('fecha_limite_pago')->nullable()->after('fecha_fin_periodo');
            $table->dateTime('fecha_autorizacion')->nullable()->after('fecha_limite_pago');
            $table->dateTime('fecha_cancelacion')->nullable()->after('fecha_pago');
            $table->string('prioridad', 30)->default('Normal')->after('fecha_cancelacion');
            $table->string('metodo_pago', 50)->nullable()->after('prioridad');
            $table->string('referencia_pago')->nullable()->after('metodo_pago');
            $table->string('banco_pago')->nullable()->after('referencia_pago');
            $table->string('comprobante_pago_path')->nullable()->after('banco_pago');
            $table->string('comprobante_pago_original')->nullable()->after('comprobante_pago_path');
            $table->text('observaciones_academica')->nullable()->after('comprobante_pago_original');
            $table->text('observaciones_administracion')->nullable()->after('observaciones_academica');
            $table->text('motivo_observacion')->nullable()->after('observaciones_administracion');
            $table->text('motivo_cancelacion')->nullable()->after('motivo_observacion');

            $table->index(['estatus', 'fecha_solicitud']);
            $table->index(['docente_id', 'estatus']);
            $table->index(['fecha_limite_pago']);
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            $table->dropIndex(['estatus', 'fecha_solicitud']);
            $table->dropIndex(['docente_id', 'estatus']);
            $table->dropIndex(['fecha_limite_pago']);

            $table->dropForeign(['autorizado_por_id']);
            $table->dropForeign(['cancelado_por_id']);
            $table->dropForeign(['calendario_materia_id']);
            $table->dropForeign(['curso_id']);
            $table->dropForeign(['curso_sesion_id']);

            $table->dropColumn([
                'folio',
                'autorizado_por_id',
                'cancelado_por_id',
                'calendario_materia_id',
                'curso_id',
                'curso_sesion_id',
                'origen',
                'concepto_pago',
                'programa_grupo',
                'materia_actividad',
                'periodo',
                'modalidad',
                'numero_sesiones',
                'horas_totales',
                'tarifa_hora',
                'fecha_inicio_periodo',
                'fecha_fin_periodo',
                'fecha_limite_pago',
                'fecha_autorizacion',
                'fecha_cancelacion',
                'prioridad',
                'metodo_pago',
                'referencia_pago',
                'banco_pago',
                'comprobante_pago_path',
                'comprobante_pago_original',
                'observaciones_academica',
                'observaciones_administracion',
                'motivo_observacion',
                'motivo_cancelacion',
            ]);
        });
    }
};
