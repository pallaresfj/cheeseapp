<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FarmTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('farm_types')->insert([
            [
                'name' => 'Tipo 1',
                'description' => 'Calidad Regular',
                'base_price' => 1300.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tipo 2',
                'description' => 'Buena Calidad',
                'base_price' => 1350.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tipo 3',
                'description' => 'Alta Calidad',
                'base_price' => 1400.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tipo 4',
                'description' => 'Calidad Premium',
                'base_price' => 1500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
