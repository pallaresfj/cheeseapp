<?php

namespace App\Filament\Resources\MilkPurchaseResource\Pages;

use App\Filament\Resources\MilkPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMilkPurchase extends EditRecord
{
    protected static string $resource = MilkPurchaseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (\App\Models\MilkPurchase::where('farm_id', $data['farm_id'])
            ->where('date', $data['date'])
            ->where('id', '!=', $this->record->id)
            ->exists()) {
            Notification::make()
                ->title('Ya existe un registro con la misma finca y fecha.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->redirect($this->getResource()::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
