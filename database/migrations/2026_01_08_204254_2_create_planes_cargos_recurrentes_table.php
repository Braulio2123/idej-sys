<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_cargos_recurrentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 160);
            $table->foreignId('concepto_id')->constrained('conceptos_pagos')->restrictOnDelete();
            $table->enum('alcance', ['todos', 'programa', 'grupo'])->default('grupo');
            $table->foreignId('programa_id')->nullable()->constrained('programas')->nullOnDelete();
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->decimal('monto', 10, 2)->nullable();
            $table->unsignedTinyInteger('dia_vencimiento')->default(10);
            $table->unsignedTinyInteger('frecuencia_meses')->default(1);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('enviar_recordatorio_email')->default(true);
            $table->foreignId('creado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('actualizado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();

            $table->index(['activo', 'fecha_inicio', 'fecha_fin'], 'planes_recurrentes_activo_fechas_idx');
            $table->index(['alcance', 'programa_id', 'grupo_id'], 'planes_recurrentes_alcance_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_cargos_recurrentes');
    }
};
