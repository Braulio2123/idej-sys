<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_institucionales', function (Blueprint $table) {
            $table->id();

            // Identidad institucional
            $table->string('nombre_institucion', 180)->default('Instituto de Altos Estudios Jurídicos de Jalisco');
            $table->string('nombre_corto', 60)->default('IDEJ');
            $table->string('razon_social', 180)->nullable();
            $table->string('rfc', 20)->nullable();
            $table->string('lema', 180)->nullable();
            $table->string('logo_path')->nullable();

            // Contacto y domicilio
            $table->string('domicilio', 220)->nullable();
            $table->string('colonia', 120)->nullable();
            $table->string('municipio', 120)->nullable();
            $table->string('estado', 120)->default('Jalisco');
            $table->string('codigo_postal', 12)->nullable();
            $table->string('telefono_principal', 40)->nullable();
            $table->string('telefono_secundario', 40)->nullable();
            $table->string('correo_contacto', 150)->nullable();
            $table->string('sitio_web', 180)->nullable();

            // Presentación visual
            $table->string('color_primario', 20)->default('#1E3A8A');
            $table->string('color_secundario', 20)->default('#0D133A');
            $table->string('color_acento', 20)->default('#F59E0B');

            // Recibos y documentos administrativos
            $table->string('recibo_prefijo', 20)->default('IDEJ');
            $table->text('recibo_leyenda')->nullable();
            $table->text('recibo_nota_fiscal')->nullable();
            $table->string('recibo_firma_recibio', 80)->default('Firma de quien recibe');
            $table->string('recibo_firma_conformidad', 80)->default('Firma de conformidad');
            $table->boolean('recibo_mostrar_logo')->default(true);

            // Parámetros operativos
            $table->string('moneda', 10)->default('MXN');
            $table->string('zona_horaria', 80)->default('America/Mexico_City');
            $table->decimal('moratorio_porcentaje', 5, 2)->default(5.00);
            $table->unsignedInteger('moratorio_dias_gracia')->default(0);
            $table->boolean('recordatorios_pago_activos')->default(false);

            $table->foreignId('actualizado_por_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_institucionales');
    }
};
