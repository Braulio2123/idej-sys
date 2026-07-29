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
                ->restrictOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->foreignId('corte_caja_id')
                ->nullable()
                ->constrained('cortes_caja')
                ->nullOnDelete();

            $table->foreignId('cancelado_por_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->enum('metodo_pago', ['Efectivo', 'Transferencia', 'Tarjeta']);
            $table->decimal('monto_total_pagado', 10, 2);
            $table->decimal('monto_recibido_efectivo', 10, 2)->nullable();
            $table->decimal('cambio_entregado', 10, 2)->default(0);
            $table->string('tratamiento_excedente', 30)->nullable();
            $table->boolean('es_pago_anticipado')->default(false);
            $table->decimal('saldo_a_favor_generado', 10, 2)->default(0);
            $table->enum('estatus', ['Activo', 'Cancelado'])->default('Activo')->index();
            $table->date('fecha_pago');
            $table->timestamp('fecha_cancelacion')->nullable();

            $table->string('folio_recibo')->nullable()->index();
            $table->uuid('recibo_uuid')->nullable()->unique();
            $table->timestamp('recibo_emitido_at')->nullable();
            $table->unsignedTinyInteger('recibo_version')->default(1);
            $table->uuid('operacion_uuid')->nullable()->unique();

            $table->string('referencia_bancaria')->nullable()->index();
            $table->string('archivo_comprobante')->nullable();
            $table->string('archivo_comprobante_original')->nullable();
            $table->string('archivo_comprobante_mime', 120)->nullable();
            $table->unsignedBigInteger('archivo_comprobante_tamano')->nullable();
            $table->char('archivo_comprobante_sha256', 64)->nullable();
            $table->string('banco_emisor')->nullable();
            $table->string('cuenta_origen')->nullable();
            $table->string('numero_autorizacion')->nullable();
            $table->string('clave_rastreo')->nullable()->index();
            $table->string('concepto_transferencia')->nullable();
            $table->dateTime('fecha_transferencia')->nullable();
            $table->string('banco_destino')->nullable();

            $table->text('observaciones')->nullable();
            $table->text('motivo_cancelacion')->nullable();

            $table->timestamps();

            $table->index(['fecha_pago', 'metodo_pago'], 'idx_pagos_fecha_metodo');
            $table->index(['alumno_id', 'fecha_pago'], 'idx_pagos_alumno_fecha');
            $table->index(['usuario_id', 'fecha_pago'], 'idx_pagos_usuario_fecha');
            $table->index(['corte_caja_id', 'estatus'], 'idx_pagos_corte_estatus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
