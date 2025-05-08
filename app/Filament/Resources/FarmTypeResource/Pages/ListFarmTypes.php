<?php

namespace App\Filament\Resources\FarmTypeResource\Pages;

use App\Filament\Resources\FarmTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFarmTypes extends ListRecords
{
    protected static string $resource = FarmTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
