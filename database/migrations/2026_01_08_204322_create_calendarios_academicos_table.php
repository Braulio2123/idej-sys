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
            $table->foreignId('grupo_id')->constrained('grupos')->restrictOnDelete();
            $table->foreignId('ciclo_escolar_id')->nullable()->constrained('ciclos_escolares')->nullOnDelete();
            $table->string('nombre');
            $table->string('periodo', 50)->nullable();
            $table->enum('modalidad', ['Presencial', 'Virtual', 'Mixta'])->default('Presencial');
            $table->string('tipo_calendario', 80)->default('Personalizado');
            $table->enum('estatus', ['Agendado', 'En curso', 'Finalizado', 'Cancelado'])->default('Agendado');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('aprobado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->foreignId('cancelado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('fecha_cancelacion')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamps();

            $table->index(['grupo_id', 'estatus']);
            $table->index(['periodo', 'estatus']);
            $table->index('tipo_calendario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendarios_academicos');
    }
};
