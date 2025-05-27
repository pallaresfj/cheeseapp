<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Branch;
use App\Models\CustomerClassification;
use App\Models\Sale;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Venta';
    protected static ?string $pluralLabel = 'Ventas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        DatePicker::make('sale_date')
                            ->label('Fecha de Venta')
                            ->default(now())
                            ->required(),
                        Select::make('classification_id')
                            ->label('Tipo Venta')
                            ->placeholder('Seleccione tipo de venta')
                            ->relationship('classification', 'name')
                            ->required()
                            ->native(false)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $classification = CustomerClassification::find($state);
                                if ($classification) {
                                    $set('price_per_kilo', $classification->price);
                                } else {
                                    $set('price_per_kilo', 0);
                                }
                            }),
                        Select::make('branch_id')
                            ->label('Sucursal')
                            ->placeholder('Seleccione sucursal')
                            ->options(Branch::where('active', true)->orderBy('name')->pluck('name', 'id'))
                            ->native(false)
                            ->required(),
                    ]),
                Grid::make()
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Cliente')
                            ->placeholder('Seleccione cliente')
                            ->searchable()
                            ->native(false)
                            ->options(User::where('role', 'customer')->pluck('name', 'id'))
                            ->required(),
                        TextInput::make('kilos')
                            ->label('Kilos')
                            ->default(0.00)
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $set('amount_paid', ($state ?? 0) * ($get('price_per_kilo') ?? 0));
                            }),
                    ]),
                TextInput::make('price_per_kilo')
                    ->label('Precio por Kilo')
                    ->default(0.00)
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('amount_paid', ($get('kilos') ?? 0) * ($state ?? 0));
                    })
                    ->afterStateHydrated(function ($component, $state) {
                        $record = $component->getRecord();
                        if ($record) {
                            $classification = \App\Models\CustomerClassification::find($record->classification_id);
                            $component->state($classification?->price ?? 0);
                        }
                    }),
                TextInput::make('amount_paid')
                    ->label('Monto')
                    ->prefix('$')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->reactive()
                    ->default(0.00)
                    ->afterStateHydrated(function ($component) {
                        $record = $component->getRecord();
                        if ($record) {
                            $component->state($record->amount_paid);
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extremePaginationLinks()
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Cliente')
                    ->numeric(),
                TextColumn::make('classification.name')
                    ->label('Tipo')
                    ->numeric(),
                TextColumn::make('sale_date')
                    ->label('Fecha')
                    ->date(),
                TextColumn::make('kilos')
                    ->label('Kilos')
                    ->numeric()
                    ->summarize(Sum::make()->label(''))
                    ->alignEnd(),
                TextColumn::make('price_per_kilo')
                    ->label('Precio')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Average::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                TextColumn::make('amount_paid')
                    ->label('Venta')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
            ])
            ->groups([
                Group::make('branch.name')
                    ->label('Sucursal')
                    ->collapsible()
            ])
            ->defaultGroup('branch.name')
            ->groupingSettingsHidden()
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Cliente')
                    ->options(\App\Models\User::where('role', 'customer')->pluck('name', 'id')),

                SelectFilter::make('classification_id')
                    ->label('Tipo de Venta')
                    ->relationship('classification', 'name'),

                Filter::make('sale_date')
                    ->label('Fecha')
                    ->form([
                        DatePicker::make('from')->label('Desde'),
                        DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('sale_date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('sale_date', '<=', $data['until']));
                    }),
            ])
            ->actions([
                EditAction::make()
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
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
