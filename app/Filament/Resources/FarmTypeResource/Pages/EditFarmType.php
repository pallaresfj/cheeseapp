<?php

namespace App\Filament\Resources\FarmTypeResource\Pages;

use App\Filament\Resources\FarmTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFarmType extends EditRecord
{
    protected static string $resource = FarmTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
