<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_parcialidad', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pago_id')
                ->constrained('pagos')
                ->cascadeOnDelete();

            $table->foreignId('parcialidad_id')
                ->constrained('parcialidades_convenio')
                ->cascadeOnDelete();

            $table->decimal('monto_aplicado', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_parcialidad');
    }
};
