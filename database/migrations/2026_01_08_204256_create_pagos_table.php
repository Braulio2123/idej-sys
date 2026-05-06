<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alumno_id')
                ->constrained('alumnos')
                ->cascadeOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->enum('metodo_pago', ['Efectivo', 'Transferencia', 'Tarjeta']);
            $table->decimal('monto_total_pagado', 10, 2);
            $table->date('fecha_pago');

            // Datos generales del recibo/comprobante
            $table->string('folio_recibo')->nullable()->index();
            $table->string('referencia_bancaria')->nullable()->index();
            $table->string('archivo_comprobante')->nullable();

            // Datos específicos para transferencia o tarjeta
            $table->string('banco_emisor')->nullable();
            $table->string('cuenta_origen')->nullable();
            $table->string('numero_autorizacion')->nullable();
            $table->string('clave_rastreo')->nullable()->index();
            $table->string('concepto_transferencia')->nullable();
            $table->dateTime('fecha_transferencia')->nullable();
            $table->string('banco_destino')->nullable();

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
