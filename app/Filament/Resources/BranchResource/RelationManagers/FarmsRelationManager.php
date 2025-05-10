<?php

namespace App\Filament\Resources\BranchResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FarmsRelationManager extends RelationManager
{
    protected static string $relationship = 'farms';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Proveedor')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Finca')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->required(),
                Forms\Components\Select::make('farm_type_id')
                    ->label('Tipo de finca')
                    ->relationship('farmType', 'name')
                    ->required(),
                Forms\Components\TextInput::make('location')
                    ->label('Ubicación')
                    ->maxLength(255),
                Forms\Components\Toggle::make('status')
                    ->label('Activa')
                    ->default(true)
                    ->required()
                    ->visibleOn('edit'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Proveedor - Finca')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => "{$record->user->name} - {$record->name}"),
                Tables\Columns\TextColumn::make('farmType.name')
                    ->label('Tipo de finca')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Estado')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('farm_type_id')
                    ->label('Tipo de finca')
                    ->relationship('farmType', 'name')
                    ->searchable(),
            ])
            ->persistFiltersInSession()
            ->groups([
                Tables\Grouping\Group::make('farm_type_id')
                    ->label('Tipo de finca')
                    ->collapsible()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
