<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('branches')->insert([
            [
                'name' => 'Sabanas',
                'address' => 'Calle 123 #45-67',
                'phone' => '3001234567',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'El Playón',
                'address' => 'Carrera 10 #20-30',
                'phone' => '3109876543',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Las Pavitas',
                'address' => 'Avenida Siempre Viva #742',
                'phone' => '3053425643',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'San Basilio',
                'address' => 'Calle 456 #78-90',
                'phone' => '3116543210',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}