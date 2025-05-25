<?php

namespace App\Filament\Resources\MovementResource\Pages;

use App\Filament\Resources\MovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;

class ListMovements extends ListRecords
{
    protected static string $resource = MovementResource::class;

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
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending')),

            'Conciliados' => Tab::make()
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'reconciled')),
        ];
    }
}
