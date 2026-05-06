<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendario_sesiones', function (Blueprint $table) {
            $table->foreignId('sesion_origen_id')->nullable()->after('id')->constrained('calendario_sesiones')->nullOnDelete();
            $table->foreignId('cancelada_por_id')->nullable()->after('observaciones')->constrained('usuarios')->nullOnDelete();
            $table->foreignId('reprogramada_por_id')->nullable()->after('cancelada_por_id')->constrained('usuarios')->nullOnDelete();
            $table->timestamp('fecha_reprogramacion')->nullable()->after('reprogramada_por_id');
            $table->text('motivo_cancelacion')->nullable()->after('fecha_reprogramacion');
            $table->text('motivo_reprogramacion')->nullable()->after('motivo_cancelacion');
        });
    }

    public function down(): void
    {
        Schema::table('calendario_sesiones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sesion_origen_id');
            $table->dropConstrainedForeignId('cancelada_por_id');
            $table->dropConstrainedForeignId('reprogramada_por_id');
            $table->dropColumn(['fecha_reprogramacion', 'motivo_cancelacion', 'motivo_reprogramacion']);
        });
    }
};
