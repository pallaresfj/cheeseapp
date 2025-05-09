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
            UsersTableSeeder::class,
            BranchesTableSeeder::class,
            FarmTypesTableSeeder::class,
            FarmsTableSeeder::class,
            MilkPurchasesTableSeeder::class,
        ]);
        
         // User::factory(10)->create();
        /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */
    }
}
