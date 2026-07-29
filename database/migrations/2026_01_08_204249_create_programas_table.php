<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 30)->nullable()->unique();
            $table->string('nombre')->unique();
            $table->string('nivel')->nullable();
            $table->string('modalidad', 50)->nullable();
            $table->unsignedSmallInteger('duracion_periodos')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['nivel', 'activo']);
            $table->index(['modalidad', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programas');
    }
};
