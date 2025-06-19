<?php

namespace App\Filament\Resources\MilkPurchaseResource\Pages;

use App\Filament\Resources\MilkPurchaseResource;
use App\Imports\MyMilkPurchaseImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use EightyNine\ExcelImport\ExcelImportAction;


class ListMilkPurchases extends ListRecords
{
    protected static string $resource = MilkPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExcelImportAction::make()
                ->label('Importar')
                ->icon('heroicon-o-cloud-arrow-up')
                ->modalHeading('Importar Compras')
                ->modalSubmitActionLabel('Importar')
                ->modalDescription('Se importarán las compras desde un archivo Excel.')
                ->validateUsing([
                    'fecha' =>  ['required', 'date'],
                    'sucursal' =>  ['required', 'exists:branches,id'],
                    'finca' =>  ['required', 'exists:farms,id'],
                    'litros' =>  ['required', 'numeric', 'gte:0'],
                    'status' =>  ['required', 'in:pending,liquidated'],
                ])
                ->use(MyMilkPurchaseImport::class),
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
