<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([

            // 1️⃣ Seguridad / base
            ConfiguracionInstitucionalSeeder::class,
            RolSeeder::class,
            UsuarioSeeder::class,

            // 2️⃣ Catálogos académicos
            ProgramaSeeder::class,
            MateriaSeeder::class,
            RequisitoDocumentalSeeder::class,
            CicloEscolarSeeder::class,

            // 3️⃣ Personal
            DocenteSeeder::class,

            // 4️⃣ Estructura académica
            GrupoSeeder::class,
            HorarioAcademicoSeeder::class,
            DiaNoLaboralSeeder::class,

            // 5️⃣ Alumnos
            AlumnoSeeder::class,
            BecaSeeder::class,

            // 6️⃣ Finanzas
            ConceptoPagoSeeder::class,
            CargoMasivoSeeder::class,          // opcional

            // 7️⃣ Operaciones finales
            SolicitudPagoDocenteSeeder::class,
        ]);
    }
}
