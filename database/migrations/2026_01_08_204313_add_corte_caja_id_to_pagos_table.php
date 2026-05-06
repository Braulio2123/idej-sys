<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'corte_caja_id')) {
                $table->foreignId('corte_caja_id')
                    ->nullable()
                    ->after('usuario_id')
                    ->constrained('cortes_caja')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'corte_caja_id')) {
                $table->dropConstrainedForeignId('corte_caja_id');
            }
        });
    }
};
