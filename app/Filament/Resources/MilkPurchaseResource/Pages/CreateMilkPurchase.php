<?php

namespace App\Filament\Resources\MilkPurchaseResource\Pages;

use Filament\Notifications\Notification;
use App\Filament\Resources\MilkPurchaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;

class CreateMilkPurchase extends CreateRecord
{
    protected static string $resource = MilkPurchaseResource::class;

    protected function getFormDefaults(): array
    {
        return [
            'date' => Session::get('last_milk_purchase_date', now()),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (\App\Models\MilkPurchase::where('farm_id', $data['farm_id'])
            ->where('date', $data['date'])
            ->exists()) {
            Notification::make()
                ->title('Registro duplicado')
                ->body('Ya existe una compra registrada para esta finca en esa fecha.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}