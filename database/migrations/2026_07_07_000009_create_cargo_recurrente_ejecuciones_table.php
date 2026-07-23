<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo_recurrente_ejecuciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_cargo_recurrente_id')->constrained('planes_cargos_recurrentes')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->char('periodo', 7);
            $table->date('fecha_vencimiento');
            $table->decimal('monto_original', 10, 2)->default(0);
            $table->decimal('monto_adeudo', 10, 2)->default(0);
            $table->enum('estatus', ['generado', 'omitido', 'error'])->default('generado');
            $table->string('motivo', 255)->nullable();
            $table->timestamp('ejecutado_at')->nullable();
            $table->timestamps();

            $table->unique(['plan_cargo_recurrente_id', 'alumno_id', 'periodo'], 'cargo_recurrente_unico_por_periodo');
            $table->index(['periodo', 'estatus'], 'cargo_recurrente_periodo_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_recurrente_ejecuciones');
    }
};
