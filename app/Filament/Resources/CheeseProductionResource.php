<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheeseProductionResource\Pages;
use App\Filament\Resources\CheeseProductionResource\RelationManagers;
use App\Models\CheeseProduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CheeseProductionResource extends Resource
{
    protected static ?string $model = CheeseProduction::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-stop';
    protected static ?string $navigationGroup = 'Compras';
    protected static ?string $label = 'Producción de Queso';
    protected static ?string $pluralLabel = 'Producción de Queso';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->required(),
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->required(),
                Forms\Components\TextInput::make('produced_kilos')
                    ->label('Kilos Producidos')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('processed_liters')
                    ->label('Litros Procesados')
                    ->required()
                    ->numeric(),
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
                Tables\Columns\TextColumn::make('produced_kilos')
                    ->label('Kilos Producidos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('processed_liters')
                    ->label('Litros Procesados')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListCheeseProductions::route('/'),
            'create' => Pages\CreateCheeseProduction::route('/create'),
            'edit' => Pages\EditCheeseProduction::route('/{record}/edit'),
        ];
    }
}
