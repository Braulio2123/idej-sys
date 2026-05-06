<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('becas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alumno_id')
                ->constrained('alumnos')
                ->cascadeOnDelete();

            $table->foreignId('autorizado_por_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->foreignId('registrado_por_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->foreignId('cancelado_por_id')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();

            $table->string('tipo', 80)->default('Institucional');
            $table->unsignedTinyInteger('porcentaje');
            $table->string('motivo', 255);
            $table->text('observaciones')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->enum('estatus', ['Programada', 'Activa', 'Vencida', 'Cancelada'])
                ->default('Activa');

            $table->timestamp('fecha_cancelacion')->nullable();
            $table->text('motivo_cancelacion')->nullable();

            $table->timestamps();

            $table->index(['alumno_id', 'estatus']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('becas');
    }
};
