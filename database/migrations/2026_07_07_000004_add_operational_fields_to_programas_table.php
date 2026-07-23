<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            if (! Schema::hasColumn('programas', 'clave')) {
                $table->string('clave', 30)->nullable()->after('id');
            }
            if (! Schema::hasColumn('programas', 'modalidad')) {
                $table->string('modalidad', 50)->nullable()->after('nivel');
            }
            if (! Schema::hasColumn('programas', 'duracion_periodos')) {
                $table->unsignedSmallInteger('duracion_periodos')->nullable()->after('modalidad');
            }
            if (! Schema::hasColumn('programas', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('duracion_periodos');
            }
            if (! Schema::hasColumn('programas', 'activo')) {
                $table->boolean('activo')->default(true)->after('descripcion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('programas', function (Blueprint $table) {
            foreach (['clave', 'modalidad', 'duracion_periodos', 'descripcion', 'activo'] as $column) {
                if (Schema::hasColumn('programas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
