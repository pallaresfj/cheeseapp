<?php

namespace App\Filament\Resources\CustomerSupplierUserResource\Pages;

use App\Filament\Resources\CustomerSupplierUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerSupplierUser extends EditRecord
{
    protected static string $resource = CustomerSupplierUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $this->redirect($this->getResource()::getUrl('index'));
    }
}
