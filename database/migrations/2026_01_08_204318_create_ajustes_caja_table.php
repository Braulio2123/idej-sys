<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes_caja', function (Blueprint $table) {
            $table->id();

            $table->foreignId('corte_caja_id')
                ->constrained('cortes_caja')
                ->cascadeOnDelete();

            $table->foreignId('pago_id')
                ->nullable()
                ->constrained('pagos')
                ->nullOnDelete();

            $table->foreignId('alumno_id')
                ->nullable()
                ->constrained('alumnos')
                ->nullOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->string('tipo', 100);
            $table->enum('metodo_pago', ['Efectivo', 'Transferencia', 'Tarjeta'])->nullable();
            $table->decimal('monto_ajuste', 10, 2);
            $table->string('estatus', 40)->default('Aplicado');
            $table->text('motivo');
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_aplicacion');

            $table->timestamps();

            $table->index(['corte_caja_id', 'estatus']);
            $table->index(['pago_id', 'tipo']);
            $table->index('fecha_aplicacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes_caja');
    }
};
