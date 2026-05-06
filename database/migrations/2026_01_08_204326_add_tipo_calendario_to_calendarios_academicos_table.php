<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendarios_academicos', function (Blueprint $table) {
            if (!Schema::hasColumn('calendarios_academicos', 'tipo_calendario')) {
                $table->string('tipo_calendario', 80)->default('Personalizado')->after('modalidad');
                $table->index('tipo_calendario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calendarios_academicos', function (Blueprint $table) {
            if (Schema::hasColumn('calendarios_academicos', 'tipo_calendario')) {
                $table->dropIndex(['tipo_calendario']);
                $table->dropColumn('tipo_calendario');
            }
        });
    }
};
