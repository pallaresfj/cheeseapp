<?php

namespace App\Filament\Resources;

use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\MilkPurchasesPivotViewResource\Pages;
use App\Filament\Resources\MilkPurchasesPivotViewResource\RelationManagers;
use App\Models\Branch;
use App\Models\MilkPurchase;
use App\Models\MilkPurchasesPivotView;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Grouping\Group;
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
                    ->modalHeading('Indique sucursal y fecha de inicio')
                    ->modalSubmitActionLabel('Actualizar Sucursal')
                    ->modalDescription('Se actualizará para la sucursal y fecha seleccionadas')
                    ->modalWidth('md')
                    ->form([
                        Select::make('branch_id')
                            ->label('Sucursal')
                            ->options(Branch::where('active', true)->pluck('name', 'id'))
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
                        DatePicker::make('start_date')
                            ->label('Inicio de ciclo')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $ciclo = \App\Models\Setting::where('key', 'facturacion.ciclo')->value('value') ?? 7;
                        DB::statement("CALL generate_milk_purchases_pivot_view({$data['branch_id']}, '{$data['start_date']}', $ciclo)");
                        Notification::make()
                            ->title('Sucursal y fecha actualizadas')
                            ->body('La vista de compras se ha actualizado para la sucursal y fecha seleccionadas.')
                            ->success()
                            ->send();
                    }),
                Action::make('registrarCompras')
                    ->label('Compras')
                    ->icon('heroicon-o-plus')
                    ->color('warning')
                    ->modalHeading('Registrar Compras')
                    ->modalSubmitActionLabel('Ver Planilla')
                    ->modalDescription('Se cargará la planilla de compras para la sucursal y fecha seleccionadas')
                    ->modalWidth('md')
                    ->form([
                        Select::make('branch_id')
                            ->label('Sucursal')
                            ->options(Branch::where('active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $startDate = MilkPurchase::where('branch_id', $state)
                                    ->where('status', 'pending')
                                    ->orderBy('date')
                                    ->value('date');
                                    $set('date', $startDate);
                            }),
                        DatePicker::make('date')
                            ->label('Inicio de ciclo')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        DB::statement('CALL sp_registrar_compras(?, ?, ?)', [
                            $data['branch_id'],
                            $data['date'],
                            Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Planilla de compras')
                            ->body('Se ha generado la planilla de compras para la sucursal y fecha seleccionadas.')
                            ->success()
                            ->send();

                        return redirect(PurchaseRegistrationResource::getUrl());
                    }),
                Action::make('liquidarCompras')
                    ->label('Liquidar')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->form([
                        Select::make('branch_id')
                            ->label('Sucursal')
                            ->options(Branch::where('active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $startDate = MilkPurchase::where('branch_id', $state)
                                    ->where('status', 'pending')
                                    ->orderBy('date')
                                    ->value('date');

                                if ($startDate) {
                                    $ciclo = (int) Setting::where('key', 'facturacion.ciclo')->value('value') ?? 7;
                                    $endDate = \Carbon\Carbon::parse($startDate)->addDays($ciclo - 1);
                                    $settlementDate = $endDate->copy()->addDay();

                                    $set('start_date', $startDate);
                                    $set('end_date', $endDate->toDateString());
                                    $set('settlement_date', $settlementDate->toDateString());
                                }
                            }),

                        DatePicker::make('start_date')
                            ->label('Fecha inicio')
                            ->required()
                            ->reactive(),

                        DatePicker::make('end_date')
                            ->label('Fecha final')
                            ->required()
                            ->reactive(),


                        Placeholder::make('resumen_liquidacion')
                            ->label('Resumen')
                            ->content(function (callable $get) {
                                $branchId = $get('branch_id');
                                $start = $get('start_date');
                                $end = $get('end_date');

                                if (!$branchId || !$start || !$end) return 'Seleccione sucursal y fechas';

                                $query = MilkPurchase::where('branch_id', $branchId)
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
                            DB::statement("CALL liquidar_compras(?, ?, ?)", [
                                $data['branch_id'],
                                $data['start_date'],
                                $data['end_date'],
                            ]);

                            Notification::make()
                                ->title('Liquidación ejecutada correctamente')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
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
                Group::make('branch.name')
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
            TextColumn::make('farm.name')
                ->label('Proveedor - Finca')
                ->formatStateUsing(fn ($record) => $record->farm->user->name . ' - ' . $record->farm->name),
        ];

        $dynamicColumns = collect(Schema::getColumnListing('milk_purchases_pivot_view'))
            ->filter(fn ($col) => preg_match('/^\d{4}_\d{2}_\d{2}$/', $col))
            ->map(fn ($col) => TextColumn::make($col)
                ->label(\Carbon\Carbon::createFromFormat('Y_m_d', $col)->locale('es_CO')->isoFormat('MMM DD'))
                ->numeric()
                ->alignEnd()
            )
            ->values()
            ->toArray();

        $footerColumns = [
            TextColumn::make('total_litros')
                ->label('Litros')
                ->numeric()
                ->alignEnd(),
            TextColumn::make('base_price')
                ->label('Precio')
                ->money('COP', locale: 'es_CO')
                ->alignEnd(),
            TextColumn::make('producido')
                ->label('Producido')
                ->money('COP', locale: 'es_CO')
                ->alignEnd(),
        ];

        return array_merge($baseColumns, $dynamicColumns, $footerColumns);
    }
}