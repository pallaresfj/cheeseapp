<?php

namespace App\Filament\Resources\FarmResource\Pages;

use App\Filament\Resources\FarmResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFarm extends EditRecord
{
    protected static string $resource = FarmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $this->redirect($this->getResource()::getUrl('index'));
    }
}
