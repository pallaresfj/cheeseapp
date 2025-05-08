<?php

namespace App\Filament\Resources\MilkPurchaseResource\Pages;

use App\Filament\Resources\MilkPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMilkPurchases extends ListRecords
{
    protected static string $resource = MilkPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
