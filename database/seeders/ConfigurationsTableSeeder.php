<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ConfigurationsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('configurations')->insert([
            [
                'id' => 1,
                'code' => 'COGS',
                'name' => 'cogs_method',
                'types' => 'mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'code' => 'S-PAY',
                'name' => 'sale_payment',
                'types' => 'mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'code' => 'DISC',
                'name' => 'sale_discount',
                'types' => 'non-mandatory',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'code' => 'SHP-S',
                'name' => 'shipping_sale_expense',
                'types' => 'non-mandatory',
                'status' => 0,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'code' => 'P-PAY',
                'name' => 'purchase_payment',
                'types' => 'mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'code' => 'SHP-P',
                'name' => 'shipping_purchase_expense',
                'types' => 'non-mandatory',
                'status' => 0,
                'created_at' => now(),

            ],
            [
                'id' => 7,
                'code' => 'EXP',
                'name' => 'expired_date_settings',
                'types' => 'non-mandatory',
                'status' => 0,
                'created_at' => now(),
            ]
        ]);
    }
}
