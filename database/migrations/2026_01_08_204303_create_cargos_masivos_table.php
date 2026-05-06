<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos_masivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('concepto_id')
                ->constrained('conceptos_pagos')
                ->cascadeOnDelete();

            $table->decimal('monto', 10, 2)->nullable();
            $table->date('fecha_vencimiento');
            $table->string('descripcion')->nullable();

            $table->foreignId('programa_id')
                ->nullable()
                ->constrained('programas')
                ->nullOnDelete();

            $table->foreignId('grupo_id')
                ->nullable()
                ->constrained('grupos')
                ->nullOnDelete();

            $table->foreignId('ciclo_escolar_id')
                ->nullable()
                ->constrained('ciclos_escolares')
                ->nullOnDelete();

            $table->integer('total_alumnos')->default(0);

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos_masivos');
    }
};
