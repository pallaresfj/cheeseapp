<?php

namespace App\Filament\Resources\CheeseProductionResource\Pages;

use App\Filament\Resources\CheeseProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheeseProduction extends EditRecord
{
    protected static string $resource = CheeseProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
