<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();

            $table->decimal('saldo_inicial', 10, 2)->default(0);

            $table->decimal('efectivo_sistema', 10, 2)->default(0);
            $table->decimal('transferencia_sistema', 10, 2)->default(0);
            $table->decimal('tarjeta_sistema', 10, 2)->default(0);
            $table->decimal('total_sistema', 10, 2)->default(0);
            $table->unsignedInteger('cantidad_pagos')->default(0);

            $table->decimal('efectivo_reportado', 10, 2)->nullable();
            $table->decimal('transferencia_reportado', 10, 2)->nullable();
            $table->decimal('tarjeta_reportado', 10, 2)->nullable();
            $table->decimal('total_reportado', 10, 2)->nullable();
            $table->decimal('diferencia_efectivo', 10, 2)->nullable();
            $table->decimal('diferencia_total', 10, 2)->nullable();

            $table->enum('estatus', ['Abierta', 'Cerrada'])->default('Abierta')->index();
            $table->text('observaciones_apertura')->nullable();
            $table->text('observaciones_cierre')->nullable();

            $table->timestamps();

            $table->index(['usuario_id', 'estatus']);
            $table->index('fecha_apertura');
            $table->index('fecha_cierre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_caja');
    }
};
