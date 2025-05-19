<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\MilkPurchase;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenerateMilkPurchasesPivotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchId = Branch::where('active', true)->value('id') ?? \App\Models\Branch::value('id');

        if (! $branchId) {
            return;
        }

        $startDate = MilkPurchase::where('branch_id', $branchId)
            ->where('status', 'pending')
            ->orderBy('date')
            ->value('date') ?? now()->toDateString();

        $ciclo = Setting::where('key', 'facturacion.ciclo')->value('value') ?? 7;

        DB::statement("CALL generate_milk_purchases_pivot_view($branchId, '$startDate', $ciclo)");
    }
}
