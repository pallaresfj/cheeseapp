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
    protected static ?string $navigationGroup = 'Ventas';
    protected static ?string $label = 'Venta';
    protected static ?string $pluralLabel = 'Ventas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Cliente')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('classification_id')
                    ->label('Tipo Cliente')
                    ->relationship('classification', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('sale_date')
                    ->label('Fecha de Venta')
                    ->required(),
                Forms\Components\TextInput::make('kilos')
                    ->label('Kilos')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price_per_kilo')
                    ->label('Precio por Kilo')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('amount_paid')
                    ->label('Monto Pagado')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('balance')
                    ->label('Saldo')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->label('Estado')
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
                    ->numeric()
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
                Tables\Actions\EditAction::make(),
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
