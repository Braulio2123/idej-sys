<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->foreignId('beca_id')
                ->nullable()
                ->after('concepto_id')
                ->constrained('becas')
                ->nullOnDelete();

            $table->unsignedTinyInteger('beca_porcentaje_aplicado')
                ->default(0)
                ->after('monto_original');

            $table->decimal('beca_monto_aplicado', 10, 2)
                ->default(0)
                ->after('beca_porcentaje_aplicado');
        });
    }

    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropForeign(['beca_id']);
            $table->dropColumn([
                'beca_id',
                'beca_porcentaje_aplicado',
                'beca_monto_aplicado',
            ]);
        });
    }
};
