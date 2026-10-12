<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KaryaLokalSeeder::class,
            ArticleSeeder::class,
            RoleWorkspaceSeeder::class,
            RoleFeatureDataSeeder::class,
        ]);
    }
}
