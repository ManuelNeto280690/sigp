<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            ConfiguracaoSeeder::class, // Adicionado
            EmbarcacaoSeeder::class,
            AlertaSeeder::class,
            IncidenteSeeder::class,
            InspecaoSeeder::class,
        ]);
    }
}
