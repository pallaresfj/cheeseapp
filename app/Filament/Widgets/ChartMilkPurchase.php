<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Branch;
use App\Models\MilkPurchase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartMilkPurchase extends ChartWidget
{
    protected static ?string $heading = 'Compra de Leche';
    protected static ?int $sort = 2;
    protected string $leyendaFechas = '';

    protected function getData(): array
    {
        $endDate = MilkPurchase::latest('date')->value('date');
        $startDate = Carbon::parse($endDate)->subDays(6)->startOfDay();
        $this->leyendaFechas = 'Litros comprados por sucursal de '.$startDate->format('d/m/Y') . ' a ' . Carbon::parse($endDate)->endOfDay()->format('d/m/Y');

        $data = MilkPurchase::select('branches.name as branch_name', DB::raw('SUM(milk_purchases.liters) as total_liters'))
            ->join('branches', 'milk_purchases.branch_id', '=', 'branches.id')
            ->whereBetween('milk_purchases.date', [$startDate, $endDate])
            ->groupBy('milk_purchases.branch_id', 'branches.name')
            ->orderBy('branches.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Litros por Sucursal',
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
        return $this->leyendaFechas;
    }
}
