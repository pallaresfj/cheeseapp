<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiquidationSummaryResource\Pages;
use App\Filament\Resources\LiquidationSummaryResource\RelationManagers;
use App\Models\LiquidationSummary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LiquidationSummaryResource extends Resource
{
    protected static ?string $model = LiquidationSummary::class;

    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?string $label = 'Liquidación';
    protected static ?string $pluralLabel = 'Liquidaciones';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal'),
                Tables\Columns\TextColumn::make('farm.name')
                    ->label('Proveedor - Finca'),
                Tables\Columns\TextColumn::make('total_liters')
                    ->label('Litros')
                    ->numeric()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('price_per_liter')
                    ->label('Precio')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Producido')
                    ->money('COP')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('loan_amount')
                    ->label('Prestado')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('loan_balance')
                    ->label('Debe')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('installment_value')
                    ->label('Cuota')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Descuento')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Neto')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
            ])
            ->filters([]);
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
            'index' => Pages\ListLiquidationSummaries::route('/'),
        ];
    }
}
