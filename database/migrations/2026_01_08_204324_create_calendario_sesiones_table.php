<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendario_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_origen_id')->nullable()->constrained('calendario_sesiones')->nullOnDelete();
            $table->foreignId('calendario_materia_id')->constrained('calendario_materias')->restrictOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('aula', 100)->nullable();
            $table->enum('modalidad', ['Presencial', 'Virtual', 'Mixta'])->default('Presencial');
            $table->enum('tipo_sesion', ['Clase', 'Coloquio', 'Conferencia', 'Examen', 'Otro'])->default('Clase');
            $table->enum('estatus', ['Programada', 'Confirmada', 'Impartida', 'Suspendida', 'Cancelada'])->default('Programada');
            $table->text('observaciones')->nullable();
            $table->foreignId('cancelada_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('reprogramada_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('fecha_reprogramacion')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->text('motivo_reprogramacion')->nullable();
            $table->timestamps();

            $table->index(['fecha', 'estatus']);
            $table->index(['aula', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_sesiones');
    }
};
