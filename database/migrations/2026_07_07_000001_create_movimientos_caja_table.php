<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corte_caja_id')
                ->constrained('cortes_caja')
                ->restrictOnDelete();
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();
            $table->foreignId('cancelado_por_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();
            $table->string('tipo', 20);
            $table->string('concepto', 120);
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago', 40)->default('Efectivo');
            $table->string('referencia', 200)->nullable();
            $table->string('comprobante_path')->nullable();
            $table->string('comprobante_original')->nullable();
            $table->text('observaciones')->nullable();
            $table->dateTime('fecha_movimiento');
            $table->string('estatus', 30)->default('Aplicado');
            $table->dateTime('fecha_cancelacion')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamps();

            $table->index(['corte_caja_id', 'estatus']);
            $table->index(['tipo', 'metodo_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
