<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addHistoricalMetadata();
        $this->alignHistoricalStatuses();

        if (DB::getDriverName() === 'mysql') {
            $this->replaceDestructiveForeignKeys();
            $this->allowCargoReuseAfterCancelledAgreement();
        }
    }

    private function addHistoricalMetadata(): void
    {
        if (Schema::hasTable('grupos')) {
            Schema::table('grupos', function (Blueprint $table) {
                if (! Schema::hasColumn('grupos', 'activo')) {
                    $table->boolean('activo')->default(true)->index();
                }
                if (! Schema::hasColumn('grupos', 'archivado_at')) {
                    $table->timestamp('archivado_at')->nullable();
                }
                if (! Schema::hasColumn('grupos', 'archivado_por_id')) {
                    $table->foreignId('archivado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
                }
                if (! Schema::hasColumn('grupos', 'motivo_archivo')) {
                    $table->text('motivo_archivo')->nullable();
                }
            });
        }

        if (Schema::hasTable('calendarios_academicos')) {
            Schema::table('calendarios_academicos', function (Blueprint $table) {
                if (! Schema::hasColumn('calendarios_academicos', 'cancelado_por_id')) {
                    $table->foreignId('cancelado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
                }
                if (! Schema::hasColumn('calendarios_academicos', 'fecha_cancelacion')) {
                    $table->timestamp('fecha_cancelacion')->nullable();
                }
                if (! Schema::hasColumn('calendarios_academicos', 'motivo_cancelacion')) {
                    $table->text('motivo_cancelacion')->nullable();
                }
            });
        }

        if (Schema::hasTable('convenios')) {
            Schema::table('convenios', function (Blueprint $table) {
                if (! Schema::hasColumn('convenios', 'cancelado_por_id')) {
                    $table->foreignId('cancelado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
                }
                if (! Schema::hasColumn('convenios', 'fecha_cancelacion')) {
                    $table->timestamp('fecha_cancelacion')->nullable();
                }
                if (! Schema::hasColumn('convenios', 'motivo_cancelacion')) {
                    $table->text('motivo_cancelacion')->nullable();
                }
            });
        }

        if (Schema::hasTable('prospectos')) {
            Schema::table('prospectos', function (Blueprint $table) {
                if (! Schema::hasColumn('prospectos', 'archivado_at')) {
                    $table->timestamp('archivado_at')->nullable();
                }
                if (! Schema::hasColumn('prospectos', 'archivado_por_id')) {
                    $table->foreignId('archivado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('seguimientos')) {
            Schema::table('seguimientos', function (Blueprint $table) {
                if (! Schema::hasColumn('seguimientos', 'cancelado_por_id')) {
                    $table->foreignId('cancelado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
                }
                if (! Schema::hasColumn('seguimientos', 'fecha_cancelacion')) {
                    $table->timestamp('fecha_cancelacion')->nullable();
                }
                if (! Schema::hasColumn('seguimientos', 'motivo_cancelacion')) {
                    $table->text('motivo_cancelacion')->nullable();
                }
            });
        }
    }

    private function alignHistoricalStatuses(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('convenios')) {
            DB::statement("ALTER TABLE `convenios` MODIFY `estatus` ENUM('Activo','Finalizado','Cancelado') NOT NULL DEFAULT 'Activo'");
        }

        if (Schema::hasTable('parcialidades_convenio')) {
            DB::statement("ALTER TABLE `parcialidades_convenio` MODIFY `estatus` ENUM('Pendiente','Pagado','Parcialmente Pagado','Cancelada') NOT NULL DEFAULT 'Pendiente'");
        }
    }

    private function replaceDestructiveForeignKeys(): void
    {
        $foreignKeys = [
            ['grupos', 'ciclo_escolar_id', 'ciclos_escolares'],
            ['grupos', 'programa_id', 'programas'],
            ['calendarios_academicos', 'grupo_id', 'grupos'],
            ['calendario_materias', 'calendario_academico_id', 'calendarios_academicos'],
            ['calendario_sesiones', 'calendario_materia_id', 'calendario_materias'],
            ['becas', 'alumno_id', 'alumnos'],
            ['cargos', 'alumno_id', 'alumnos'],
            ['pagos', 'alumno_id', 'alumnos'],
            ['convenios', 'alumno_id', 'alumnos'],
            ['documentos_alumnos', 'alumno_id', 'alumnos'],
            ['cargo_recurrente_ejecuciones', 'alumno_id', 'alumnos'],
            ['cargo_convenio', 'cargo_id', 'cargos'],
            ['cargo_convenio', 'convenio_id', 'convenios'],
            ['parcialidades_convenio', 'convenio_id', 'convenios'],
            ['cargo_pago', 'cargo_id', 'cargos'],
            ['cargo_pago', 'pago_id', 'pagos'],
            ['pago_parcialidad', 'pago_id', 'pagos'],
            ['pago_parcialidad', 'parcialidad_id', 'parcialidades_convenio'],
            ['ajustes_caja', 'corte_caja_id', 'cortes_caja'],
            ['cargos_masivos', 'concepto_id', 'conceptos_pagos'],
            ['cargos_masivos', 'usuario_id', 'usuarios'],
            ['solicitudes_pago_docentes', 'docente_id', 'docentes'],
            ['solicitudes_pago_docentes', 'creado_por_id', 'usuarios'],
        ];

        foreach ($foreignKeys as [$table, $column, $referencedTable]) {
            $this->replaceForeignKeyWithRestrict($table, $column, $referencedTable);
        }
    }

    private function replaceForeignKeyWithRestrict(string $table, string $column, string $referencedTable): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $constraint = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$table, $column]
        );

        if (! $constraint?->name) {
            return;
        }

        $constraintName = str_replace('`', '``', $constraint->name);
        $newConstraintName = str_replace('`', '``', "{$table}_{$column}_foreign");

        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$newConstraintName}` " .
            "FOREIGN KEY (`{$column}`) REFERENCES `{$referencedTable}` (`id`) ON DELETE RESTRICT"
        );
    }

    private function allowCargoReuseAfterCancelledAgreement(): void
    {
        if (! Schema::hasTable('cargo_convenio')) {
            return;
        }

        $singleColumnUniqueIndexes = DB::select(
            "SELECT INDEX_NAME AS name
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'cargo_convenio'
               AND NON_UNIQUE = 0
             GROUP BY INDEX_NAME
             HAVING COUNT(*) = 1
                AND MAX(COLUMN_NAME) = 'cargo_id'"
        );

        foreach ($singleColumnUniqueIndexes as $index) {
            if ($index->name !== 'PRIMARY') {
                $indexName = str_replace('`', '``', $index->name);
                DB::statement("ALTER TABLE `cargo_convenio` DROP INDEX `{$indexName}`");
            }
        }

        $compositeExists = DB::selectOne(
            "SELECT INDEX_NAME AS name
             FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'cargo_convenio'
               AND INDEX_NAME = 'cargo_convenio_unico'
             LIMIT 1"
        );

        if (! $compositeExists) {
            DB::statement('ALTER TABLE `cargo_convenio` ADD UNIQUE `cargo_convenio_unico` (`cargo_id`, `convenio_id`)');
        }
    }

    public function down(): void
    {
        // Migración de seguridad deliberadamente conservadora.
        // No se restauran cascadas destructivas ni se eliminan metadatos históricos.
    }
};
