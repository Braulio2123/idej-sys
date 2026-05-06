<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'recibo_uuid')) {
                $table->uuid('recibo_uuid')
                    ->nullable()
                    ->unique()
                    ->after('folio_recibo');
            }

            if (! Schema::hasColumn('pagos', 'recibo_emitido_at')) {
                $table->timestamp('recibo_emitido_at')
                    ->nullable()
                    ->after('recibo_uuid');
            }

            if (! Schema::hasColumn('pagos', 'recibo_version')) {
                $table->unsignedTinyInteger('recibo_version')
                    ->default(1)
                    ->after('recibo_emitido_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'recibo_uuid')) {
                $table->dropUnique('pagos_recibo_uuid_unique');
            }

            foreach (['recibo_uuid', 'recibo_emitido_at', 'recibo_version'] as $column) {
                if (Schema::hasColumn('pagos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
