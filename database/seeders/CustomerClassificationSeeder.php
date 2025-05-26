<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CustomerClassification;

class CustomerClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    

    public function run(): void
    {
        CustomerClassification::insert([
            [
                'name' => 'Minorista',
                'description' => 'Cliente minorista con compras pequeñas',
                'price' => 20000,
            ],
            [
                'name' => 'Mayorista',
                'description' => 'Cliente con compras a gran escala',
                'price' => 19000,
            ],
            [
                'name' => 'Preferencial',
                'description' => 'Cliente frecuente con beneficios adicionales',
                'price' => 18000,
            ],
        ]);
    }
}
