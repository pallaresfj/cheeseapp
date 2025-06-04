<?php

namespace App\Filament\Resources\MovementResource\Pages;

use App\Filament\Resources\MovementResource;
use App\Filament\Resources\MovementResource\Widgets\ChartLiquidationD;
use App\Filament\Resources\MovementResource\Widgets\ChartLiquidationP;
use App\Filament\Resources\SaleResource\Widgets\ChartSale;
use App\Filament\Resources\UserSalesResource\Widgets\UserSalesChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListMovements extends ListRecords
{
    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ChartLiquidationP::class,
            UserSalesChart::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'Pendientes' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending')),

            'Conciliados' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'reconciled')),
        ];
    }
}
