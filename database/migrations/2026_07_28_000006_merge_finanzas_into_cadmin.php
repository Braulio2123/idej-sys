<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El IDEJ no opera un área independiente de Finanzas. Coordinación
     * Administrativa absorbe las responsabilidades administrativas y
     * financieras. Esta migración conserva usuarios e historial, reasigna
     * las cuentas existentes y retira el rol obsoleto.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $cadmin = DB::table('roles')->where('clave', 'CAdmin')->first();

        if (! $cadmin) {
            $cadminId = DB::table('roles')->insertGetId([
                'nombre' => 'Coordinación Administrativa IDEJ',
                'clave' => 'CAdmin',
                'descripcion' => 'Gestión administrativa, financiera y operativa.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $cadmin = (object) ['id' => $cadminId];
        }

        $finanzas = DB::table('roles')
            ->where('clave', 'Finanzas')
            ->orWhere('nombre', 'Finanzas IDEJ')
            ->first();

        if (! $finanzas) {
            return;
        }

        if (Schema::hasTable('usuarios') && Schema::hasColumn('usuarios', 'rol_id')) {
            $usuariosUpdates = [
                'rol_id' => $cadmin->id,
                'updated_at' => now(),
            ];

            // El cambio de rol debe invalidar sesiones y tokens previos para
            // que los permisos de CAdmin se apliquen en el siguiente acceso.
            if (Schema::hasColumn('usuarios', 'auth_version')) {
                $usuariosUpdates['auth_version'] = DB::raw('auth_version + 1');
            }

            if (Schema::hasColumn('usuarios', 'remember_token')) {
                $usuariosUpdates['remember_token'] = null;
            }

            DB::table('usuarios')
                ->where('rol_id', $finanzas->id)
                ->update($usuariosUpdates);

            // La cuenta incluida únicamente para demostración deja de estar
            // activa, pero se conserva para no romper referencias históricas.
            DB::table('usuarios')
                ->where('email', 'finanzas@idej.test')
                ->update([
                    'activo' => false,
                    'updated_at' => now(),
                ]);
        }

        DB::table('roles')->where('id', $finanzas->id)->delete();
    }

    /**
     * La reasignación de usuarios es deliberadamente irreversible porque no
     * existe una forma segura de distinguir cuentas históricas después de la
     * integración. El rollback solo recrea el catálogo legado.
     */
    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        DB::table('roles')->updateOrInsert(
            ['clave' => 'Finanzas'],
            [
                'nombre' => 'Finanzas IDEJ',
                'descripcion' => 'Rol legado sin usuarios reasignados automáticamente.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
};
