<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MilkPurchasesPivotViewResource\Pages;
use App\Filament\Resources\MilkPurchasesPivotViewResource\RelationManagers;
use App\Models\MilkPurchasesPivotView;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Livewire\Component as Livewire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class MilkPurchasesPivotViewResource extends Resource
{
    protected static ?string $model = MilkPurchasesPivotView::class;
    protected static ?string $primaryKey = 'farm_id';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Compra Semanal';
    protected static ?string $pluralLabel = 'Compras Semanales';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description('Compras de leche por finca y sucursal para la semana actual')
            ->headerActions([
                Action::make('configurarVista')
                    ->label('Sucursal')
                    ->icon('heroicon-o-building-office')
                    ->color('info')
                    ->modalHeading('Parámetros de la vista')
                    ->modalWidth('md')
                    ->form([
                        Forms\Components\Select::make('branch_id')
                            ->label('Sucursal')
                            ->options(\App\Models\Branch::where('active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $startDate = \App\Models\MilkPurchase::where('branch_id', $state)
                                    ->where('status', 'pending')
                                    ->orderBy('date')
                                    ->value('date');
                                    $set('start_date', $startDate);
                            }),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha Inicial')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $ciclo = \App\Models\Setting::where('key', 'facturacion.ciclo')->value('value') ?? 10;
                        DB::statement("CALL generate_milk_purchases_pivot_view({$data['branch_id']}, '{$data['start_date']}', $ciclo)");
                        Notification::make()->title('Vista actualizada')->success()->send();
                    }),
                Action::make('liquidarCompras')
                    ->label('Liquidar')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('branch_id')
                            ->label('Sucursal')
                            ->options(\App\Models\Branch::where('active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $startDate = \App\Models\MilkPurchase::where('branch_id', $state)
                                    ->where('status', 'pending')
                                    ->orderBy('date')
                                    ->value('date');

                                if ($startDate) {
                                    $ciclo = (int) \App\Models\Setting::where('key', 'facturacion.ciclo')->value('value') ?? 7;
                                    $endDate = \Carbon\Carbon::parse($startDate)->addDays($ciclo - 1);
                                    $settlementDate = $endDate->copy()->addDay();

                                    $set('start_date', $startDate);
                                    $set('end_date', $endDate->toDateString());
                                    $set('settlement_date', $settlementDate->toDateString());
                                }
                            }),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha inicio')
                            ->required()
                            ->reactive(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Fecha final')
                            ->required()
                            ->reactive(),


                        Forms\Components\Placeholder::make('resumen_liquidacion')
                            ->label('Resumen')
                            ->content(function (callable $get) {
                                $branchId = $get('branch_id');
                                $start = $get('start_date');
                                $end = $get('end_date');

                                if (!$branchId || !$start || !$end) return 'Seleccione sucursal y fechas';

                                $query = \App\Models\MilkPurchase::where('branch_id', $branchId)
                                    ->where('status', 'pending')
                                    ->whereBetween('date', [$start, $end]);

                                $count = $query->count();
                                $totalLitros = number_format($query->sum('liters'), 2);

                                $startFormatted = \Carbon\Carbon::parse($start)->translatedFormat('F d/Y');
                                $endFormatted = \Carbon\Carbon::parse($end)->translatedFormat('F d/Y');
                                return "Se liquidarán {$count} compras pendientes entre {$startFormatted} y {$endFormatted}, con un total de {$totalLitros} litros.";
                            })
                            ->visible(fn (callable $get) => $get('branch_id') && $get('start_date') && $get('end_date')),
                    ])
                    ->action(function (array $data): void {
                        try {
                            \Illuminate\Support\Facades\DB::statement("CALL liquidar_compras(?, ?, ?)", [
                                $data['branch_id'],
                                $data['start_date'],
                                $data['end_date'],
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Liquidación ejecutada correctamente')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error al ejecutar la liquidación')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Ejecutar Liquidación')
            ])
            ->columns(self::getTableColumns())
            ->groups([
                Tables\Grouping\Group::make('branch.name')
                    ->label('Sucursal')
                    ->collapsible()
            ])
            ->defaultGroup('branch.name')
            ->groupingSettingsHidden()
            ->filters([
                //
            ])
            ->actions([])
            ->bulkActions([]);
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
            'index' => Pages\ListMilkPurchasesPivotViews::route('/'),
        ];
    }

    protected static function getTableColumns(): array
    {
        $baseColumns = [
            Tables\Columns\TextColumn::make('farm.name')
                ->label('Proveedor - Finca')
                ->formatStateUsing(fn ($record) => $record->farm->user->name . ' - ' . $record->farm->name),
        ];

        $dynamicColumns = collect(Schema::getColumnListing('milk_purchases_pivot_view'))
            ->filter(fn ($col) => preg_match('/^\d{4}_\d{2}_\d{2}$/', $col))
            ->map(fn ($col) => Tables\Columns\TextColumn::make($col)
                ->label(\Carbon\Carbon::createFromFormat('Y_m_d', $col)->locale('es_CO')->isoFormat('MMM DD'))
                ->numeric()
                ->alignEnd()
            )
            ->values()
            ->toArray();

        $footerColumns = [
            Tables\Columns\TextColumn::make('total_litros')
                ->label('Litros')
                ->numeric()
                ->alignEnd(),
            Tables\Columns\TextColumn::make('base_price')
                ->label('Precio')
                ->money('COP', locale: 'es_CO')
                ->alignEnd(),
            Tables\Columns\TextColumn::make('producido')
                ->label('Producido')
                ->money('COP', locale: 'es_CO')
                ->alignEnd(),
        ];

        return array_merge($baseColumns, $dynamicColumns, $footerColumns);
    }
}