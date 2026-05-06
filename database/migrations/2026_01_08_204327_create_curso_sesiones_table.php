<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos_educacion_continua')->cascadeOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->string('expositor_nombre')->nullable();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->decimal('duracion_horas', 5, 2)->default(0);
            $table->string('aula_liga')->nullable();
            $table->string('modalidad', 40)->default('Presencial');
            $table->string('estatus', 40)->default('Programada');
            $table->boolean('requiere_equipo')->default(false);
            $table->json('equipo_requerido')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['fecha', 'hora_inicio']);
            $table->index(['curso_id', 'estatus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_sesiones');
    }
};
