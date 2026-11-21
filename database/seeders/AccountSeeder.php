<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('accounts')->insert([

            // ===== ASSETS (1xxx) =====
            [
                'id' => 1,
                'code' => '1101',
                'name' => 'Kas Tunai',
                'type' => 'Asset',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
    
            [
                'id' => 2,
                'code' => '1102',
                'name' => 'Kas Bank',
                'type' => 'Asset',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),

            ],

            [
                'id' => 3,
                'code' => '1103',
                'name' => 'Persediaan',
                'type' => 'Asset',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===== LIABILITY (2xxx) =====
            [
                'id' => 4,
                'code' => '2101',
                'name' => 'Hutang Usaha',
                'type' => 'Liability',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===== EQUITY (3xxx) =====
            [
                'id' => 5,
                'code' => '3101',
                'name' => 'Modal',
                'type' => 'Equity',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===== REVENUE (4xxx) =====
            [
                'id' => 6,
                'code' => '4101',
                'name' => 'Penjualan',
                'type' => 'Revenue',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'code' => '4102',
                'name' => 'Diskon Penjualan',
                'type' => 'Revenue',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===== EXPENSE (5xxx) =====
            [
                'id' => 8,
                'code' => '5101',
                'name' => 'HPP',
                'type' => 'Expense',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'id' => 9,
                'code' => '6101',
                'name' => 'Biaya Pengiriman Pembelian',
                'type' => 'Expense',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            [
                'id' => 10,
                'code' => '6102',
                'name' => 'Biaya Pengiriman Penjualan',
                'type' => 'Expense',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
