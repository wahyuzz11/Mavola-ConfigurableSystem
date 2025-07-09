<?php

namespace Database\Seeders;

use ConfigurationsTableSeeder;
use Illuminate\Database\Seeder;
use SubConfigurationsTableSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
           ConfigurationsTableSeeder::class,
           SubConfigurationsTableSeeder::class,
           CompanySeeder::class,           
        ]);
    }
}
