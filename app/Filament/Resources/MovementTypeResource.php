<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovementTypeResource\Pages;
use App\Filament\Resources\MovementTypeResource\RelationManagers;
use App\Models\MovementType;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovementTypeResource extends Resource
{
    protected static ?string $model = MovementType::class;

    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Empresa';
    protected static ?string $label = 'Tipo de movimiento';
    protected static ?string $pluralLabel = 'Tipos de movimiento';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('class')
                    ->label('Clase')
                    ->options([
                        'income' => 'Ingreso',
                        'expense' => 'Egreso',
                    ])
                    ->native(false)
                    ->required(),
                Select::make('type')
                    ->label('Tipo')
                    ->default('fixed')
                    ->options([
                        'fixed' => 'Fijo',
                        'variable' => 'Variable',
                    ])
                    ->native(false)
                    ->required(),
                TextInput::make('description')
                    ->label('Descripción')
                    ->maxLength(100)
                    ->columnSpanFull(),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('class')
                    ->label('Clase')
                    ->formatStateUsing(fn (string $state) => $state === 'income' ? 'Ingreso' : 'Egreso'),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => $state === 'fixed' ? 'Fijo' : 'Variable')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class')
                    ->label('Clase')
                    ->options([
                        'income' => 'Ingreso',
                        'expense' => 'Egreso',
                    ])
                    ->native(false),
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
            'index' => Pages\ListMovementTypes::route('/'),
            'create' => Pages\CreateMovementType::route('/create'),
            'edit' => Pages\EditMovementType::route('/{record}/edit'),
        ];
    }
}
