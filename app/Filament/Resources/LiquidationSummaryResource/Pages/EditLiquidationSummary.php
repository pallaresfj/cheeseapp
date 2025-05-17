<?php

namespace App\Filament\Resources\LiquidationSummaryResource\Pages;

use App\Filament\Resources\LiquidationSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLiquidationSummary extends EditRecord
{
    protected static string $resource = LiquidationSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
