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
                'name' => 'Tipo A',
                'description' => 'Alta Calidad',
                'base_price' => 1500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tipo B',
                'description' => 'Buena Calidad',
                'base_price' => 1200.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tipo C',
                'description' => 'Calidad Media',
                'base_price' => 1000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
