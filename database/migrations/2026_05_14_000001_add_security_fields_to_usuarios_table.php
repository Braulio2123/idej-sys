<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('rol_id');
            $table->timestamp('ultimo_acceso_at')->nullable()->after('activo');
            $table->string('ultimo_login_ip', 45)->nullable()->after('ultimo_acceso_at');
            $table->text('ultimo_user_agent')->nullable()->after('ultimo_login_ip');
            $table->timestamp('password_changed_at')->nullable()->after('ultimo_user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'activo',
                'ultimo_acceso_at',
                'ultimo_login_ip',
                'ultimo_user_agent',
                'password_changed_at',
            ]);
        });
    }
};
