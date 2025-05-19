<?php

namespace App\Filament\Resources\PurchaseRegistrationResource\Pages;

use App\Filament\Resources\PurchaseRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseRegistration extends EditRecord
{
    protected static string $resource = PurchaseRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
