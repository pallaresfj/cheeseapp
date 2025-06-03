<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSalesBalanceResource\Pages;
use App\Filament\Resources\UserSalesBalanceResource\RelationManagers;
use App\Models\UserSalesBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserSalesBalanceResource extends Resource
{
    protected static ?string $model = UserSalesBalance::class;

    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Balance de Ventas';
    protected static ?string $pluralLabel = 'Balance de Ventas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Cliente')
                            ->columnSpan(6),

                        Forms\Components\TextInput::make('balance')
                            ->label('Saldo')
                            ->prefix('$ ')
                            ->columnSpan(4),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'up_to_date' => 'Al día',
                            ])
                            ->columnSpan(2),
                    ]),

                Forms\Components\TextInput::make('total_sales')
                    ->label('Ventas')
                    ->prefix('$ '),

                Forms\Components\TextInput::make('total_payments')
                    ->label('Pagos')
                    ->prefix('$ '),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('status')
                    ->label('')
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'up_to_date' => 'heroicon-o-check-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'up_to_date' => 'success',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'up_to_date' => 'Al día',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->weight(FontWeight::Bold)
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Ventas')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total_payments')
                    ->label('Pagos')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->weight(FontWeight::Bold)
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
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
            'index' => Pages\ListUserSalesBalances::route('/'),
            'view' => Pages\ViewUserSalesBalance::route('/{record}/view'),
        ];
    }
}
