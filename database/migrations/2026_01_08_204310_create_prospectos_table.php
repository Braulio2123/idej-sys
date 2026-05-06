<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospectos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre_completo');
            $table->string('correo')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();

            $table->foreignId('programa_id')
                ->nullable()
                ->constrained('programas')
                ->nullOnDelete();

            $table->string('nivel_interes', 80)->nullable();
            $table->string('medio_contacto', 80)->nullable();
            $table->string('origen', 120)->nullable();

            $table->foreignId('asesor_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->enum('estatus', [
                'Nuevo',
                'Contactado',
                'Interesado',
                'En seguimiento',
                'Inscrito',
                'Descartado',
            ])->default('Nuevo');

            $table->enum('prioridad', ['Baja', 'Normal', 'Alta', 'Urgente'])->default('Normal');

            $table->dateTime('fecha_contacto')->nullable();
            $table->dateTime('fecha_proximo_contacto')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_descarte')->nullable();

            $table->foreignId('alumno_id')
                ->nullable()
                ->constrained('alumnos')
                ->nullOnDelete();

            $table->dateTime('fecha_conversion')->nullable();

            $table->timestamps();

            $table->index(['estatus', 'prioridad']);
            $table->index(['fecha_proximo_contacto', 'estatus']);
            $table->index('medio_contacto');
            $table->index('origen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospectos');
    }
};
