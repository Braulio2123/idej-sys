<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            // Campo heredado: se conserva para compatibilidad con instalaciones previas.
            $table->enum('tipo', ['Visita', 'Llamada', 'Recepcion de Documento'])->default('Visita');

            // Campos reales de auditoría.
            $table->string('accion', 120);
            $table->string('modulo', 80)->nullable();
            $table->text('descripcion')->nullable();

            $table->foreignId('alumno_id')
                ->nullable()
                ->constrained('alumnos')
                ->nullOnDelete();

            $table->string('modelo_type')->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('metodo_http', 10)->nullable();
            $table->dateTime('fecha_evento')->nullable();

            $table->timestamps();

            $table->index(['accion', 'modulo']);
            $table->index(['modelo_type', 'modelo_id']);
            $table->index('fecha_evento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
