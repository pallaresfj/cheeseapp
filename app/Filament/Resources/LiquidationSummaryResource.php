<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiquidationSummaryResource\Pages;
use App\Filament\Resources\LiquidationSummaryResource\RelationManagers;
use App\Models\LiquidationSummary;
use Filament\Forms;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('farm_display')
                    ->label('Proveedor - Finca'),
                TextColumn::make('total_liters')
                    ->label('Litros')
                    ->numeric()
                    ->alignEnd(),
                TextColumn::make('price_per_liter')
                    ->label('Precio')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                TextColumn::make('total_paid')
                    ->label('Producido')
                    ->money('COP')
                    ->alignEnd(),
                TextColumn::make('loan_amount')
                    ->label('Prestado')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                TextColumn::make('loan_balance')
                    ->label('Debe')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                TextColumn::make('installment_value')
                    ->label('Cuota')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                TextColumn::make('discount')
                    ->label('Descuento')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                TextColumn::make('new_balance')
                    ->label('Nuevo saldo')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                TextColumn::make('net_amount')
                    ->label('Neto')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
            ])
            ->groups([
                Tables\Grouping\Group::make('date')
                    ->label('Fecha')
                    ->collapsible()
            ])
            ->defaultGroup('date')
            ->groupingSettingsHidden()
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('farm_id')
                    ->label('Proveedor - Finca')
                    ->options(function () {
                        return \App\Models\Farm::with('user')->get()->mapWithKeys(function ($farm) {
                            $label = ($farm->user->name ?? '—') . ' - ' . $farm->name;
                            return [$farm->id => $label];
                        });
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->persistFiltersInSession();
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
