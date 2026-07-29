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

            $table->text('rfc')->nullable();
            $table->text('numero_cuenta')->nullable();
            $table->string('banco')->nullable();
            $table->string('curriculum_path')->nullable();
            $table->string('curriculum_original')->nullable();
            $table->char('curriculum_sha256', 64)->nullable();
            $table->string('titulo_cedula_path')->nullable();
            $table->string('titulo_cedula_original')->nullable();
            $table->char('titulo_cedula_sha256', 64)->nullable();
            $table->string('constancia_fiscal_path')->nullable();
            $table->string('constancia_fiscal_original')->nullable();
            $table->char('constancia_fiscal_sha256', 64)->nullable();

            $table->enum('estatus', ['Pendiente de Datos', 'Activo', 'Inactivo'])
                ->default('Pendiente de Datos');

            $table->timestamps();

            $table->index(['estatus', 'nombre_completo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
