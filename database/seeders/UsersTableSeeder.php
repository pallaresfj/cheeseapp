<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Soporte
        User::create([
            'name' => 'Usuario Soporte',
            'email' => 'soporte@cheeseapp.com.co',
            'password' => Hash::make('7052'),
            'role' => 'soporte',
        ]);

        // Usuario Admin
        User::create([
            'name' => 'Usuario Administrador',
            'email' => 'admin@cheeseapp.com.co',
            'password' => Hash::make('7052'),
            'role' => 'admin',
        ]);

        // Usuario Sucursal
        User::create([
            'name' => 'Usuario Sucursal',
            'email' => 'sucursal@cheeseapp.com.co',
            'password' => Hash::make('7052'),
            'role' => 'sucursal',
        ]);

        // Usuarios Proveedores
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Proveedor $i",
                'email' => "proveedor$i@cheeseapp.com.co",
                'password' => Hash::make('1234'),
                'role' => 'supplier',
            ]);
        }

        // Usuarios Clientes
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "Cliente $i",
                'email' => "cliente$i@cheeseapp.com.co",
                'password' => Hash::make('1234'),
                'role' => 'customer',
            ]);
        }
    }
}
