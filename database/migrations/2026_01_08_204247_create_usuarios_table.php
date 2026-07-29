<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('telefono_notificaciones', 30)->nullable();
            $table->string('whatsapp_notificaciones', 30)->nullable();
            $table->boolean('notificar_email')->default(true);
            $table->boolean('notificar_sms')->default(false);
            $table->boolean('notificar_whatsapp')->default(false);
            $table->string('password');
            $table->rememberToken();

            $table->foreignId('rol_id')
                ->constrained('roles')
                ->restrictOnDelete();

            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso_at')->nullable();
            $table->string('ultimo_login_ip', 45)->nullable();
            $table->text('ultimo_user_agent')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->unsignedBigInteger('auth_version')->default(1);
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('temporary_password_generated_at')->nullable();
            $table->timestamp('temporary_password_expires_at')->nullable();

            $table->timestamps();

            $table->index(['rol_id', 'activo']);
            $table->index('ultimo_acceso_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
