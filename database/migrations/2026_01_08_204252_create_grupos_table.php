<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');

            $table->foreignId('ciclo_escolar_id')
                ->constrained('ciclos_escolares')
                ->restrictOnDelete();

            $table->foreignId('programa_id')
                ->constrained('programas')
                ->restrictOnDelete();

            $table->foreignId('docente_id')
                ->nullable()
                ->constrained('docentes')
                ->nullOnDelete();

            $table->unsignedTinyInteger('semestre_o_cuatrimestre')->default(1);

            $table->enum('turno', ['Matutino', 'Vespertino', 'Sabatino', 'Mixto'])
                ->default('Matutino');

            $table->string('aula', 50)->nullable();

            $table->unsignedTinyInteger('cupo_maximo')->default(30);
            $table->boolean('activo')->default(true)->index();
            $table->timestamp('archivado_at')->nullable();
            $table->foreignId('archivado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->text('motivo_archivo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
