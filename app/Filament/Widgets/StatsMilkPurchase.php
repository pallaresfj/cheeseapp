<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\MilkPurchase;
use App\Models\CheeseProduction;
use App\Models\Setting;
use Carbon\Carbon;

class StatsMilkPurchase extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalLitros = MilkPurchase::whereBetween('date', [
            Carbon::now()->subDays(6)->startOfDay(),
            Carbon::now()->endOfDay()
        ])->sum('liters');
        $totalKilos = CheeseProduction::whereBetween('date', [
            Carbon::now()->subDays(6)->startOfDay(),
            Carbon::now()->endOfDay()
        ])->sum('produced_kilos');
        $productividad = Setting::where('key', 'productividad')->value('value') ?? 1;
        $kilosEsperados = $totalLitros * $productividad;
        $porcentajeProduccion = $kilosEsperados > 0 ? ($totalKilos / $kilosEsperados) * 100 : 0;
        return [
            Stat::make('Litros recibidos', number_format($totalLitros, 1))
                ->description('Últimos 7 días')
                ->color('success')
                ->icon('heroicon-o-arrow-down-on-square'),
            Stat::make('Kilos producidos', number_format($totalKilos, 1))
                ->description('Últimos 7 días')
                ->color('success')
                ->icon('heroicon-o-truck'),
            Stat::make('% Producción', number_format($porcentajeProduccion, 1) . '%')
                ->description('Últimos 7 días')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }
}
