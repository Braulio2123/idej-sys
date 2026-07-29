<?php

namespace Tests\Unit;

use App\Models\DocumentoAlumno;
use App\Models\Rol;
use App\Models\Usuario;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PrivateFileSecurityTest extends TestCase
{
    #[Test]
    public function student_documents_are_filtered_by_business_function(): void
    {
        $cadmin = $this->usuarioConRol(Rol::CADMIN);
        $academica = $this->usuarioConRol(Rol::ACADEMICA);
        $recepcion = $this->usuarioConRol(Rol::RECEPCION);

        $comprobante = new DocumentoAlumno(['tipo_documento' => 'Comprobante de pago', 'archivo_path' => 'documentos/pago.pdf']);
        $certificado = new DocumentoAlumno(['tipo_documento' => 'Certificado de estudios', 'archivo_path' => 'documentos/certificado.pdf']);
        $identificacion = new DocumentoAlumno(['tipo_documento' => 'Identificación oficial', 'archivo_path' => 'documentos/identificacion.pdf']);
        $restringido = new DocumentoAlumno(['tipo_documento' => 'Documento jurídico especial', 'archivo_path' => 'documentos/restringido.pdf']);

        $this->assertTrue($comprobante->puedeDescargar($cadmin));
        $this->assertTrue($certificado->puedeDescargar($cadmin));
        $this->assertTrue($certificado->puedeDescargar($academica));
        $this->assertFalse($identificacion->puedeDescargar($academica));
        $this->assertTrue($identificacion->puedeDescargar($recepcion));
        $this->assertFalse($restringido->puedeDescargar($recepcion));
    }

    #[Test]
    public function sensitive_controllers_do_not_download_from_the_public_disk(): void
    {
        foreach ([
            app_path('Http/Controllers/DocumentoAlumnoController.php'),
            app_path('Http/Controllers/PagoController.php'),
            app_path('Http/Controllers/CorteCajaController.php'),
            app_path('Http/Controllers/SolicitudPagoDocenteController.php'),
            app_path('Http/Controllers/DocenteController.php'),
        ] as $controller) {
            $source = file_get_contents($controller);

            $this->assertStringNotContainsString("Storage::disk('public')->download", $source, basename($controller));
            $this->assertStringContainsString('PrivateFileService', $source, basename($controller));
        }
    }

    #[Test]
    public function replacements_are_committed_before_old_private_files_are_removed(): void
    {
        $documentos = file_get_contents(app_path('Http/Controllers/DocumentoAlumnoController.php'));
        $docentes = file_get_contents(app_path('Http/Controllers/DocenteController.php'));

        $this->assertMatchesRegularExpression('/DB::transaction.*if \(\$archivoGuardado && \$pathAnterior/s', $documentos);
        $this->assertMatchesRegularExpression('/DB::transaction.*foreach \(\$pathsAnteriores as \$path\)/s', $docentes);
        $this->assertStringContainsString('catch (\\Throwable $e)', $documentos);
        $this->assertStringContainsString('$this->eliminarArchivos($archivosNuevos);', $docentes);
    }

    #[Test]
    public function local_disk_is_private_and_raises_storage_failures(): void
    {
        $source = file_get_contents(config_path('filesystems.php'));

        $this->assertMatchesRegularExpression("/'local'.*'serve' => false.*'throw' => true.*'report' => true/s", $source);
    }

    #[Test]
    public function compatibility_migration_adds_integrity_metadata_for_all_sensitive_files(): void
    {
        $source = file_get_contents(database_path('migrations/2026_07_24_000004_harden_private_file_storage.php'));

        foreach ([
            'archivo_sha256',
            'archivo_verificado_at',
            'archivo_comprobante_sha256',
            'comprobante_sha256',
            'comprobante_pago_sha256',
            "'curriculum', 'titulo_cedula', 'constancia_fiscal'",
        ] as $expected) {
            $this->assertStringContainsString($expected, $source);
        }
    }

    #[Test]
    public function routes_and_commands_keep_sensitive_files_behind_controllers(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $command = file_get_contents(app_path('Console/Commands/AuditarArchivosPrivados.php'));

        $this->assertStringContainsString('alumnos.documentos.download', $routes);
        $this->assertStringContainsString('cortes-caja.movimientos.comprobante', $routes);
        $this->assertStringContainsString('docentes.documentos.download', $routes);
        $this->assertStringContainsString('idej:auditar-archivos-privados', $command);
        $this->assertStringContainsString('Archivos privados sin referencia', $command);
        $this->assertStringNotContainsString('->delete($path)', $command);
    }

    #[Test]
    public function demo_seeder_creates_real_private_files_instead_of_broken_references(): void
    {
        $source = file_get_contents(database_path('seeders/DatosDemoIntegralSeeder.php'));

        $this->assertStringContainsString('Storage::disk(\'local\')->put($path, $contenido)', $source);
        $this->assertStringContainsString("'archivo_sha256' => \$archivo['sha256']", $source);
    }

    private function usuarioConRol(string $clave): Usuario
    {
        $usuario = new Usuario();
        $rol = new Rol();
        $rol->forceFill(['clave' => $clave, 'nombre' => $clave]);
        $usuario->setRelation('rol', $rol);

        return $usuario;
    }
}
