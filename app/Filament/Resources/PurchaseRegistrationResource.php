<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseRegistrationResource\Pages;
use App\Filament\Resources\PurchaseRegistrationResource\RelationManagers;
use App\Models\PurchaseRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseRegistrationResource extends Resource
{
    protected static ?string $model = PurchaseRegistration::class;
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Registro de Compras';
    protected static ?string $pluralLabel = 'Registro de Compras';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('farm.name')
                    ->label('Proveedor - Finca')
                    ->formatStateUsing(fn ($state, $record) => "{$record->farm->user->name} - {$record->farm->name}"),
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date(),
                TextInputColumn::make('liters')
                    ->label('Litros'),
            ])
            ->groups([
                Group::make('branch.name')
                    ->label('Sucursal')
                    ->collapsible()
            ])
            ->defaultGroup('branch.name')
            ->groupingSettingsHidden()
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('transferirCompras')
                    ->label('Guardar Compras')
                    ->icon('heroicon-o-folder-plus')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Guardar Compras')
                    ->modalDescription('¿Está seguro de guardar las compras actuales?')
                    ->modalSubmitActionLabel('Guardar')
                    ->action(fn () => DB::statement('CALL sp_transferir_compras(?)', [Auth::id()]))
                    ->after(function () {
                        Notification::make()
                            ->title('Compras guardadas')
                            ->body('Se guardaron todas las compras correctamente.')
                            ->success()
                            ->send();

                        return redirect(MilkPurchasesPivotViewResource::getUrl());
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseRegistrations::route('/'),
        ];
    }
}
