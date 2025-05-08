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
                    ->relationship('branch', 'name')
                    ->required(),
                Forms\Components\Select::make('farm_id')
                    ->label('Finca')
                    ->relationship('farm', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->required(),
                Forms\Components\TextInput::make('liters')
                    ->label('Litros')
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
                Tables\Columns\TextColumn::make('farm.name')
                    ->label('Finca')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('liters')
                    ->label('Litros')
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
            'index' => Pages\ListMilkPurchases::route('/'),
            'create' => Pages\CreateMilkPurchase::route('/create'),
            'edit' => Pages\EditMilkPurchase::route('/{record}/edit'),
        ];
    }
}
