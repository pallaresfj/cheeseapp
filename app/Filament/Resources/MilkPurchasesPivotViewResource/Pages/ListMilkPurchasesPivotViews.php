<?php

namespace App\Filament\Resources\MilkPurchasesPivotViewResource\Pages;

use App\Filament\Resources\MilkPurchasesPivotViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMilkPurchasesPivotViews extends ListRecords
{
    protected static string $resource = MilkPurchasesPivotViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
