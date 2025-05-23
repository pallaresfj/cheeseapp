<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovementResource\Pages;
use App\Filament\Resources\MovementResource\RelationManagers;
use App\Models\Movement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?string $label = 'Movimiento';
    protected static ?string $pluralLabel = 'Movimientos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->native(false)
                    ->required()
                    ->default(function () {
                        return \App\Models\Movement::latest('created_at')->value('branch_id');
                    }),
                Forms\Components\Select::make('movement_type_id')
                    ->label('Tipo de movimiento')
                    ->options(function () {
                        return \App\Models\MovementType::all()
                            ->groupBy('class')
                            ->mapWithKeys(function ($group, $key) {
                                $label = $key === 'income' ? 'Ingresos' : 'Egresos';
                                return [
                                    $label => $group->pluck('description', 'id')->toArray(),
                                ];
                            })
                            ->toArray();
                    })
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->default(function () {
                        return \App\Models\Movement::latest('created_at')->value('date') ?? now();
                    })
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label('Valor')
                    ->placeholder('0.00')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /* Tables\Columns\TextColumn::make('movementType.class')
                    ->label('Clase')
                    ->formatStateUsing(fn (string $state) => $state === 'income' ? 'Ingreso' : 'Egreso')
                    ->sortable(), */
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('movementType.description')
                    ->label('Tipo de movimiento')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->money('COP', locale: 'es_CO')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->native(false),
            ])
            ->persistFiltersInSession()
            ->groups([
                Tables\Grouping\Group::make('movementType.class')
                    ->label('Clase')
                    ->getTitleFromRecordUsing(fn ($record) => $record->movementType->class === 'income' ? 'Ingresos' : 'Egresos'),
            ])
            ->defaultGroup('movementType.class')
            ->groupingSettingsHidden()
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
            'index' => Pages\ListMovements::route('/'),
            'create' => Pages\CreateMovement::route('/create'),
            'edit' => Pages\EditMovement::route('/{record}/edit'),
        ];
    }
}
