<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovementResource\Pages;
use App\Filament\Resources\MovementResource\RelationManagers;
use App\Models\Movement;
use App\Models\WeeklyBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
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
                    ->placeholder('Seleccione sucursal')
                    ->relationship('branch', 'name', fn (Builder $query) => $query->where('active', true))
                    ->native(false)
                    ->required()
                    ->default(function () {
                        return \App\Models\Movement::latest('created_at')->value('branch_id');
                    }),
                Forms\Components\Select::make('movement_type_id')
                    ->label('Tipo de movimiento')
                    ->placeholder('Seleccione tipo de movimiento')
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
            ->extremePaginationLinks()
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->numeric(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->date(),
                Tables\Columns\TextColumn::make('movementType.description')
                    ->label('Descripción')
                    ->numeric(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->getStateUsing(fn ($record) => $record->status === 'reconciled'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name', fn (Builder $query) => $query->where('active', true))
                    ->native(false),
                Tables\Filters\Filter::make('date')
                    ->label('Rango de fechas')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
            ])
            ->persistFiltersInSession()
            ->groups([
                Tables\Grouping\Group::make('movementType.class')
                    ->label('Clase')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn ($record) => $record->movementType->class === 'income' ? 'Ingresos' : 'Egresos'),
            ])
            ->defaultGroup('movementType.class')
            ->groupingSettingsHidden()
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->orderBy('date', 'desc')
                ->orderBy(
                    \App\Models\MovementType::select('description')
                        ->whereColumn('movement_types.id', 'movements.movement_type_id')
                        ->limit(1),
                    'asc'
                )
            )
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
            ])
            ->headerActions([
                //
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
