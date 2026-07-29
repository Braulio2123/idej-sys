<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->uuid('operacion_uuid')->nullable()->unique();

            $table->foreignId('alumno_id')
                ->constrained('alumnos')
                ->restrictOnDelete();

            $table->foreignId('concepto_id')
                ->constrained('conceptos_pagos')
                ->restrictOnDelete();

            $table->foreignId('beca_id')
                ->nullable()
                ->constrained('becas')
                ->nullOnDelete();

            $table->foreignId('cargo_recurrente_plan_id')
                ->nullable()
                ->constrained('planes_cargos_recurrentes')
                ->nullOnDelete();

            $table->char('periodo_recurrente', 7)->nullable();
            $table->boolean('generado_automaticamente')->default(false);

            $table->string('descripcion_cargo');
            $table->decimal('monto_original', 10, 2);
            $table->unsignedTinyInteger('beca_porcentaje_aplicado')->default(0);
            $table->decimal('beca_monto_aplicado', 10, 2)->default(0);
            $table->decimal('saldo_favor_aplicado', 10, 2)->default(0);
            $table->decimal('monto_adeudo', 10, 2);
            $table->date('fecha_vencimiento');

            $table->enum('estatus', ['Pendiente', 'Pagado', 'Parcialmente Pagado', 'En Convenio', 'Cancelado'])
                ->default('Pendiente');

            $table->boolean('moratorio_aplicado')->default(false);

            $table->timestamps();

            $table->index(['estatus', 'fecha_vencimiento'], 'idx_cargos_estado_vencimiento');
            $table->index(['alumno_id', 'estatus'], 'idx_cargos_alumno_estado');
            $table->index(['concepto_id', 'estatus'], 'idx_cargos_concepto_estado');
            $table->index(['cargo_recurrente_plan_id', 'periodo_recurrente'], 'cargos_recurrente_plan_periodo_idx');
            $table->index(['generado_automaticamente', 'fecha_vencimiento'], 'cargos_auto_vencimiento_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
