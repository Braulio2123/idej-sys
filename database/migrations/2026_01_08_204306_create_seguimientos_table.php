<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alumno_id')
                ->nullable()
                ->constrained('alumnos')
                ->nullOnDelete();

            // Campo reservado para el futuro módulo de prospectos.
            $table->unsignedBigInteger('prospecto_id')->nullable()->index();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->string('area', 80)->nullable();
            $table->string('tipo', 40);
            $table->string('prioridad', 20)->default('Normal');
            $table->string('estatus', 30)->default('Abierto');
            $table->string('asunto', 160);
            $table->text('descripcion')->nullable();
            $table->text('resultado')->nullable();
            $table->dateTime('fecha_contacto')->nullable();
            $table->dateTime('fecha_proximo_contacto')->nullable();
            $table->dateTime('fecha_cierre')->nullable();

            $table->timestamps();

            $table->index(['alumno_id', 'estatus']);
            $table->index(['fecha_proximo_contacto', 'estatus']);
            $table->index(['tipo', 'prioridad']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};
