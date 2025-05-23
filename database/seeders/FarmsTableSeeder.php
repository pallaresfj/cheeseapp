<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\User;
use App\Models\Branch;
use App\Models\FarmType;
use Illuminate\Database\Seeder;

class FarmsTableSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::pluck('id')->all();
        $farmTypes = FarmType::pluck('id')->all();

        // Solo seleccionamos usuarios con rol supplier
        $suppliers = \App\Models\User::where('role', 'supplier')->pluck('id')->all();

        $count = 0;

        foreach ($suppliers as $supplierId) {
            for ($i = 1; $i <= 2; $i++) {
                if ($count >= 20) break;

                Farm::create([
                    'name' => 'Finca ' . ($count + 1),
                    'branch_id' => fake()->randomElement($branches),
                    'user_id' => $supplierId,
                    'farm_type_id' => fake()->randomElement($farmTypes),
                    'location' => fake()->city . ', ' . fake()->state,
                    'status' => true,
                ]);

                $count++;
            }

            if ($count >= 20) break;
        }
    }
}
