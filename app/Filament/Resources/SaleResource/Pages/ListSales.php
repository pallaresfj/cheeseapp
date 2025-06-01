<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Filament\Resources\SaleResource\Widgets\ChartSale;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\ChartCheeseProduction;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ChartCheeseProduction::class,
            ChartSale::class,
        ];
    }
}
