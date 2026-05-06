<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'estatus')) {
                $table->enum('estatus', ['Activo', 'Cancelado'])
                    ->default('Activo')
                    ->after('monto_total_pagado')
                    ->index();
            }

            if (! Schema::hasColumn('pagos', 'saldo_a_favor_generado')) {
                $table->decimal('saldo_a_favor_generado', 10, 2)
                    ->default(0)
                    ->after('monto_total_pagado');
            }

            if (! Schema::hasColumn('pagos', 'cancelado_por_id')) {
                $table->foreignId('cancelado_por_id')
                    ->nullable()
                    ->after('usuario_id')
                    ->constrained('usuarios')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('pagos', 'fecha_cancelacion')) {
                $table->timestamp('fecha_cancelacion')
                    ->nullable()
                    ->after('fecha_pago');
            }

            if (! Schema::hasColumn('pagos', 'motivo_cancelacion')) {
                $table->text('motivo_cancelacion')
                    ->nullable()
                    ->after('observaciones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'cancelado_por_id')) {
                $table->dropConstrainedForeignId('cancelado_por_id');
            }

            foreach (['estatus', 'saldo_a_favor_generado', 'fecha_cancelacion', 'motivo_cancelacion'] as $column) {
                if (Schema::hasColumn('pagos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
