<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendarios_academicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('ciclo_escolar_id')->nullable()->constrained('ciclos_escolares')->nullOnDelete();
            $table->string('nombre');
            $table->string('periodo', 50)->nullable();
            $table->enum('modalidad', ['Presencial', 'Virtual', 'Mixta'])->default('Presencial');
            $table->enum('estatus', ['Borrador', 'Planeado', 'Aprobado', 'En curso', 'Finalizado', 'Cancelado'])->default('Borrador');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('aprobado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();

            $table->index(['grupo_id', 'estatus']);
            $table->index(['periodo', 'estatus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendarios_academicos');
    }
};
