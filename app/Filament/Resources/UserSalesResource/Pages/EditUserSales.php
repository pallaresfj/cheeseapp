<?php

namespace App\Filament\Resources\UserSalesResource\Pages;

use App\Filament\Resources\UserSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserSales extends EditRecord
{
    protected static string $resource = UserSalesResource::class;

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
