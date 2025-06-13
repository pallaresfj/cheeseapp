<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\CheeseProduction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartCheeseProduction extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Producción de Queso';
    protected static ?int $sort = 3;
    protected string $leyendaFechas = '';

    protected function getData(): array
    {
        $endDate = CheeseProduction::latest('date')->value('date');
        $startDate = Carbon::parse($endDate)->subDays(6)->startOfDay();
        $this->leyendaFechas = 'Kilos producidos por sucursal de '.$startDate->format('d/m/Y') . ' a ' . Carbon::parse($endDate)->endOfDay()->format('d/m/Y');

        $data = CheeseProduction::select('branches.name as branch_name', DB::raw('SUM(cheese_productions.produced_kilos) as total_kilos'))
            ->join('branches', 'cheese_productions.branch_id', '=', 'branches.id')
            ->whereBetween('cheese_productions.date', [$startDate, $endDate])
            ->groupBy('cheese_productions.branch_id', 'branches.name')
            ->orderBy('branches.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Kilos por Sucursal',
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
        return $this->leyendaFechas;
    }
}
