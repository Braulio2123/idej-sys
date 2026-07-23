<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('usuarios', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password_changed_at');
            }

            if (! Schema::hasColumn('usuarios', 'temporary_password_generated_at')) {
                $table->timestamp('temporary_password_generated_at')->nullable()->after('must_change_password');
            }

            if (! Schema::hasColumn('usuarios', 'temporary_password_expires_at')) {
                $table->timestamp('temporary_password_expires_at')->nullable()->after('temporary_password_generated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'must_change_password',
                'temporary_password_generated_at',
                'temporary_password_expires_at',
            ]);
        });
    }
};
