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
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->placeholder('Seleccione sucursal')
                    ->options(\App\Models\Branch::where('active', true)->pluck('name', 'id'))
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('farm_id')
                    ->label('Finca')
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
                    })
                    ->placeholder('Seleccione finca')
                    ->reactive()
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->required()
                    ->default(fn () => session('last_milk_purchase_date', now())),
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
