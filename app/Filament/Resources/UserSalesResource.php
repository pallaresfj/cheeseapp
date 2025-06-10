<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSalesResource\Pages;
use App\Filament\Resources\UserSalesResource\RelationManagers;
use App\Models\UserAccount;
use App\Models\UserSales;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserSalesResource extends Resource
{
    protected static ?string $model = UserAccount::class;

    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-m-chevron-right';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Venta';
    protected static ?string $pluralLabel = 'Ventas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->disabled()
                            ->columnSpan(6),

                        Forms\Components\TextInput::make('balance')
                            ->label('Saldo')
                            ->formatStateUsing(fn ($record) =>
                                number_format($record->sales()->sum('amount_paid') - $record->salePayments()->sum('amount'), 0, ',', '.')
                            )
                            ->prefix('$ ')
                            ->disabled()
                            ->columnSpan(4),

                        Forms\Components\TextInput::make('estado')
                            ->label('Estado')
                            ->formatStateUsing(fn ($record) =>
                                ($record->sales()->sum('amount_paid') - $record->salePayments()->sum('amount')) > 0
                                    ? 'Pendiente'
                                    : 'Al día'
                            )
                            ->disabled()
                            ->columnSpan(2),
                    ]),
                Forms\Components\TextInput::make('total_sales')
                    ->label('Ventas')
                    ->formatStateUsing(fn ($record) =>
                        number_format($record->sales()->sum('amount_paid'), 0, ',', '.')
                    )
                    ->prefix('$ ')
                    ->disabled(),

                Forms\Components\TextInput::make('total_payments')
                    ->label('Pagos')
                    ->formatStateUsing(fn ($record) =>
                        number_format($record->salePayments()->sum('amount'), 0, ',', '.')
                    )
                    ->prefix('$ ')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extremePaginationLinks()
            ->striped()
            ->defaultSort('name', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Ventas')
                    ->getStateUsing(fn ($record) => 
                        $record->sales()->sum('amount_paid')
                    )
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total_payments')
                    ->label('Pagos')
                    ->getStateUsing(fn ($record) => 
                        $record->salePayments()->sum('amount')
                    )
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->getStateUsing(fn ($record) =>
                        $record->sales()->sum('amount_paid') - $record->salePayments()->sum('amount')
                    )
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('estado')
                    ->label('')
                    ->getStateUsing(fn ($record) =>
                        ($record->sales()->sum('amount_paid') - $record->salePayments()->sum('amount')) > 0
                            ? 'Pendiente'
                            : 'Al día'
                    )
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'warning',
                        'Al día' => 'success',
                    })
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->tooltip('Editar')
                    ->iconSize('h-6 w-6'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SalesRelationManager::class,
            RelationManagers\SalePaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserSales::route('/'),
            'edit' => Pages\EditUserSales::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'customer')
            ->where('status', true);
    }
}
