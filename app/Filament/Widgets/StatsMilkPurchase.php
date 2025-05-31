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
        $lastDate = MilkPurchase::latest('date')->value('date');
        $startDate = Carbon::parse($lastDate)->subDays(6)->startOfDay();
        $leyendaFechas = $startDate->format('d/m/Y') . ' - ' . Carbon::parse($lastDate)->endOfDay()->format('d/m/Y');
        $totalLitros = MilkPurchase::whereBetween('date', [
            $startDate,
            Carbon::parse($lastDate)->endOfDay(),
        ])->sum('liters');
        $totalKilos = CheeseProduction::whereBetween('date', [
            $startDate,
            Carbon::parse($lastDate)->endOfDay(),
        ])->sum('produced_kilos');
        $productividad = Setting::where('key', 'sistema.productividad')->value('value') ?? 1;
        $kilosEsperados = $totalLitros * $productividad;
        $porcentajeProduccion = $kilosEsperados > 0 ? ($totalKilos / $kilosEsperados) * 100 : 0;
        return [
            Stat::make('Litros recibidos', number_format($totalLitros, 1, ',', '.'))
                ->description($leyendaFechas)
                ->color('success')
                ->icon('heroicon-o-arrow-down-on-square'),
            Stat::make('Kilos producidos', number_format($totalKilos, 1, ',', '.'))
                ->description($leyendaFechas)
                ->color('success')
                ->icon('heroicon-o-truck'),
            Stat::make('% Producción', number_format($porcentajeProduccion, 1, ',', '.') . '%')
                ->description($leyendaFechas)
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }
}
