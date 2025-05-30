<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestSales extends BaseWidget
{
    protected static ?string $heading = 'Últimas Ventas';
    protected static ?int $sort = 4;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->latest('sale_date')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Fecha')
                    ->date(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('kilos')
                    ->label('Kilos')
                    ->numeric()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Total')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
            ]);
    }
}
