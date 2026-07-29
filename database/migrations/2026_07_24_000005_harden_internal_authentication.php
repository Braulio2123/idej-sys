<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuarios') && ! Schema::hasColumn('usuarios', 'auth_version')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->unsignedBigInteger('auth_version')
                    ->default(1)
                    ->after('password_changed_at');
            });
        }

        if (Schema::hasTable('usuarios') && Schema::hasColumn('usuarios', 'password_changed_at')) {
            DB::table('usuarios')
                ->whereNull('password_changed_at')
                ->update([
                    'password_changed_at' => DB::raw('COALESCE(updated_at, created_at, CURRENT_TIMESTAMP)'),
                ]);
        }

        if (Schema::hasTable('password_reset_tokens')
            && ! Schema::hasColumn('password_reset_tokens', 'auth_version')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->unsignedBigInteger('auth_version')
                    ->nullable()
                    ->after('token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('password_reset_tokens')
            && Schema::hasColumn('password_reset_tokens', 'auth_version')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropColumn('auth_version');
            });
        }

        if (Schema::hasTable('usuarios') && Schema::hasColumn('usuarios', 'auth_version')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropColumn('auth_version');
            });
        }
    }
};
