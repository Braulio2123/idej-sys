<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordatorios_enviados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->nullable()->constrained('alumnos')->nullOnDelete();
            $table->string('tipo', 80);
            $table->string('canal', 40);
            $table->date('fecha_recordatorio');
            $table->string('destinatario')->nullable();
            $table->string('referencia_hash', 191)->unique();
            $table->string('estatus', 40)->default('registrado');
            $table->text('respuesta')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'canal', 'fecha_recordatorio'], 'recordatorios_tipo_canal_fecha_idx');
            $table->index(['alumno_id', 'tipo', 'fecha_recordatorio'], 'recordatorios_alumno_tipo_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios_enviados');
    }
};
