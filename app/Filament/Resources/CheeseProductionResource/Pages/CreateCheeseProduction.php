<?php

namespace App\Filament\Resources\CheeseProductionResource\Pages;

use App\Filament\Resources\CheeseProductionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCheeseProduction extends CreateRecord
{
    protected static string $resource = CheeseProductionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

