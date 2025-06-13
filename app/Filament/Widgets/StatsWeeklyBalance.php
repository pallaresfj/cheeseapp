<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Setting;
use App\Models\WeeklyBalance;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;

class StatsWeeklyBalance extends BaseWidget
{
    use HasWidgetShield;
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        $lastDate = WeeklyBalance::latest('date')->value('date');
        $startDate = Carbon::parse($lastDate)->subDays(6)->startOfDay();
        $leyendaFechas = $startDate->format('d/m/Y') . ' - ' . Carbon::parse($lastDate)->endOfDay()->format('d/m/Y');
        $ingresos = WeeklyBalance::whereBetween('date', [
            $startDate,
            Carbon::parse($lastDate)->endOfDay(),
        ])->sum('incomes');
        $egresos = WeeklyBalance::whereBetween('date', [
            $startDate,
            Carbon::parse($lastDate)->endOfDay(),
        ])->sum('expenses');
        // $productividad = Setting::where('key', 'sistema.productividad')->value('value') ?? 1;
        // $kilosEsperados = $totalLitros * $productividad;
        $balance = $ingresos - $egresos;
        return [
            Stat::make('Ingresos', '$' . number_format($ingresos, 1, ',', '.'))
                ->description($leyendaFechas)
                ->color('success')
                ->icon('heroicon-o-arrow-down-on-square'),
            Stat::make('Egresos', '$' . number_format($egresos, 1, ',', '.'))
                ->description($leyendaFechas)
                ->color('success')
                ->icon('heroicon-o-truck'),
            Stat::make('Balance', '$' . number_format($balance, 1, ',', '.'))
                ->description($leyendaFechas)
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }
}
