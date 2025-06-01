<?php

namespace App\Filament\Resources\MovementResource\Widgets;

use App\Models\Liquidation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartLiquidationP extends ChartWidget
{
    protected static ?string $heading = 'Pagos a Proveedores';
    protected static ?int $sort = 1;
    protected string $leyendaFechas = '';

    protected function getData(): array
    {
        $endDate = Liquidation::latest('date')->value('date');
        $startDate = Carbon::parse($endDate)->subDays(6)->startOfDay();
        $this->leyendaFechas = 'Pagos por sucursal de '.$startDate->format('d/m/Y') . ' a ' . Carbon::parse($endDate)->endOfDay()->format('d/m/Y');

        $data = Liquidation::select('branches.name as branch_name', DB::raw('SUM(net_total) as net_total'))
            ->join('branches', 'liquidations.branch_id', '=', 'branches.id')
            ->whereBetween('liquidations.date', [$startDate, $endDate])
            ->groupBy('liquidations.branch_id', 'branches.name')
            ->orderBy('branches.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pagos por Sucursal',
                    'data' => $data->pluck('net_total'),
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
