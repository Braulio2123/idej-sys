<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'monto_recibido_efectivo')) {
                $table->decimal('monto_recibido_efectivo', 10, 2)->nullable()->after('monto_total_pagado');
            }

            if (! Schema::hasColumn('pagos', 'cambio_entregado')) {
                $table->decimal('cambio_entregado', 10, 2)->default(0)->after('monto_recibido_efectivo');
            }

            if (! Schema::hasColumn('pagos', 'tratamiento_excedente')) {
                $table->string('tratamiento_excedente', 30)->nullable()->after('cambio_entregado');
            }

            if (! Schema::hasColumn('pagos', 'es_pago_anticipado')) {
                $table->boolean('es_pago_anticipado')->default(false)->after('tratamiento_excedente');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            foreach (['es_pago_anticipado', 'tratamiento_excedente', 'cambio_entregado', 'monto_recibido_efectivo'] as $column) {
                if (Schema::hasColumn('pagos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
