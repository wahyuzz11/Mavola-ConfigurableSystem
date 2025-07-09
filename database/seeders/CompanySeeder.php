<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('company')->insert([
            'id' => 1,
            'name' => 'Testing CIS',
            'phone_number' => '082144564472',
            'address' => 'Jalan hayam wuruk no 420',
            'email' => 'test@gmail.com',
            'created_at' => now(),
        ]); 
    }
}
