<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('matricula')->unique();
            $table->string('nombre_completo');
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('correo')->nullable();
            $table->string('telefono')->nullable();

            $table->foreignId('grupo_id')
                ->nullable()
                ->constrained('grupos')
                ->nullOnDelete();

            $table->foreignId('ciclo_escolar_id')
                ->nullable()
                ->constrained('ciclos_escolares')
                ->nullOnDelete();

            $table->enum('estatus_financiero', ['Al Corriente', 'Con Adeudo', 'En Convenio', 'Becado'])
                ->default('Al Corriente');

            $table->enum('estatus_academico', ['Activo', 'Baja Temporal', 'Suspendido'])
                ->default('Activo');

            $table->enum('condicion_alumno', ['Normal', 'Becado', 'En Convenio'])
                ->default('Normal');

            $table->unsignedTinyInteger('beca_porcentaje')->default(0);
            $table->decimal('saldo_a_favor', 10, 2)->default(0);

            $table->timestamps();

            $table->index(['estatus_financiero', 'estatus_academico'], 'idx_alumnos_estado_fin_acad');
            $table->index(['grupo_id', 'estatus_academico'], 'idx_alumnos_grupo_estado');
            $table->index(['ciclo_escolar_id', 'estatus_academico'], 'idx_alumnos_ciclo_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
