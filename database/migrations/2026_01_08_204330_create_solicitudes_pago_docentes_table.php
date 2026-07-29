<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_pago_docentes', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->nullable()->unique();

            $table->foreignId('docente_id')->constrained('docentes')->restrictOnDelete();
            $table->foreignId('creado_por_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('procesado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('autorizado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('cancelado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('calendario_materia_id')->nullable()->constrained('calendario_materias')->nullOnDelete();
            $table->json('calendario_sesion_ids')->nullable();
            $table->foreignId('curso_id')->nullable()->constrained('cursos_educacion_continua')->nullOnDelete();
            $table->foreignId('curso_sesion_id')->nullable()->constrained('curso_sesiones')->nullOnDelete();
            $table->json('curso_sesion_ids')->nullable();

            $table->string('origen', 80)->default('Manual');
            $table->string('concepto_pago', 120)->nullable();
            $table->string('nivel')->nullable();
            $table->string('programa_grupo', 180)->nullable();
            $table->string('materia_actividad', 220)->nullable();
            $table->string('periodo', 120)->nullable();
            $table->string('modalidad', 60)->nullable();
            $table->unsignedInteger('numero_sesiones')->nullable();
            $table->decimal('horas_totales', 8, 2)->nullable();
            $table->decimal('tarifa_hora', 10, 2)->nullable();
            $table->decimal('monto', 10, 2)->default(0);

            $table->date('fecha_solicitud');
            $table->date('fecha_inicio_periodo')->nullable();
            $table->date('fecha_fin_periodo')->nullable();
            $table->date('fecha_limite_pago')->nullable();
            $table->date('fecha_tentativa_pago')->nullable();
            $table->dateTime('fecha_autorizacion')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->dateTime('fecha_cancelacion')->nullable();

            $table->string('estatus')->default('Pendiente');
            $table->string('prioridad', 30)->default('Normal');
            $table->string('metodo_pago', 50)->nullable();
            $table->string('referencia_pago')->nullable();
            $table->string('banco_pago')->nullable();
            $table->string('comprobante_pago_path')->nullable();
            $table->string('comprobante_pago_original')->nullable();
            $table->string('comprobante_pago_mime', 120)->nullable();
            $table->unsignedBigInteger('comprobante_pago_tamano')->nullable();
            $table->char('comprobante_pago_sha256', 64)->nullable();
            $table->uuid('pago_operacion_uuid')->nullable()->unique();

            $table->text('observaciones')->nullable();
            $table->text('observaciones_academica')->nullable();
            $table->text('observaciones_administracion')->nullable();
            $table->text('motivo_observacion')->nullable();
            $table->text('motivo_cancelacion')->nullable();

            $table->timestamps();

            $table->index(['estatus', 'fecha_solicitud']);
            $table->index(['docente_id', 'estatus']);
            $table->index(['fecha_limite_pago']);
            $table->index(['estatus', 'fecha_limite_pago'], 'idx_solicitudes_estado_limite');
            $table->index(['creado_por_id', 'estatus'], 'idx_solicitudes_creador_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_pago_docentes');
    }
};
