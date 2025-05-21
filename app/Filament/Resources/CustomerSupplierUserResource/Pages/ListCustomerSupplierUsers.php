<?php

namespace App\Filament\Resources\CustomerSupplierUserResource\Pages;

use App\Filament\Resources\CustomerSupplierUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerSupplierUsers extends ListRecords
{
    protected static string $resource = CustomerSupplierUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
