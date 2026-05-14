<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Seguridad / configuración base
            RolSeeder::class,
            UsuarioSeeder::class,
            ConfiguracionInstitucionalSeeder::class,

            // 2. Catálogos académicos base
            ProgramaSeeder::class,
            CicloEscolarSeeder::class,
            DocenteSeeder::class,
            GrupoSeeder::class,
            MateriaSeeder::class,
            DiaNoLaboralSeeder::class,
            RequisitoDocumentalSeeder::class,

            // 3. Alumnos y finanzas base
            AlumnoSeeder::class,
            ConceptoPagoSeeder::class,
            BecaSeeder::class,
            CargoMasivoSeeder::class,

            // 4. Demo integral para probar módulos reales del sistema
            DatosDemoIntegralSeeder::class,

            // 5. Solicitudes adicionales aleatorias para probar listados/filtros
            SolicitudPagoDocenteSeeder::class,
        ]);
    }
}
