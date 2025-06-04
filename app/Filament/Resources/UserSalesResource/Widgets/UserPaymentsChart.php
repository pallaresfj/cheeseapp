<?php

namespace App\Filament\Resources\UserSalesResource\Widgets;

use App\Models\SalePayment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserPaymentsChart extends ChartWidget
{
    protected static ?string $heading = 'Pagos por fechas';
    protected static ?int $sort = 2;
    protected string $leyendaFechas = '';

    protected function getData(): array
    {
        $endDate = SalePayment::latest('date')->value('date');
        $startDate = Carbon::parse($endDate)->subDays(6)->startOfDay();
        $this->leyendaFechas = 'Pagos de '.$startDate->format('d/m/Y') . ' a ' . Carbon::parse($endDate)->endOfDay()->format('d/m/Y');

        $data = SalePayment::select(DB::raw('DATE(date) as payment_date'), DB::raw('SUM(amount) as amount'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('payment_date')
            ->orderBy('payment_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Pagos por fechas',
                    'data' => $data->pluck('amount'),
                    'backgroundColor' => 'rgba(75, 192, 89, 0.2)',
                    'borderColor' => 'rgb(93, 192, 75)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('payment_date')->map(fn ($date) => Carbon::parse($date)->format('d/m')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
