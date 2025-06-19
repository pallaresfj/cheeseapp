<?php

namespace App\Filament\Resources\MilkPurchaseResource\Pages;

use App\Filament\Resources\MilkPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListMilkPurchases extends ListRecords
{
    protected static string $resource = MilkPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'Pendientes' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('milk_purchases.status', 'pending')),

            'Liquidadas' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('milk_purchases.status', 'liquidated')),
        ];
    }
}
