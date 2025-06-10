<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\MilkPurchase;
use Illuminate\Database\Seeder;

class MilkPurchasesTableSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = now()->setDate(2025, 5, 31);
        $endDate = now()->setDate(2025, 6, 6);
        $dateRange = collect();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateRange->push($date->copy());
        }

        $farms = Farm::with('branch')->where('status', true)->whereHas('branch', function ($query) {
            $query->where('active', true);
        })->get();

        foreach ($farms as $farm) {
            foreach ($dateRange as $date) {
                MilkPurchase::create([
                    'branch_id' => $farm->branch_id,
                    'farm_id' => $farm->id,
                    'date' => $date->format('Y-m-d'),
                    'liters' => fake()->numberBetween(5, 100),
                    'status' => 'pending',
                ]);
            }
        }
    }
}