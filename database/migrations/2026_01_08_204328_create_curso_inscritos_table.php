<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_inscritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos_educacion_continua')->cascadeOnDelete();
            $table->foreignId('alumno_id')->nullable()->constrained('alumnos')->nullOnDelete();
            $table->foreignId('prospecto_id')->nullable()->constrained('prospectos')->nullOnDelete();
            $table->string('tipo_participante', 40)->default('Externo');
            $table->string('nombre_externo')->nullable();
            $table->string('correo_externo')->nullable();
            $table->string('telefono_externo')->nullable();
            $table->string('estatus', 40)->default('Inscrito');
            $table->date('fecha_inscripcion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['curso_id', 'estatus']);
            $table->index(['alumno_id', 'prospecto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_inscritos');
    }
};
