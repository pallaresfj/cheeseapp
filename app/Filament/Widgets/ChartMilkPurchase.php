<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Branch;
use App\Models\MilkPurchase;
use Illuminate\Support\Facades\DB;

class ChartMilkPurchase extends ChartWidget
{
    protected static ?string $heading = 'Compra de Leche';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $startDate = now()->subDays(6)->toDateString();
        $endDate = now()->toDateString();

        $data = MilkPurchase::select('branches.name as branch_name', DB::raw('SUM(milk_purchases.liters) as total_liters'))
            ->join('branches', 'milk_purchases.branch_id', '=', 'branches.id')
            ->whereBetween('milk_purchases.date', [$startDate, $endDate])
            ->groupBy('milk_purchases.branch_id', 'branches.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Litros comprados',
                    'data' => $data->pluck('total_liters'),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('branch_name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    public function getDescription(): ?string
    {
        return 'Litros comprados por sucursal en la última semana';
    }
}
