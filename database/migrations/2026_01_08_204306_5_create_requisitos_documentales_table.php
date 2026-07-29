<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisitos_documentales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('programa_id')
                ->nullable()
                ->constrained('programas')
                ->nullOnDelete();

            $table->string('nivel', 80)->nullable();
            $table->string('tipo_documento', 120);
            $table->text('descripcion')->nullable();
            $table->boolean('obligatorio')->default(true);
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);

            $table->timestamps();

            $table->index(['programa_id', 'activo']);
            $table->index(['nivel', 'activo']);
            $table->index(['tipo_documento', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitos_documentales');
    }
};
