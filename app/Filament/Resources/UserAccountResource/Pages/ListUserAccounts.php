<?php

namespace App\Filament\Resources\UserAccountResource\Pages;

use App\Filament\Resources\UserAccountResource;
use App\Models\UserAccount;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Validation\Rule;
use App\Imports\MyUserAccountImport;
use Filament\Forms\Components\Actions\Action;

class ListUserAccounts extends ListRecords
{
    protected static string $resource = UserAccountResource::class;

    public function getTabs(): array
    {
        return [
            'supplier' => Tab::make('Proveedores')
                ->icon('heroicon-m-user-group')
                ->modifyQueryUsing(fn ($query) => $query->where('role', 'supplier'))
                ->badge(UserAccount::query()->where('role', 'supplier')->where('status', true)->count()),
            'customer' => Tab::make('Clientes')
                ->icon('heroicon-m-users')
                ->modifyQueryUsing(fn ($query) => $query->where('role', 'customer'))
                ->badge(UserAccount::query()->where('role', 'customer')->where('status', true)->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExcelImportAction::make()
                ->label('Importar')
                ->icon('heroicon-o-cloud-arrow-up')
                ->modalHeading('Importar Proveedores/Clientes')
                ->modalSubmitActionLabel('Importar')
                ->modalDescription('Se importarán los datos de los proveedores o clientes desde un archivo Excel.')
                ->validateUsing([
                    'nombre' =>  ['required'],
                    'correo' => 'required|email',
                    'rol' => [
                                'required',
                                Rule::in(['supplier', 'customer']),
                              ],
                    'usuario' =>  ['required', 'unique:users,username'],
                ])
                ->use(MyUserAccountImport::class),
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
