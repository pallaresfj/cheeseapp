<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Loan;
use App\Models\Farm;
use Illuminate\Support\Carbon;

class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $farms = Farm::whereHas('branch', fn ($q) => $q->where('active', true))->get();

        if ($farms->isEmpty()) {
            $this->command->warn('No hay fincas asociadas a sucursales activas.');
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            $farm = $farms->random();
            $user_id = $farm->user_id;

            // Omitir si el usuario ya tiene un préstamo activo
            if (Loan::where('user_id', $user_id)->where('status', 'active')->exists()) {
                continue;
            }

            do {
                $amount = rand(400, 5000) * 100;
                $installments = rand(1, 12);
                $installment_value = $amount / $installments;
            } while (!is_int($installment_value) || $installment_value % 100 !== 0);

            Loan::create([
                'user_id' => $user_id,
                'farm_id' => $farm->id,
                'date' => Carbon::create(2025, 5, rand(17, 23)),
                'amount' => $amount,
                'installments' => $installments,
                'installment_value' => $installment_value,
                'paid_value' => 0,
                'description' => fake()->sentence(),
                'status' => 'active',
            ]);
        }
    }
}
