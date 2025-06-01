<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartSale extends ChartWidget
{
    protected static ?string $heading = 'Ventas por Sucursal';
    protected static ?int $sort = 2;
    protected string $leyendaFechas = '';

    protected function getData(): array
    {
        $endDate = Sale::latest('sale_date')->value('sale_date');
        $startDate = Carbon::parse($endDate)->subDays(6)->startOfDay();
        $this->leyendaFechas = 'Ventas por sucursal de '.$startDate->format('d/m/Y') . ' a ' . Carbon::parse($endDate)->endOfDay()->format('d/m/Y');

        $data = Sale::select('branches.name as branch_name', DB::raw('SUM(amount_paid) as amount_paid'))
            ->join('branches', 'sales.branch_id', '=', 'branches.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->groupBy('sales.branch_id', 'branches.name')
            ->orderBy('branches.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Ventas por Sucursal',
                    'data' => $data->pluck('amount_paid'),
                    'backgroundColor' => 'rgba(192, 98, 75, 0.2)',
                    'borderColor' => 'rgb(192, 128, 75)',
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
}
