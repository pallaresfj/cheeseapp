<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Venta';
    protected static ?string $pluralLabel = 'Ventas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('sale_date')
                    ->label('Fecha de Venta')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->options(\App\Models\Branch::where('active', true)->pluck('name', 'id'))
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Cliente')
                    ->searchable()
                    ->options(\App\Models\User::where('role', 'customer')->pluck('name', 'id'))
                    ->required(),
                Forms\Components\Select::make('classification_id')
                    ->label('Tipo Cliente')
                    ->relationship('classification', 'name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $classification = \App\Models\CustomerClassification::find($state);
                        if ($classification) {
                            $set('price_per_kilo', $classification->price);
                        }
                    }),
                Forms\Components\TextInput::make('kilos')
                    ->label('Kilos')
                    ->default(0.00)
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('amount_paid', ($state ?? 0) * ($get('price_per_kilo') ?? 0));
                    }),
                Forms\Components\TextInput::make('price_per_kilo')
                    ->label('Precio por Kilo')
                    ->default(0.00)
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('amount_paid', ($get('kilos') ?? 0) * ($state ?? 0));
                    }),
                Forms\Components\TextInput::make('amount_paid')
                    ->label('Monto')
                    ->prefix('$')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->reactive()
                    ->default(0.00)
                    ->afterStateHydrated(function ($component, $state) {
                        $record = $component->getRecord();
                        if ($record) {
                            $component->state($record->kilos * $record->price_per_kilo);
                        }
                    }),
                Forms\Components\TextInput::make('balance')
                    ->label('Saldo')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'cancelled' => 'Cancelado',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('classification.name')
                    ->label('Tipo Cliente')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Fecha de Venta')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kilos')
                    ->label('Kilos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_kilo')
                    ->label('Precio por Kilo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Monto Pagado')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado'),
            ])
            ->filters([
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
