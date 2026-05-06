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

            $table->foreignId('alumno_id')
                ->constrained('alumnos')
                ->cascadeOnDelete();

            $table->foreignId('concepto_id')
                ->constrained('conceptos_pagos')
                ->restrictOnDelete();

            $table->string('descripcion_cargo');
            $table->decimal('monto_original', 10, 2);
            $table->decimal('monto_adeudo', 10, 2);

            $table->date('fecha_vencimiento');

            $table->enum('estatus', ['Pendiente', 'Pagado', 'Parcialmente Pagado', 'En Convenio', 'Cancelado'])
                ->default('Pendiente');

            $table->boolean('moratorio_aplicado')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
