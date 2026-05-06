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
                ->cascadeOnDelete();

            $table->foreignId('programa_id')
                ->constrained('programas')
                ->cascadeOnDelete();

            $table->foreignId('docente_id')
                ->nullable()
                ->constrained('docentes')
                ->nullOnDelete();

            $table->unsignedTinyInteger('semestre_o_cuatrimestre')->default(1);

            $table->enum('turno', ['Matutino', 'Vespertino', 'Sabatino', 'Mixto'])
                ->default('Matutino');

            $table->string('aula', 50)->nullable();

            $table->unsignedTinyInteger('cupo_maximo')->default(30);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
