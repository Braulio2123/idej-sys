<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alumno_id')
                ->constrained('alumnos')
                ->cascadeOnDelete();

            $table->foreignId('cargo_original_id')
                ->constrained('cargos')
                ->restrictOnDelete();

            $table->string('descripcion');
            $table->decimal('total_reestructurado', 10, 2);
            $table->unsignedInteger('numero_parcialidades');

            $table->enum('estatus', ['Activo', 'Finalizado'])->default('Activo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};
