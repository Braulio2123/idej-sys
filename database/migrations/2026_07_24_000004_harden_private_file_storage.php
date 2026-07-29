<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addDocumentoAlumnoMetadata();
        $this->addPagoMetadata();
        $this->addMovimientoMetadata();
        $this->addSolicitudDocenteMetadata();
        $this->addDocenteMetadata();
    }

    public function down(): void
    {
        // No se eliminan metadatos de integridad en rollback para evitar perder
        // evidencia necesaria para verificar archivos privados ya almacenados.
    }

    private function addDocumentoAlumnoMetadata(): void
    {
        if (! Schema::hasTable('documentos_alumnos')) {
            return;
        }

        Schema::table('documentos_alumnos', function (Blueprint $table) {
            if (! Schema::hasColumn('documentos_alumnos', 'archivo_sha256')) {
                $table->char('archivo_sha256', 64)->nullable()->after('tamano_bytes');
            }
            if (! Schema::hasColumn('documentos_alumnos', 'archivo_verificado_at')) {
                $table->dateTime('archivo_verificado_at')->nullable()->after('archivo_sha256');
            }
        });
    }

    private function addPagoMetadata(): void
    {
        if (! Schema::hasTable('pagos')) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'archivo_comprobante_original')) {
                $table->string('archivo_comprobante_original')->nullable()->after('archivo_comprobante');
            }
            if (! Schema::hasColumn('pagos', 'archivo_comprobante_mime')) {
                $table->string('archivo_comprobante_mime', 120)->nullable()->after('archivo_comprobante_original');
            }
            if (! Schema::hasColumn('pagos', 'archivo_comprobante_tamano')) {
                $table->unsignedBigInteger('archivo_comprobante_tamano')->nullable()->after('archivo_comprobante_mime');
            }
            if (! Schema::hasColumn('pagos', 'archivo_comprobante_sha256')) {
                $table->char('archivo_comprobante_sha256', 64)->nullable()->after('archivo_comprobante_tamano');
            }
        });
    }

    private function addMovimientoMetadata(): void
    {
        if (! Schema::hasTable('movimientos_caja')) {
            return;
        }

        Schema::table('movimientos_caja', function (Blueprint $table) {
            if (! Schema::hasColumn('movimientos_caja', 'comprobante_original')) {
                $table->string('comprobante_original')->nullable()->after('comprobante_path');
            }
            if (! Schema::hasColumn('movimientos_caja', 'comprobante_mime')) {
                $table->string('comprobante_mime', 120)->nullable()->after('comprobante_original');
            }
            if (! Schema::hasColumn('movimientos_caja', 'comprobante_tamano')) {
                $table->unsignedBigInteger('comprobante_tamano')->nullable()->after('comprobante_mime');
            }
            if (! Schema::hasColumn('movimientos_caja', 'comprobante_sha256')) {
                $table->char('comprobante_sha256', 64)->nullable()->after('comprobante_tamano');
            }
        });
    }

    private function addSolicitudDocenteMetadata(): void
    {
        if (! Schema::hasTable('solicitudes_pago_docentes')) {
            return;
        }

        Schema::table('solicitudes_pago_docentes', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes_pago_docentes', 'comprobante_pago_original')) {
                $table->string('comprobante_pago_original')->nullable()->after('comprobante_pago_path');
            }
            if (! Schema::hasColumn('solicitudes_pago_docentes', 'comprobante_pago_mime')) {
                $table->string('comprobante_pago_mime', 120)->nullable()->after('comprobante_pago_original');
            }
            if (! Schema::hasColumn('solicitudes_pago_docentes', 'comprobante_pago_tamano')) {
                $table->unsignedBigInteger('comprobante_pago_tamano')->nullable()->after('comprobante_pago_mime');
            }
            if (! Schema::hasColumn('solicitudes_pago_docentes', 'comprobante_pago_sha256')) {
                $table->char('comprobante_pago_sha256', 64)->nullable()->after('comprobante_pago_tamano');
            }
        });
    }

    private function addDocenteMetadata(): void
    {
        if (! Schema::hasTable('docentes')) {
            return;
        }

        Schema::table('docentes', function (Blueprint $table) {
            foreach (['curriculum', 'titulo_cedula', 'constancia_fiscal'] as $prefix) {
                if (! Schema::hasColumn('docentes', $prefix.'_original')) {
                    $table->string($prefix.'_original')->nullable()->after($prefix.'_path');
                }
                if (! Schema::hasColumn('docentes', $prefix.'_sha256')) {
                    $table->char($prefix.'_sha256', 64)->nullable()->after($prefix.'_original');
                }
            }
        });
    }
};
