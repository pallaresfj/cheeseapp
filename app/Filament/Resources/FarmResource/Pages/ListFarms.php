<?php

namespace App\Filament\Resources\FarmResource\Pages;

use App\Filament\Resources\FarmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use EightyNine\ExcelImport\ExcelImportAction;
use Illuminate\Validation\Rule;
use App\Imports\MyFarmImport;

class ListFarms extends ListRecords
{
    protected static string $resource = FarmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExcelImportAction::make()
                ->label('Importar')
                ->icon('heroicon-o-cloud-arrow-up')
                ->modalHeading('Importar Fincas')
                ->modalSubmitActionLabel('Importar')
                ->modalDescription('Se importarán las fincas desde un archivo Excel.')
                ->validateUsing([
                    'proveedor' =>  ['required', 'exists:users,username'],
                    'finca' => ['required'],
                    'sucursal' =>  ['required', 'exists:branches,name'],
                    'tipo' =>  ['required', 'exists:farm_types,name'],
                ])
                ->use(MyFarmImport::class),
        ];
    }
    protected function getTableActions(): array
    {
        return [
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
