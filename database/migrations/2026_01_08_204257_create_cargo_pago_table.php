<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo_pago', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cargo_id')
                ->constrained('cargos')
                ->cascadeOnDelete();

            $table->foreignId('pago_id')
                ->constrained('pagos')
                ->cascadeOnDelete();

            $table->decimal('monto_aplicado', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_pago');
    }
};
