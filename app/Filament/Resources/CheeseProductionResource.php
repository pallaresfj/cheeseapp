<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheeseProductionResource\Pages;
use App\Filament\Resources\CheeseProductionResource\RelationManagers;
use App\Models\CheeseProduction;
use App\Models\MilkPurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Average;

class CheeseProductionResource extends Resource
{
    protected static ?string $model = CheeseProduction::class;
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-stop';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Producción de Queso';
    protected static ?string $pluralLabel = 'Producción de Queso';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->options(\App\Models\Branch::where('active', true)->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->native(false)
                    ->default(fn () => session('cheese_production_branch_id'))
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        session(['cheese_production_branch_id' => $state]);

                        $branchId = $state;

                        // Obtener la fecha más reciente de milk_purchases para la sucursal
                        $lastDate = \App\Models\CheeseProduction::when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                            ->orderByDesc('date')
                            ->value('date');

                        $nextDate = $lastDate ? \Illuminate\Support\Carbon::parse($lastDate)->addDay()->toDateString() : now()->toDateString();

                        $set('date', $nextDate);

                        $date = $nextDate;

                        if ($branchId && $date) {
                            $totalLiters = MilkPurchase::where('branch_id', $branchId)
                                ->whereDate('date', $date)
                                ->sum('liters');

                            $set('processed_liters', $totalLiters);
                        }
                    }),
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->default(function (callable $get) {
                        $branchId = $get('branch_id');
                        $lastDate = \App\Models\CheeseProduction::when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                            ->orderByDesc('date')
                            ->value('date');

                        return $lastDate ? \Illuminate\Support\Carbon::parse($lastDate)->addDay()->toDateString() : now()->toDateString();
                    })
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $branchId = $get('branch_id');
                        $date = $state;

                        $set('date', $date);

                        if ($branchId && $date) {
                            $totalLiters = MilkPurchase::where('branch_id', $branchId)
                                ->whereDate('date', $date)
                                ->sum('liters');

                            $set('processed_liters', $totalLiters);
                        }
                    }),
                Forms\Components\TextInput::make('processed_liters')
                    ->label('Litros Procesados')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(true)
                    ->reactive()
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, $set, $get) {
                        $branchId = $get('branch_id');
                        $date = $get('date');

                        if ($branchId && $date) {
                            $totalLiters = MilkPurchase::where('branch_id', $branchId)
                                ->whereDate('date', $date)
                                ->sum('liters');

                            $set('processed_liters', $totalLiters);

                            $productividad = cache('app_settings')['sistema.productividad'] ?? 0;
                            $set('produced_kilos', round($totalLiters * floatval($productividad), 2));
                        }
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        $productividad = cache('app_settings')['sistema.productividad'] ?? 0;
                        $set('produced_kilos', round($state * floatval($productividad), 2));
                    }),
                Forms\Components\TextInput::make('produced_kilos')
                    ->label('Kilos Producidos')
                    ->numeric()
                    ->default(0)
                    ->dehydrated(true)
                    ->reactive()
                    ->afterStateHydrated(function ($component, $state, $set, $get) {
                        if (! $state || $state == 0) {
                            $litros = $get('processed_liters');
                            $productividad = cache('app_settings')['sistema.productividad'] ?? 0;

                            if ($litros && $productividad) {
                                $set('produced_kilos', round($litros * floatval($productividad), 2));
                            }
                        }
                    })
                    ->hint(function (callable $get) {
                        $litros = $get('processed_liters');
                        $productividad = cache('app_settings')['sistema.productividad'] ?? 0;

                        return ($litros && $productividad)
                            ? 'Sugerido: ' . round($litros * floatval($productividad), 2) . ' kg'
                            : null;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('processed_liters')
                    ->label('Litros Procesados')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('')),
                Tables\Columns\TextColumn::make('produced_kilos_sugerido')
                    ->label('Kilos Esperados')
                    ->state(function ($record) {
                        $productividad = cache('app_settings')['sistema.productividad'] ?? 0;
                        return round($record->processed_liters * floatval($productividad), 2);
                    }),
                Tables\Columns\TextColumn::make('produced_kilos')
                    ->label('Kilos Producidos')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('')),
                Tables\Columns\TextColumn::make('produced_kilos_porcentaje')
                    ->label('% Producción')
                    ->state(function ($record) {
                        $productividad = cache('app_settings')['sistema.productividad'] ?? 0;
                        $esperado = floatval($record->processed_liters) * floatval($productividad);
                        if ($esperado > 0) {
                            return round(($record->produced_kilos / $esperado) * 100, 1) . '%';
                        }
                        return '0%';
                    })
                    ->color(function ($record) {
                        $productividad = cache('app_settings')['sistema.productividad'] ?? 0;
                        $esperado = floatval($record->processed_liters) * floatval($productividad);
                        if ($esperado <= 0) return 'gray';

                        $porcentaje = ($record->produced_kilos / $esperado) * 100;

                        return match (true) {
                            $porcentaje >= 100 => 'success',
                            $porcentaje >= 80 => 'info',
                            default => 'warning',
                        };
                    })
                    ->badge(),
            ])
            ->groups([
                Tables\Grouping\Group::make('branch.name')
                    ->label('Sucursal')
                    ->collapsible()
            ])
            ->defaultGroup('branch.name')
            ->groupingSettingsHidden()
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->native(false)
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->label('Fecha')
                    ->form([
                        Forms\Components\DatePicker::make('date')->label('Fecha'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['date'], fn ($q) => $q->whereDate('date', $data['date']));
                    }),
            ])
            ->persistFiltersInSession()
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
            'index' => Pages\ListCheeseProductions::route('/'),
            'create' => Pages\CreateCheeseProduction::route('/create'),
            'edit' => Pages\EditCheeseProduction::route('/{record}/edit'),
        ];
    }
}
