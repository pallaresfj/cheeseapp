<?php

namespace App\Filament\Resources\MilkPurchaseResource\Pages;

use App\Filament\Resources\MilkPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMilkPurchase extends EditRecord
{
    protected static string $resource = MilkPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
