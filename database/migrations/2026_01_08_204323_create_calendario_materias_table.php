<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendario_academico_id')->constrained('calendarios_academicos')->cascadeOnDelete();
            $table->foreignId('materia_id')->nullable()->constrained('materias')->nullOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->unsignedInteger('orden')->default(1);
            $table->string('nombre_materia_snapshot')->nullable();
            $table->string('docente_snapshot')->nullable();
            $table->enum('estatus', ['Programada', 'Confirmada', 'Impartida', 'Cancelada'])->default('Programada');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['calendario_academico_id', 'orden']);
            $table->index(['docente_id', 'estatus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_materias');
    }
};
