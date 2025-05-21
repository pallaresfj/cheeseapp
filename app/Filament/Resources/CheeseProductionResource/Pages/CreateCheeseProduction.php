<?php

namespace App\Filament\Resources\CheeseProductionResource\Pages;

use App\Filament\Resources\CheeseProductionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;
use Filament\Notifications\Notification;

class CreateCheeseProduction extends CreateRecord
{
    protected static string $resource = CheeseProductionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) { // Código de error para duplicado
                Notification::make()
                    ->title('Ya existe un registro')
                    ->body('Ya hay una producción de queso registrada para esa fecha y sucursal.')
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt(); // Detiene la ejecución de la acción
            }

            throw $e;
        }
    }
}
