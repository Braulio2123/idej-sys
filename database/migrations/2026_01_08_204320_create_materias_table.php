<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('programa_id')
                ->nullable()
                ->constrained('programas')
                ->nullOnDelete();

            $table->string('clave', 50)->nullable();
            $table->string('nombre');
            $table->string('nivel', 80)->nullable();
            $table->unsignedTinyInteger('semestre_o_cuatrimestre')->nullable();
            $table->unsignedTinyInteger('creditos')->nullable();
            $table->unsignedTinyInteger('horas_teoricas')->default(0);
            $table->unsignedTinyInteger('horas_practicas')->default(0);
            $table->enum('estatus', ['Activa', 'Inactiva'])->default('Activa');
            $table->text('descripcion')->nullable();

            $table->timestamps();

            $table->index(['programa_id', 'semestre_o_cuatrimestre']);
            $table->index('estatus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
