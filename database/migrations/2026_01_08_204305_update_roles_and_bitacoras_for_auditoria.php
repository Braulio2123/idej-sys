<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (! Schema::hasColumn('roles', 'clave')) {
                    $table->string('clave', 50)->nullable()->after('nombre');
                }

                if (! Schema::hasColumn('roles', 'descripcion')) {
                    $table->string('descripcion')->nullable()->after('clave');
                }
            });

            $map = [
                'Administrador IDEJ' => ['Admin', 'Acceso total al sistema.'],
                'Sistemas IDEJ' => ['Sistemas', 'Administración técnica de usuarios, bitácora y soporte.'],
                'Dirección IDEJ' => ['Direccion', 'Consulta ejecutiva de reportes e información institucional.'],
                'Coordinación Administrativa IDEJ' => ['CAdmin', 'Gestión administrativa, financiera y operativa.'],
                'Coordinación Académica IDEJ' => ['Academica', 'Gestión académica, docentes, grupos y solicitudes.'],
                'Recepción IDEJ' => ['Recepcion', 'Atención a alumnos, cargos, pagos y convenios operativos.'],
                'Relaciones Públicas IDEJ' => ['RRPP', 'Seguimiento comercial y consulta de alumnos.'],
                'Finanzas IDEJ' => ['Finanzas', 'Gestión de pagos, reportes financieros y solicitudes aprobadas.'],
            ];

            foreach ($map as $nombre => [$clave, $descripcion]) {
                DB::table('roles')->updateOrInsert(
                    ['nombre' => $nombre],
                    ['clave' => $clave, 'descripcion' => $descripcion, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        if (Schema::hasTable('bitacoras')) {
            Schema::table('bitacoras', function (Blueprint $table) {
                if (! Schema::hasColumn('bitacoras', 'accion')) {
                    $table->string('accion', 120)->nullable()->after('tipo');
                }

                if (! Schema::hasColumn('bitacoras', 'modulo')) {
                    $table->string('modulo', 80)->nullable()->after('accion');
                }

                if (! Schema::hasColumn('bitacoras', 'modelo_type')) {
                    $table->string('modelo_type')->nullable()->after('alumno_id');
                }

                if (! Schema::hasColumn('bitacoras', 'modelo_id')) {
                    $table->unsignedBigInteger('modelo_id')->nullable()->after('modelo_type');
                }

                if (! Schema::hasColumn('bitacoras', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('modelo_id');
                }

                if (! Schema::hasColumn('bitacoras', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                }

                if (! Schema::hasColumn('bitacoras', 'url')) {
                    $table->string('url')->nullable()->after('user_agent');
                }

                if (! Schema::hasColumn('bitacoras', 'metodo_http')) {
                    $table->string('metodo_http', 10)->nullable()->after('url');
                }
            });

            DB::table('bitacoras')
                ->whereNull('accion')
                ->update(['accion' => DB::raw('tipo')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bitacoras')) {
            Schema::table('bitacoras', function (Blueprint $table) {
                foreach (['accion', 'modulo', 'modelo_type', 'modelo_id', 'ip_address', 'user_agent', 'url', 'metodo_http'] as $column) {
                    if (Schema::hasColumn('bitacoras', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'descripcion')) {
                    $table->dropColumn('descripcion');
                }

                if (Schema::hasColumn('roles', 'clave')) {
                    $table->dropColumn('clave');
                }
            });
        }
    }
};
