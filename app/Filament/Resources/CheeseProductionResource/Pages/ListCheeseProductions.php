<?php

namespace App\Filament\Resources\CheeseProductionResource\Pages;

use App\Filament\Resources\CheeseProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCheeseProductions extends ListRecords
{
    protected static string $resource = CheeseProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
