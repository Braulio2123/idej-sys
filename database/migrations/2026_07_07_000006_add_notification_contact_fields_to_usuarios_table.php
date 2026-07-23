<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'telefono_notificaciones')) {
                $table->string('telefono_notificaciones', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('usuarios', 'whatsapp_notificaciones')) {
                $table->string('whatsapp_notificaciones', 30)->nullable()->after('telefono_notificaciones');
            }
            if (! Schema::hasColumn('usuarios', 'notificar_email')) {
                $table->boolean('notificar_email')->default(true)->after('whatsapp_notificaciones');
            }
            if (! Schema::hasColumn('usuarios', 'notificar_sms')) {
                $table->boolean('notificar_sms')->default(false)->after('notificar_email');
            }
            if (! Schema::hasColumn('usuarios', 'notificar_whatsapp')) {
                $table->boolean('notificar_whatsapp')->default(false)->after('notificar_sms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $columns = [
                'telefono_notificaciones',
                'whatsapp_notificaciones',
                'notificar_email',
                'notificar_sms',
                'notificar_whatsapp',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('usuarios', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
