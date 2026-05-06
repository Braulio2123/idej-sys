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

            // Mantengo nombre_completo por compatibilidad con tu sistema actual,
            // y además agrego apellidos opcionales como ya venían en updates.
            $table->string('nombre_completo');
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();

            $table->string('correo')->nullable();
            $table->string('telefono')->nullable();

            // Relaciones académicas
            $table->foreignId('grupo_id')
                ->nullable()
                ->constrained('grupos')
                ->nullOnDelete();

            $table->foreignId('ciclo_escolar_id')
                ->nullable()
                ->constrained('ciclos_escolares')
                ->nullOnDelete();

            // Estatus
            $table->enum('estatus_financiero', ['Al Corriente', 'Con Adeudo', 'En Convenio', 'Becado'])
                ->default('Al Corriente');

            $table->enum('estatus_academico', ['Activo', 'Baja Temporal', 'Suspendido'])
                ->default('Activo');

            $table->enum('condicion_alumno', ['Normal', 'Becado', 'En Convenio'])
                ->default('Normal');

            // Becas / saldos
            $table->unsignedTinyInteger('beca_porcentaje')->default(0);
            $table->decimal('saldo_a_favor', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
