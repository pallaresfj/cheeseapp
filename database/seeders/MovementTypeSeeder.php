<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MovementType;

class MovementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['class' => 'income', 'description' => 'Recaudo de cartera', 'type' => 'fixed'],
            ['class' => 'income', 'description' => 'Venta de productos', 'type' => 'fixed'],
            ['class' => 'expense', 'description' => 'Compra de insumos', 'type' => 'fixed'],
            ['class' => 'expense', 'description' => 'Pago de proveedores', 'type' => 'fixed'],
            ['class' => 'expense', 'description' => 'Pago de servicios', 'type' => 'fixed'],
            ['class' => 'expense', 'description' => 'Pago de nómina', 'type' => 'fixed'],
            ['class' => 'expense', 'description' => 'Varios', 'type' => 'variable'],
        ];

        foreach ($data as $item) {
            MovementType::create($item);
        }
    }
}