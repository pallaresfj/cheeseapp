<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'empresa.nombre', 'category' => 'empresa', 'value' => 'QUESERA SOCIOS'],
            ['key' => 'empresa.direccion', 'category' => 'empresa', 'value' => 'Calle 5 3 04, Sabanas Magdalena'],
            ['key' => 'empresa.telefono', 'category' => 'empresa', 'value' => '3145028741'],
            ['key' => 'empresa.nit', 'category' => 'empresa', 'value' => '19640365-8'],
            ['key' => 'empresa.logo', 'category' => 'empresa', 'value' => 'logo-empresa.png'],
            ['key' => 'empresa.slogan', 'category' => 'empresa', 'value' => 'El mejor queso de la región'],
            ['key' => 'empresa.web', 'category' => 'empresa', 'value' => 'https://qsocios.cheeseapp.com.co'],
            ['key' => 'empresa.correo', 'category' => 'empresa', 'value' => 'queserasocios@outlook.com'],
            ['key' => 'facturacion.ciclo', 'category' => 'facturacion', 'value' => '7'],
            ['key' => 'sistema.productividad', 'category' => 'sistema', 'value' => '0.125'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'category' => $setting['category'],
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }
    }
}
