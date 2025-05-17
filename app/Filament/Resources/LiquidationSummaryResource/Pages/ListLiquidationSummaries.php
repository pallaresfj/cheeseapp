<?php

namespace App\Filament\Resources\LiquidationSummaryResource\Pages;

use App\Filament\Resources\LiquidationSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiquidationSummaries extends ListRecords
{
    protected static string $resource = LiquidationSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
