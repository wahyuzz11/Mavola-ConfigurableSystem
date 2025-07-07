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
                'types' => 'non-mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'code' => 'INV-T',
                'name' => 'inventory_tracking_method',
                'types' => 'mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'code' => 'S-PAY',
                'name' => 'sale_payment',
                'types' => 'mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'code' => 'DISC',
                'name' => 'sale_discount',
                'types' => 'non-mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'code' => 'DEL',
                'name' => 'shipping_sale_method',
                'types' => 'mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'code' => 'P-PAY',
                'name' => 'purchase_payment',
                'types' => 'mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
            [
                'id' => 7,
                'code' => 'RE',
                'name' => 'receiving_purchase_method',
                'types' => 'mandatory',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
