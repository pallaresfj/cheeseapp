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
        $user = User::create([
            'name' => 'Usuario Soporte',
            'email' => 'soporte@cheeseapp.com.co',
            'password' => Hash::make('7052'),
            'role' => 'soporte',
        ]);
        $user->assignRole('soporte');

        // Usuario Admin
        $user = User::create([
            'name' => 'Usuario Administrador',
            'email' => 'admin@cheeseapp.com.co',
            'password' => Hash::make('7052'),
            'role' => 'admin',
        ]);
        $user->assignRole('Administrador');

        // Usuario Sucursal
        $user = User::create([
            'name' => 'Usuario Sucursal',
            'email' => 'sucursal@cheeseapp.com.co',
            'password' => Hash::make('7052'),
            'role' => 'sucursal',
        ]);
        $user->assignRole('Sucursal');

        // Usuarios Proveedores
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Nombre Apellidos Proveedor $i",
                'email' => "proveedor$i@cheeseapp.com.co",
                'password' => Hash::make('1234'),
                'role' => 'supplier',
            ]);
            $user->assignRole('Proveedor');
        }

        // Usuarios Clientes
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => "Nombre Apellidos Cliente $i",
                'email' => "cliente$i@cheeseapp.com.co",
                'password' => Hash::make('1234'),
                'role' => 'customer',
            ]);
            $user->assignRole('Cliente');
        }
    }
}
