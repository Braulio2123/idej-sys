<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_internas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('rol_clave', 40)->nullable()->index();
            $table->string('tipo', 80)->default('general')->index();
            $table->string('modulo', 120)->nullable()->index();
            $table->string('titulo', 180);
            $table->text('mensaje')->nullable();
            $table->string('url')->nullable();
            $table->string('severidad', 30)->default('media')->index();
            $table->string('referencia_tipo')->nullable()->index();
            $table->unsignedBigInteger('referencia_id')->nullable()->index();
            $table->string('hash', 191)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('leida_at')->nullable()->index();
            $table->timestamp('archivada_at')->nullable()->index();
            $table->timestamps();

            $table->index(['usuario_id', 'leida_at', 'archivada_at'], 'notificaciones_usuario_estado_index');
            $table->index(['rol_clave', 'leida_at', 'archivada_at'], 'notificaciones_rol_estado_index');
            $table->index(['referencia_tipo', 'referencia_id'], 'notificaciones_referencia_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_internas');
    }
};
