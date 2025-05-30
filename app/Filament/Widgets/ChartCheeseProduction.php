<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\CheeseProduction;
use Illuminate\Support\Facades\DB;

class ChartCheeseProduction extends ChartWidget
{
    protected static ?string $heading = 'Producción de Queso';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $startDate = now()->subDays(6)->toDateString();
        $endDate = now()->toDateString();

        $data = CheeseProduction::select('branches.name as branch_name', DB::raw('SUM(cheese_productions.produced_kilos) as total_kilos'))
            ->join('branches', 'cheese_productions.branch_id', '=', 'branches.id')
            ->whereBetween('cheese_productions.date', [$startDate, $endDate])
            ->groupBy('cheese_productions.branch_id', 'branches.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Kilos producidos',
                    'data' => $data->pluck('total_kilos'),
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
        return 'Kilos producidos por sucursal en la última semana';
    }
}
