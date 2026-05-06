<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conceptos_pagos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->decimal('monto_base', 10, 2)->default(0);
            $table->boolean('es_becable')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conceptos_pagos');
    }
};
