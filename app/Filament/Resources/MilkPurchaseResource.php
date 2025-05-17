<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MilkPurchaseResource\Pages;
use App\Filament\Resources\MilkPurchaseResource\RelationManagers;
use App\Models\MilkPurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class MilkPurchaseResource extends Resource
{
    protected static ?string $model = MilkPurchase::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?string $label = 'Compra';
    protected static ?string $pluralLabel = 'Compras';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->required()
                    ->default(fn () => session('last_milk_purchase_date', now()))
                    ->afterStateUpdated(fn ($state) => session(['last_milk_purchase_date' => $state])),
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->placeholder('Seleccione sucursal')
                    ->options(\App\Models\Branch::where('active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(fn () => session('last_milk_purchase_branch_id'))
                    ->afterStateUpdated(fn ($state) => session(['last_milk_purchase_branch_id' => $state])),
                Forms\Components\Select::make('farm_id')
                    ->label('Finca')
                    ->placeholder('Seleccione finca')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function (callable $get) {
                        $branchId = $get('branch_id');
                        if (!$branchId) {
                            return [];
                        }
                        return \App\Models\Farm::where('branch_id', $branchId)
                            ->where('status', true)
                            ->with('user')
                            ->get()
                            ->mapWithKeys(function ($farm) {
                                return [$farm->id => "{$farm->user->name} - {$farm->name}"];
                            });
                    }),
                Forms\Components\TextInput::make('liters')
                    ->label('Litros')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Forms\Components\Hidden::make('status')
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('liquidarCompras')
                    ->label('Liquidar compras')
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
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('farm.name')
                    ->label('Proveedor - Finca')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => "{$record->farm->user->name} - {$record->farm->name}"),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('liters')
                    ->label('Litros')
                    ->numeric(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Estado')
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'liquidated' => 'heroicon-o-check',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'liquidated' => 'success',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'liquidated' => 'Liquidada',
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->join('farms', 'milk_purchases.farm_id', '=', 'farms.id')
                    ->join('branches', 'milk_purchases.branch_id', '=', 'branches.id')
                    ->join('users', 'farms.user_id', '=', 'users.id')
                    ->where('milk_purchases.status', 'pending')
                    ->orderByDesc('milk_purchases.date')
                    ->orderBy('branches.name')
                    ->orderByRaw("CONCAT(users.name, ' - ', farms.name)")
                    ->select('milk_purchases.*')
            )
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('farm_id')
                    ->label('Finca')
                    ->relationship('farm', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->label('Fecha')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($query, $date) => $query->whereDate('milk_purchases.date', '>=', $date))
                            ->when($data['until'], fn ($query, $date) => $query->whereDate('milk_purchases.date', '<=', $date));
                    }),
            ])
            ->persistFiltersInSession()
            ->groups([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->tooltip('Editar')
                    ->iconSize('h-6 w-6'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip('Borrar')
                    ->iconSize('h-6 w-6'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMilkPurchases::route('/'),
            'create' => Pages\CreateMilkPurchase::route('/create'),
            'edit' => Pages\EditMilkPurchase::route('/{record}/edit'),
        ];
    }
}
