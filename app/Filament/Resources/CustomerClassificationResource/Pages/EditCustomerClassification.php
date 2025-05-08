<?php

namespace App\Filament\Resources\CustomerClassificationResource\Pages;

use App\Filament\Resources\CustomerClassificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerClassification extends EditRecord
{
    protected static string $resource = CustomerClassificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
