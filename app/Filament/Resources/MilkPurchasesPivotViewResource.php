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
                    ->modalSubmitActionLabel('Cambiar Sucursal')
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
                        session(['pivot_branch_id' => $data['branch_id']]);
                        $ciclo = \App\Models\Setting::where('key', 'facturacion.ciclo')->value('value') ?? 7;
                        DB::statement("CALL generate_milk_purchases_pivot_view({$data['branch_id']}, '{$data['start_date']}', $ciclo)");
                        Notification::make()
                            ->title('Sucursal y fecha actualizadas')
                            ->body('La vista de compras se ha actualizado para la sucursal y fecha seleccionadas.')
                            ->success()
                            ->send();
                    }),
                Action::make('planillaPorFecha')
                    ->label('Compras')
                    ->icon('heroicon-o-plus')
                    ->color('warning')
                    ->modalHeading('Registrar Compras')
                    ->modalSubmitActionLabel('Ver Planilla')
                    ->modalDescription('Se cargará la planilla de compras para la sucursal y fecha seleccionadas')
                    ->modalWidth('md')
                    ->form([
                        Select::make('date')
                            ->label('Fecha')
                            ->searchable()
                            ->preload()
                            ->options(
                                collect(Schema::getColumnListing('milk_purchases_pivot_view'))
                                    ->filter(fn ($col) => preg_match('/^\d{4}_\d{2}_\d{2}$/', $col))
                                    ->mapWithKeys(function ($col) {
                                        $fecha = \Carbon\Carbon::createFromFormat('Y_m_d', $col);
                                        $label = $fecha->locale('es_CO')->isoFormat('MMM DD');
                                        return [$fecha->toDateString() => $label];
                                    })
                                    ->toArray()
                            )
                            ->required()
                    ])
                    ->action(function (array $data) {
                        $branchId = session('pivot_branch_id');
                        $date = $data['date'];

                        if (!$branchId || !$date) {
                            Notification::make()
                                ->title('Sucursal o fecha no definidas')
                                ->danger()
                                ->send();
                            return;
                        }

                        DB::statement("CALL sp_registrar_compras(?, ?, ?)", [
                            $branchId,
                            $date,
                            Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Planilla generada')
                            ->success()
                            ->send();

                        return redirect(\App\Filament\Resources\PurchaseRegistrationResource::getUrl());
                    }),
                Action::make('liquidarCompras')
                    ->label('Liquidación')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->form([
                        Placeholder::make('resumen')
                            ->label(function () {
                                $branchName = \App\Models\Branch::find(session('pivot_branch_id'))?->name ?? 'seleccionada';
                                return "Sucursal {$branchName}";
                            })
                            ->content(function () {
                                $branchId = session('pivot_branch_id');
                                $branchName = Branch::find($branchId)?->name;
                                $start = MilkPurchase::where('branch_id', $branchId)
                                    ->where('status', 'pending')
                                    ->orderBy('date')
                                    ->value('date');

                                if (!$start) {
                                    return 'No hay registros pendientes para procesar.';
                                }

                                $ciclo = (int) \App\Models\Setting::where('key', 'facturacion.ciclo')->value('value') ?? 7;
                                $end = \Carbon\Carbon::parse($start)->addDays($ciclo - 1);

                                $query = \App\Models\MilkPurchase::where('branch_id', $branchId)
                                    ->where('status', 'pending')
                                    ->whereBetween('date', [$start, $end->toDateString()]);

                                $count = $query->count();
                                $litros = number_format($query->sum('liters'), 2);
                                $startFormatted = \Carbon\Carbon::parse($start)->translatedFormat('F d/Y');
                                $endFormatted = $end->translatedFormat('F d/Y');

                                return "Se van a procesar {$count} compras pendientes de esta sucursal entre {$startFormatted} y {$endFormatted}, por un total de {$litros} litros.";
                            }),
                    ])
                    ->action(function (array $data): void {
                        try {
                            $start = MilkPurchase::where('branch_id', session('pivot_branch_id'))
                                ->where('status', 'pending')
                                ->orderBy('date')
                                ->value('date');

                            DB::statement("CALL liquidar_compras(?, ?, ?)", [
                                session('pivot_branch_id'),
                                $start,
                                \Carbon\Carbon::parse($start)->addDays(
                                    (int) Setting::where('key', 'facturacion.ciclo')->value('value') ?? 7
                                )->toDateString(),
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
                    ->modalHeading('Ejecutar Liquidación'),
                // Acción: Planilla por Fecha
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
                ->formatStateUsing(fn ($record) => $record->farm->user->name . ' - ' . $record->farm->name)
                ->action(function ($record) {
                    $branchId = $record->branch_id;
                    $farmId = $record->farm_id;

                    $columns = collect(\Illuminate\Support\Facades\Schema::getColumnListing('milk_purchases_pivot_view'))
                        ->filter(fn ($col) => preg_match('/^\d{4}_\d{2}_\d{2}$/', $col))
                        ->map(fn ($col) => \Carbon\Carbon::createFromFormat('Y_m_d', $col)->toDateString())
                        ->sort()
                        ->values();

                    $startDate = $columns->first();
                    $endDate = $columns->last();

                    if (!$branchId || !$farmId || !$startDate || !$endDate) {
                        \Filament\Notifications\Notification::make()
                            ->title('Datos incompletos')
                            ->body('Finca, sucursal o fechas no definidas.')
                            ->danger()
                            ->send();
                        return;
                    }

                    \Illuminate\Support\Facades\DB::statement("CALL sp_registrar_compras_finca(?, ?, ?, ?, ?)", [
                        $branchId,
                        $farmId,
                        $startDate,
                        $endDate,
                        \Illuminate\Support\Facades\Auth::id(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Planilla individual generada')
                        ->success()
                        ->send();

                    return redirect(\App\Filament\Resources\PurchaseRegistrationResource::getUrl());
                }),
        ];

        $dynamicColumns = collect(Schema::getColumnListing('milk_purchases_pivot_view'))
            ->filter(fn ($col) => preg_match('/^\d{4}_\d{2}_\d{2}$/', $col))
            ->map(function ($col) {
                $fecha = \Carbon\Carbon::createFromFormat('Y_m_d', $col);
                $label = $fecha->locale('es_CO')->isoFormat('MMM DD');
                return TextColumn::make($col)
                    ->label($label)
                    ->numeric()
                    ->alignEnd();
            })
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