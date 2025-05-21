<?php

namespace App\Filament\Resources\CheeseProductionResource\Pages;

use App\Filament\Resources\CheeseProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\QueryException;
use Filament\Notifications\Notification;

class EditCheeseProduction extends EditRecord
{
    protected static string $resource = CheeseProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
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
