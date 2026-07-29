<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('calendarios_academicos') && Schema::hasColumn('calendarios_academicos', 'estatus')) {
            // Se amplía temporalmente el catálogo para poder convertir registros
            // creados con los estados anteriores sin truncar información.
            Schema::table('calendarios_academicos', function (Blueprint $table) {
                $table->enum('estatus', [
                    'Borrador',
                    'Planeado',
                    'Aprobado',
                    'Agendado',
                    'En curso',
                    'Finalizado',
                    'Cancelado',
                ])->default('Agendado')->change();
            });

            DB::table('calendarios_academicos')
                ->whereIn('estatus', ['Borrador', 'Planeado', 'Aprobado'])
                ->update(['estatus' => 'Agendado']);

            Schema::table('calendarios_academicos', function (Blueprint $table) {
                $table->enum('estatus', ['Agendado', 'En curso', 'Finalizado', 'Cancelado'])
                    ->default('Agendado')
                    ->change();
            });
        }

        if (Schema::hasTable('docentes') && Schema::hasColumn('docentes', 'estatus')) {
            Schema::table('docentes', function (Blueprint $table) {
                $table->enum('estatus', ['Pendiente de Datos', 'Activo', 'Inactivo'])
                    ->default('Pendiente de Datos')
                    ->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('calendarios_academicos') && Schema::hasColumn('calendarios_academicos', 'estatus')) {
            Schema::table('calendarios_academicos', function (Blueprint $table) {
                $table->enum('estatus', [
                    'Borrador',
                    'Planeado',
                    'Aprobado',
                    'Agendado',
                    'En curso',
                    'Finalizado',
                    'Cancelado',
                ])->default('Borrador')->change();
            });

            DB::table('calendarios_academicos')
                ->where('estatus', 'Agendado')
                ->update(['estatus' => 'Planeado']);

            Schema::table('calendarios_academicos', function (Blueprint $table) {
                $table->enum('estatus', ['Borrador', 'Planeado', 'Aprobado', 'En curso', 'Finalizado', 'Cancelado'])
                    ->default('Borrador')
                    ->change();
            });
        }

        if (Schema::hasTable('docentes') && Schema::hasColumn('docentes', 'estatus')) {
            DB::table('docentes')
                ->where('estatus', 'Inactivo')
                ->update(['estatus' => 'Pendiente de Datos']);

            Schema::table('docentes', function (Blueprint $table) {
                $table->enum('estatus', ['Pendiente de Datos', 'Activo'])
                    ->default('Pendiente de Datos')
                    ->change();
            });
        }
    }
};
