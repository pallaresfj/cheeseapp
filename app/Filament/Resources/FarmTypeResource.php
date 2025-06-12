<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FarmTypeResource\Pages;
use App\Filament\Resources\FarmTypeResource\RelationManagers;
use App\Models\FarmType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FarmTypeResource extends Resource
{
    protected static ?string $model = FarmType::class;
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-m-chevron-right';
    protected static ?string $navigationGroup = 'Empresa';
    protected static ?string $label = 'Tipo de finca';
    protected static ?string $pluralLabel = 'Tipos de finca';
    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('base_price')
                    ->label('Precio base')
                    ->prefix('$')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('description')
                    ->label('Descripción')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('Precio base')
                    ->money('COP', locale: 'es_CO')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
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
                Tables\Actions\RestoreAction::make()
                    ->label('')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->tooltip('Restaurar')
                    ->iconSize('h-6 w-6'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->tooltip('Borrar permanentemente')
                    ->iconSize('h-6 w-6'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListFarmTypes::route('/'),
            'create' => Pages\CreateFarmType::route('/create'),
            'edit' => Pages\EditFarmType::route('/{record}/edit'),
        ];
    }
}
