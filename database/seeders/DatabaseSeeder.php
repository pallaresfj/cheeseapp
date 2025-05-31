<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       
        $this->call([
            SettingsSeeder::class,
            FarmTypesTableSeeder::class,
            CustomerClassificationSeeder::class,
            MovementTypeSeeder::class,
            RoleSeeder::class,
            UsersTableSeeder::class, // Ingresar el admin
            BranchesTableSeeder::class,
            FarmsTableSeeder::class,
            MilkPurchasesTableSeeder::class, // Borrar este seeder en producción
            LoanSeeder::class,
        ]);
    }
}
