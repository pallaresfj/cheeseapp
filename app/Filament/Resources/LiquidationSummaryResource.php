<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiquidationSummaryResource\Pages;
use App\Filament\Resources\LiquidationSummaryResource\RelationManagers;
use App\Models\LiquidationSummary;
use Carbon\Carbon;
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
use Illuminate\Support\HtmlString;

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
            ->actions([
                Tables\Actions\Action::make('ver_detalles')
                    ->label('')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('info')
                    ->tooltip('Detalles')
                    ->iconSize('h-6 w-6')
                    ->modalHeading(fn (LiquidationSummary $record) => $record->farm_display)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth('md')
                    ->modalContent(fn (LiquidationSummary $record) =>
                        new HtmlString(
                            '<div class="overflow-x-auto text-sm w-full">
                                <table class="table-auto w-full border border-gray-300">
                                    <thead class="bg-gray-100 text-left">
                                        <tr>
                                            <th class="px-4 py-2 border-b">Fecha</th>
                                            <th class="px-4 py-2 border-b">Litros</th>
                                        </tr>
                                    </thead>
                                    <tbody>' .
                                    collect($record->details)->map(fn ($item) =>
                                        '<tr>
                                            <td class="px-4 py-1 border-b">' . e(\Carbon\Carbon::parse($item['date'] ?? '')->format('d/m/Y')) . '</td>
                                            <td class="px-4 py-1 border-b text-right">' . e(number_format($item['liters'] ?? 0, 2, ',', '.')) . '</td>
                                        </tr>'
                                    )->implode('') .
                                '</tbody>
                                </table>
                            </div>'
                        )
                    ),
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
