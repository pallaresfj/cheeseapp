<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiquidationSummaryResource\Pages;
use App\Filament\Resources\LiquidationSummaryResource\RelationManagers;
use App\Models\LiquidationSummary;
use App\Models\MilkPurchase;
use App\Models\Liquidation;
use App\Models\LoanPayment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Collection;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Average;

class LiquidationSummaryResource extends Resource
{
    protected static ?string $model = LiquidationSummary::class;

    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-m-chevron-right';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Preparar Liquidación';
    protected static ?string $pluralLabel = 'Preparar Liquidaciones';
    protected static bool $hasTitleCaseModelLabel = false;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Revise antes de generar las liquidaciones definitivas.')
            ->extremePaginationLinks()
            ->striped()
            ->columns([
                TextColumn::make('farm_display')
                    ->label('Proveedor - Finca')
                    ->weight(FontWeight::Bold)
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('farm', function (Builder $farmQuery) use ($search) {
                            $farmQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                                    $userQuery->where('name', 'like', "%{$search}%");
                                });
                        });
                    }),
                TextColumn::make('total_liters')
                    ->label('Litros')
                    ->numeric()
                    ->summarize(Sum::make()->label(''))
                    ->alignEnd(),
                TextColumn::make('price_per_liter')
                    ->label('Precio')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Average::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                TextColumn::make('total_paid')
                    ->label('Producido')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                TextColumn::make('loan_amount')
                    ->label('Prestado')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                TextColumn::make('loan_balance')
                    ->label('Debe')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                TextColumn::make('installment_value')
                    ->label('Cuota')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                TextColumn::make('discount')
                    ->label('Descuento')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                TextColumn::make('new_balance')
                    ->label('Nuevo Saldo')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                TextColumn::make('net_amount')
                    ->label('Neto')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd()
                    ->badge()
                    ->color(function ($record) {
                        if (!$record->total_paid || $record->total_paid == 0) {
                            return 'gray';
                        }
                        $percentage = ($record->net_amount / $record->total_paid) * 100;
                        return match (true) {
                            $percentage < 50 => 'danger',
                            $percentage < 70 => 'warning',
                            default => 'success',
                        };
                    }),
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
                                    <thead class="bg-gray-100 dark:bg-gray-800 text-left text-gray-700 dark:text-gray-200">
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
                Tables\Actions\Action::make('alternar_estado_prestamo')
                    ->label('')
                    ->icon(fn (LiquidationSummary $record) => match (\App\Models\Loan::find($record->loan_id)?->status) {
                        'active' => 'heroicon-o-pause-circle',
                        'suspended' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-minus-circle',
                    })
                    ->color(fn (LiquidationSummary $record) => match (\App\Models\Loan::find($record->loan_id)?->status) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn (LiquidationSummary $record) => match (\App\Models\Loan::find($record->loan_id)?->status) {
                        'active' => 'Suspender préstamo',
                        'suspended' => 'Activar préstamo',
                        default => 'Estado no disponible',
                    })
                    ->iconSize('h-6 w-6')
                    ->disabled(fn (LiquidationSummary $record) =>
                        ! $record->loan_id || ! \App\Models\Loan::where('id', $record->loan_id)
                            ->whereIn('status', ['active', 'suspended'])
                            ->exists()
                    )
                    ->action(function (LiquidationSummary $record) {
                        $loan = \App\Models\Loan::find($record->loan_id);

                        if ($loan && in_array($loan->status, ['active', 'suspended'])) {
                            $loan->status = $loan->status === 'active' ? 'suspended' : 'active';
                            $loan->save();

                            Notification::make()
                                ->title('Estado del préstamo actualizado')
                                ->success()
                                ->send();
                        }
                    }),
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete')
                        ->label('Deshacer liquidación')
                        ->icon('heroicon-o-arrow-uturn-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                MilkPurchase::where('liquidation_id', $record->id)
                                    ->update([
                                        'status' => 'pending',
                                        'liquidation_id' => null,
                                    ]);

                                Liquidation::where('id', $record->id)->delete();
                            }

                            Notification::make()
                                ->title('Liquidaciones deshechas')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Aprobar liquidación')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                // Si hay descuento
                                if ($record->discount > 0) {
                                    $loan = \App\Models\Loan::where('farm_id', $record->farm_id)
                                        ->whereIn('status', ['active', 'overdue', 'suspended'])
                                        ->orderByDesc('created_at')
                                        ->first();

                                    if ($loan) {
                                        LoanPayment::create([
                                            'loan_id' => $loan->id,
                                            'date' => Carbon::parse($record->date),
                                            'amount' => $record->discount,
                                        ]);
                                    }
                                }

                                // Actualiza la liquidación
                                \App\Models\Liquidation::where('id', $record->id)->update([
                                    'loan_amount' => $record->loan_amount,
                                    'previous_balance' => $record->loan_balance,
                                    'discounts' => $record->discount,
                                    'status' => 'liquidated',
                                ]);
                            }

                            Notification::make()
                                ->title('Liquidaciones aprobadas')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
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
            'index' => Pages\ListLiquidationSummaries::route('/'),
        ];
    }
}
