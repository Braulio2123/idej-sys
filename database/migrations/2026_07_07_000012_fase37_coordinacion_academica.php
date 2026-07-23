<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            if (! Schema::hasColumn('docentes', 'banco')) {
                $table->string('banco')->nullable()->after('numero_cuenta');
            }
            if (! Schema::hasColumn('docentes', 'curriculum_path')) {
                $table->string('curriculum_path')->nullable()->after('banco');
            }
            if (! Schema::hasColumn('docentes', 'titulo_cedula_path')) {
                $table->string('titulo_cedula_path')->nullable()->after('curriculum_path');
            }
            if (! Schema::hasColumn('docentes', 'constancia_fiscal_path')) {
                $table->string('constancia_fiscal_path')->nullable()->after('titulo_cedula_path');
            }
        });

        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes_pago_docentes', 'calendario_sesion_ids')) {
                $table->json('calendario_sesion_ids')->nullable()->after('calendario_materia_id');
            }
            if (! Schema::hasColumn('solicitudes_pago_docentes', 'curso_sesion_ids')) {
                $table->json('curso_sesion_ids')->nullable()->after('curso_sesion_id');
            }
            if (! Schema::hasColumn('solicitudes_pago_docentes', 'fecha_tentativa_pago')) {
                $table->date('fecha_tentativa_pago')->nullable()->after('fecha_limite_pago');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            foreach (['calendario_sesion_ids', 'curso_sesion_ids', 'fecha_tentativa_pago'] as $column) {
                if (Schema::hasColumn('solicitudes_pago_docentes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('docentes', function (Blueprint $table) {
            foreach (['banco', 'curriculum_path', 'titulo_cedula_path', 'constancia_fiscal_path'] as $column) {
                if (Schema::hasColumn('docentes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
