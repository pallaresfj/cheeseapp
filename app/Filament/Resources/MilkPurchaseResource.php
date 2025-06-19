<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MilkPurchaseResource\Pages;
use App\Filament\Resources\MilkPurchaseResource\RelationManagers;
use App\Models\Branch;
use App\Models\MilkPurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class MilkPurchaseResource extends Resource
{
    protected static ?string $model = MilkPurchase::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-m-chevron-right';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Compra';
    protected static ?string $pluralLabel = 'Compras individuales';
    protected static bool $hasTitleCaseModelLabel = false;

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
                    ->options(Branch::where('active', true)->orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->native(false)
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
            ->extremePaginationLinks()
            ->headerActions([
                //
            ])
            ->striped()
            ->columns([
                Tables\Columns\IconColumn::make('status')
                    ->label('')
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'liquidated' => 'heroicon-o-check-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'liquidated' => 'success',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'liquidated' => 'Liquidada',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('farm.name')
                    ->label('Proveedor - Finca')
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn ($state, $record) => "{$record->farm->user->name} - {$record->farm->name}"),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('liters')
                    ->label('Litros')
                    ->numeric(),
            ])
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->join('farms', 'milk_purchases.farm_id', '=', 'farms.id')
                    ->join('branches', 'milk_purchases.branch_id', '=', 'branches.id')
                    ->join('users', 'farms.user_id', '=', 'users.id')
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
                    ->preload()
                    ->native(false),
                Tables\Filters\SelectFilter::make('farm_id')
                    ->label('Finca')
                    ->relationship('farm', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
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
                    ExportBulkAction::make()->exports([
                        ExcelExport::make('form')
                            ->fromForm()
                            ->withFilename(date('Ymd') . '_Compras'),
                    ]),
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
