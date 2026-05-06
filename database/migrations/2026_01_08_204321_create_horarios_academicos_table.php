<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_academicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('grupo_id')
                ->constrained('grupos')
                ->cascadeOnDelete();

            $table->foreignId('materia_id')
                ->constrained('materias')
                ->restrictOnDelete();

            $table->foreignId('docente_id')
                ->constrained('docentes')
                ->restrictOnDelete();

            $table->enum('dia_semana', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('aula', 80)->nullable();
            $table->enum('modalidad', ['Presencial', 'Virtual', 'Mixta'])->default('Presencial');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estatus', ['Activo', 'Suspendido', 'Finalizado'])->default('Activo');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['grupo_id', 'dia_semana', 'hora_inicio']);
            $table->index(['docente_id', 'dia_semana', 'hora_inicio']);
            $table->index(['estatus', 'dia_semana']);
            $table->index('aula');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_academicos');
    }
};
