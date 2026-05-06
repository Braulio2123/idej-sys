<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_alumnos', function (Blueprint $table) {
            $table->foreignId('requisito_documental_id')
                ->nullable()
                ->after('alumno_id')
                ->constrained('requisitos_documentales')
                ->nullOnDelete();

            $table->index(['alumno_id', 'requisito_documental_id'], 'documentos_alumnos_requisito_idx');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_alumnos', function (Blueprint $table) {
            $table->dropIndex('documentos_alumnos_requisito_idx');
            $table->dropConstrainedForeignId('requisito_documental_id');
        });
    }
};
