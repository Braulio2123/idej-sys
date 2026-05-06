<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo_convenio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cargo_id')
                ->constrained('cargos')
                ->cascadeOnDelete();

            $table->foreignId('convenio_id')
                ->constrained('convenios')
                ->cascadeOnDelete();

            $table->decimal('monto_original', 10, 2);
            $table->decimal('monto_adeudo_original', 10, 2);
            $table->string('estatus_original', 50)->default('Pendiente');

            $table->timestamps();

            $table->unique('cargo_id');
            $table->index('convenio_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo_convenio');
    }
};
