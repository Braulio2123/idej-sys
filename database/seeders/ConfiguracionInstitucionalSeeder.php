<?php

namespace Database\Seeders;

use App\Models\ConfiguracionInstitucional;
use Illuminate\Database\Seeder;

class ConfiguracionInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        ConfiguracionInstitucional::query()->firstOrCreate(
            ['id' => 1],
            ConfiguracionInstitucional::defaults()
        );
    }
}
