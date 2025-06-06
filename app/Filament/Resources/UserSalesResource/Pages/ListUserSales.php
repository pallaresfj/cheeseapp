<?php

namespace App\Filament\Resources\UserSalesResource\Pages;

use App\Filament\Resources\UserSalesResource;
use App\Filament\Resources\UserSalesResource\Widgets\UserPaymentsChart;
use App\Filament\Resources\UserSalesResource\Widgets\UserSalesChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserSales extends ListRecords
{
    protected static string $resource = UserSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            // UserSalesChart::class,
            UserPaymentsChart::class,
        ];
    }
}
