<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dias_no_laborales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->string('nombre');
            $table->enum('tipo', ['Ley', 'Institucional', 'Interno'])->default('Ley');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dias_no_laborales');
    }
};
