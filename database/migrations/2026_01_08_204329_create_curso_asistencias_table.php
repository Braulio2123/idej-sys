<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_sesion_id')->constrained('curso_sesiones')->cascadeOnDelete();
            $table->foreignId('curso_inscrito_id')->constrained('curso_inscritos')->cascadeOnDelete();
            $table->string('estatus', 40)->default('Asistió');
            $table->decimal('horas_acreditadas', 5, 2)->default(0);
            $table->foreignId('registrado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['curso_sesion_id', 'curso_inscrito_id'], 'curso_asistencia_unica');
            $table->index(['estatus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_asistencias');
    }
};
