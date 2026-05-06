<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_alumnos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alumno_id')
                ->constrained('alumnos')
                ->cascadeOnDelete();

            $table->foreignId('usuario_subio_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->foreignId('usuario_reviso_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->string('tipo_documento', 120);
            $table->string('nombre_original')->nullable();
            $table->string('archivo_path')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();

            $table->string('estatus', 40)->default('Pendiente');
            $table->date('fecha_documento')->nullable();
            $table->dateTime('fecha_entrega')->nullable();
            $table->dateTime('fecha_revision')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_rechazo')->nullable();

            $table->timestamps();

            $table->index(['alumno_id', 'estatus']);
            $table->index(['tipo_documento', 'estatus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_alumnos');
    }
};
