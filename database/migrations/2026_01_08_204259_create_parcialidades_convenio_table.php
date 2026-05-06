<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcialidades_convenio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convenio_id')
                ->constrained('convenios')
                ->cascadeOnDelete();

            $table->decimal('monto_parcialidad', 10, 2);
            $table->decimal('monto_adeudo', 10, 2)->default(0);

            $table->date('fecha_vencimiento');

            $table->enum('estatus', ['Pendiente', 'Pagado', 'Parcialmente Pagado'])
                ->default('Pendiente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcialidades_convenio');
    }
};
