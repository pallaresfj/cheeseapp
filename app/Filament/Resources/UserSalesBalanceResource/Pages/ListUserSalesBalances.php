<?php

namespace App\Filament\Resources\UserSalesBalanceResource\Pages;

use App\Filament\Resources\UserSalesBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserSalesBalances extends ListRecords
{
    protected static string $resource = UserSalesBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
