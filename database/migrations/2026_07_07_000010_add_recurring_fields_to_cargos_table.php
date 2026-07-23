<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->foreignId('cargo_recurrente_plan_id')
                ->nullable()
                ->after('concepto_id')
                ->constrained('planes_cargos_recurrentes')
                ->nullOnDelete();
            $table->char('periodo_recurrente', 7)->nullable()->after('cargo_recurrente_plan_id');
            $table->boolean('generado_automaticamente')->default(false)->after('periodo_recurrente');
            $table->index(['cargo_recurrente_plan_id', 'periodo_recurrente'], 'cargos_recurrente_plan_periodo_idx');
            $table->index(['generado_automaticamente', 'fecha_vencimiento'], 'cargos_auto_vencimiento_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropIndex('cargos_auto_vencimiento_idx');
            $table->dropIndex('cargos_recurrente_plan_periodo_idx');
            $table->dropConstrainedForeignId('cargo_recurrente_plan_id');
            $table->dropColumn(['periodo_recurrente', 'generado_automaticamente']);
        });
    }
};
