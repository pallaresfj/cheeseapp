<?php

namespace App\Filament\Resources\CustomerClassificationResource\Pages;

use App\Filament\Resources\CustomerClassificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerClassifications extends ListRecords
{
    protected static string $resource = CustomerClassificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
