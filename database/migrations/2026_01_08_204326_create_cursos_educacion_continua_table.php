<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos_educacion_continua', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 80);
            $table->string('modalidad', 40)->default('Presencial');
            $table->decimal('horas_totales', 6, 2)->default(0);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estatus', 40)->default('Planeado');
            $table->foreignId('responsable_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('creado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->unsignedInteger('cupo_maximo')->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->boolean('requiere_equipo')->default(false);
            $table->json('equipo_requerido')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'estatus']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos_educacion_continua');
    }
};
