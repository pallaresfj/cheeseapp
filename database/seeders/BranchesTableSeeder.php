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
                'name' => '1 Sabanas',
                'address' => 'Calle 5 3 04',
                'phone' => '3145028741',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '2 Los Olivos',
                'address' => 'Los Olivos',
                'phone' => '3145028741',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '3 Los Playones',
                'address' => 'Los Playones',
                'phone' => '3145028741',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '4 La Gloria',
                'address' => 'La Gloria',
                'phone' => '3145028741',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '5 San Basilio',
                'address' => 'San Basilio',
                'phone' => '3145028741',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '6 El Playon',
                'address' => 'El Playón',
                'phone' => '3145028741',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '7 Las Pavitas',
                'address' => 'Las Pavitas',
                'phone' => '3145028741',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}