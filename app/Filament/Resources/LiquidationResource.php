<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiquidationResource\Pages;
use App\Filament\Resources\LiquidationResource\RelationManagers;
use App\Models\Liquidation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Average;

class LiquidationResource extends Resource
{
    protected static ?string $model = Liquidation::class;

    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Liquidación Aprobada';
    protected static ?string $pluralLabel = 'Liquidaciones Aprobadas';


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'liquidated');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Liquidaciones listas para imprimir recibos.')
            ->columns([
                Tables\Columns\TextColumn::make('farm.name')
                    ->label('Proveedor - Finca')
                    ->formatStateUsing(fn ($record) => $record->farm->user->name . ' - ' . $record->farm->name),
                Tables\Columns\TextColumn::make('total_liters')
                    ->label('Litros')
                    ->numeric()
                    ->summarize(Sum::make()->label(''))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('price_per_liter')
                    ->label('Precio')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Average::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Producido')
                    ->state(fn ($record) => $record->total_liters * $record->price_per_liter)
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('loan_amount')
                    ->label('Prestado')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('previous_balance')
                    ->label('Debe')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('discounts')
                    ->label('Descuentos')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('new_balance')
                    ->label('Nuevo Saldo')
                    ->state(fn ($record) => $record->previous_balance - $record->discounts)
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('net_total')
                    ->label('Neto')
                    ->state(fn ($record) => ($record->total_liters * $record->price_per_liter) - $record->discounts)
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
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\Action::make('ver_detalles')
                    ->label('')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('info')
                    ->tooltip('Detalles')
                    ->iconSize('h-6 w-6')
                    ->modalHeading(fn (Liquidation $record) => $record->farm->user->name . ' - ' . $record->farm->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth('md')
                    ->modalContent(fn (Liquidation $record) =>
                        new HtmlString(
                            '<div class="overflow-x-auto text-sm w-full">
                                <table class="table-auto w-full border border-gray-300">
                                    <thead class="bg-gray-100 dark:bg-gray-800 text-left text-gray-700 dark:text-gray-200">
                                        <tr>
                                            <th class="px-4 py-2 border-b">Fecha</th>
                                            <th class="px-4 py-2 border-b text-right">Litros</th>
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
                Tables\Actions\Action::make('generar_pdf')
                    ->label('')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->tooltip('Descargar PDF')
                    ->url(fn ($record) => route('filament.liquidation.pdf', $record), true),
                Tables\Actions\Action::make('delete')
                    ->label('')
                    ->icon('heroicon-o-arrow-uturn-down')
                    ->tooltip('Deshacer liquidación')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('¿Desea deshacer esta liquidación?')
                    ->modalDescription('Al deshacer esta liquidación asegurate de eliminar manualmente el descuento que tenga asociado.')
                    ->action(function ($record) {
                        \App\Models\MilkPurchase::where('liquidation_id', $record->id)
                            ->update([
                                'status' => 'pending',
                                'liquidation_id' => null,
                            ]);

                        $record->delete();

                        Notification::make()
                            ->title('Liquidación deshecha correctamente')
                            ->success()
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete_liquidation')
                        ->label('Deshacer liquidaciones')
                        ->icon('heroicon-o-arrow-uturn-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('¿Desea deshacer las liquidaciones seleccionadas?')
                        ->modalDescription('Al deshacer estas liquidaciones asegurate de eliminar manualmente los descuentos que tengan asociados.')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            foreach ($records as $record) {
                                \App\Models\MilkPurchase::where('liquidation_id', $record->id)
                                    ->update([
                                        'status' => 'pending',
                                        'liquidation_id' => null,
                                    ]);

                                $record->delete();
                            }

                            Notification::make()
                                ->title('Liquidaciones deshechas correctamente')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('descargar_pdf')
                        ->label('Descargar PDF')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->action(function (Collection $records) {
                            Notification::make()
                                ->title('Generando archivo PDF…')
                                ->success()
                                ->send();

                            $ids = $records->pluck('id')->implode(',');
                            return redirect()->to(route('filament.liquidations.bulk-pdf', ['ids' => $ids]));
                        })
                        ->deselectRecordsAfterCompletion(),
                ])
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
            'index' => Pages\ListLiquidations::route('/'),
        ];
    }
}
