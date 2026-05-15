<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'operacion_uuid')) {
                $table->uuid('operacion_uuid')->nullable()->unique()->after('recibo_version');
            }
        });

        Schema::table('cortes_caja', function (Blueprint $table) {
            if (! Schema::hasColumn('cortes_caja', 'usuario_caja_abierta_id')) {
                $table->unsignedBigInteger('usuario_caja_abierta_id')->nullable()->unique()->after('usuario_id');
                $table->index(['usuario_caja_abierta_id', 'estatus'], 'cortes_caja_usuario_abierta_estatus_index');
            }
        });

        if (Schema::hasColumn('cortes_caja', 'usuario_caja_abierta_id')) {
            DB::table('cortes_caja')
                ->where('estatus', 'Abierta')
                ->whereNull('usuario_caja_abierta_id')
                ->update(['usuario_caja_abierta_id' => DB::raw('usuario_id')]);
        }

        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes_pago_docentes', 'pago_operacion_uuid')) {
                $table->uuid('pago_operacion_uuid')->nullable()->unique()->after('comprobante_pago_original');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_pago_docentes', 'pago_operacion_uuid')) {
                $table->dropUnique('solicitudes_pago_docentes_pago_operacion_uuid_unique');
                $table->dropColumn('pago_operacion_uuid');
            }
        });

        Schema::table('cortes_caja', function (Blueprint $table) {
            if (Schema::hasColumn('cortes_caja', 'usuario_caja_abierta_id')) {
                $table->dropIndex('cortes_caja_usuario_abierta_estatus_index');
                $table->dropUnique('cortes_caja_usuario_caja_abierta_id_unique');
                $table->dropColumn('usuario_caja_abierta_id');
            }
        });

        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'operacion_uuid')) {
                $table->dropUnique('pagos_operacion_uuid_unique');
                $table->dropColumn('operacion_uuid');
            }
        });
    }
};
