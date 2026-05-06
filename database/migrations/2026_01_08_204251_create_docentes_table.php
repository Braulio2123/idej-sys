<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();

            $table->string('nombre_completo');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('domicilio')->nullable();

            $table->string('area_especialidad');

            $table->foreignId('creado_por_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->string('rfc')->nullable();
            $table->string('numero_cuenta')->nullable();

            $table->enum('estatus', ['Pendiente de Datos', 'Activo'])
                ->default('Pendiente de Datos');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
