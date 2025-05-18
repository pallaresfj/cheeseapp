<?php

namespace App\Filament\Resources\LiquidationResource\Pages;

use App\Filament\Resources\LiquidationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLiquidation extends EditRecord
{
    protected static string $resource = LiquidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
