<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciclos_escolares', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();

            $table->enum('tipo_periodo', ['Cuatrimestral', 'Semestral', 'Anual', 'Otro'])
                ->default('Cuatrimestral');

            // Fechas “base” del ciclo (las dejo nullable por el historial de "fix_old_fields"
            // y porque a veces se capturan después).
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            // Ventanas académicas detalladas (opcionales)
            $table->date('fecha_inicio_inscripcion')->nullable();
            $table->date('fecha_fin_inscripcion')->nullable();
            $table->date('fecha_inicio_clases')->nullable();
            $table->date('fecha_fin_clases')->nullable();

            $table->boolean('activo')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciclos_escolares');
    }
};
