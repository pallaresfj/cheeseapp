<?php

namespace App\Filament\Resources\MilkPurchaseResource\Pages;

use Illuminate\Database\QueryException;
use Filament\Notifications\Notification;
use App\Filament\Resources\MilkPurchaseResource;
use Filament\Actions;
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

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $record = parent::handleRecordCreation($data);
            Session::put('last_milk_purchase_date', $record->date);
            return $record;
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                Notification::make()
                    ->title('Registro duplicado')
                    ->body('Ya existe una compra registrada para esta finca en esa fecha.')
                    ->danger()
                    ->send();

                return $this->getModel()::query()->firstOrNew([]);
            }

            throw $e;
        }
    }
}
