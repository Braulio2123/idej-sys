<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campos exclusivos para el acceso del Portal Alumno PWA.
     *
     * No se usa la columna `password` para evitar confundir este acceso con
     * usuarios administrativos del sistema interno.
     */
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            if (! Schema::hasColumn('alumnos', 'portal_password')) {
                $table->string('portal_password')->nullable()->after('telefono');
            }

            if (! Schema::hasColumn('alumnos', 'portal_activo')) {
                $table->boolean('portal_activo')->default(true)->after('portal_password');
            }

            if (! Schema::hasColumn('alumnos', 'portal_ultimo_acceso_at')) {
                $table->timestamp('portal_ultimo_acceso_at')->nullable()->after('portal_activo');
            }

            if (! Schema::hasColumn('alumnos', 'portal_remember_token')) {
                $table->string('portal_remember_token', 100)->nullable()->after('portal_ultimo_acceso_at');
            }
        });

        // Credencial temporal para pruebas locales.
        // Recomendacion: cambiarla por alumno antes de liberar a produccion.
        DB::table('alumnos')
            ->whereNull('portal_password')
            ->update([
                'portal_password' => Hash::make('alumno123'),
                'portal_activo' => true,
            ]);
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('alumnos', 'portal_password') ? 'portal_password' : null,
            Schema::hasColumn('alumnos', 'portal_activo') ? 'portal_activo' : null,
            Schema::hasColumn('alumnos', 'portal_ultimo_acceso_at') ? 'portal_ultimo_acceso_at' : null,
            Schema::hasColumn('alumnos', 'portal_remember_token') ? 'portal_remember_token' : null,
        ]));

        if ($columns !== []) {
            Schema::table('alumnos', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
