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

            $table->foreignId('docente_id')
                ->constrained('docentes')
                ->cascadeOnDelete();

            $table->foreignId('creado_por_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('procesado_por_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->string('nivel')->nullable();

            $table->decimal('monto', 10, 2);

            $table->date('fecha_solicitud');
            $table->date('fecha_pago')->nullable();

            $table->text('observaciones')->nullable();

            $table->string('estatus')->default('Pendiente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_pago_docentes');
    }
};
