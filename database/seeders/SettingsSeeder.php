<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'empresa.nombre', 'category' => 'empresa', 'value' => 'QUESERA LA ESPERANZA'],
            ['key' => 'empresa.direccion', 'category' => 'empresa', 'value' => 'Calle 24 65 78, Los Hampton Escocia'],
            ['key' => 'empresa.telefono', 'category' => 'empresa', 'value' => '3458796543'],
            ['key' => 'empresa.nit', 'category' => 'empresa', 'value' => '987654321-0'],
            ['key' => 'empresa.logo', 'category' => 'empresa', 'value' => 'logo-empresa.png'],
            ['key' => 'empresa.slogan', 'category' => 'empresa', 'value' => 'La mejor leche, el mejor queso'],
            ['key' => 'empresa.web', 'category' => 'empresa', 'value' => 'https://queseraesperanza.com'],
            ['key' => 'empresa.correo', 'category' => 'empresa', 'value' => 'info@cqueseraesperanza.com'],
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
