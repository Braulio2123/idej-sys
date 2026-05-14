<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla propia para avisos de la PWA de alumnos.
     *
     * Se mantiene separada para no alterar modulos administrativos existentes.
     */
    public function up(): void
    {
        Schema::create('portal_alumno_avisos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('contenido');
            $table->string('categoria', 80)->default('General');
            $table->enum('prioridad', ['normal', 'importante', 'urgente'])->default('normal');
            $table->enum('destino_tipo', ['todos', 'grupo'])->default('todos');
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->timestamp('visible_desde')->nullable();
            $table->timestamp('visible_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('publicado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();

            $table->index(['activo', 'visible_desde', 'visible_hasta']);
            $table->index(['destino_tipo', 'grupo_id']);
            $table->index(['prioridad', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_alumno_avisos');
    }
};
