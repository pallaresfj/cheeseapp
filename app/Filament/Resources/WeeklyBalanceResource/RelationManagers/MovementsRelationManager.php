<?php

namespace App\Filament\Resources\WeeklyBalanceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('date')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date(),
                Tables\Columns\TextColumn::make('movementType.description')
                    ->label('Descripción')
                    ->numeric(),
                TextColumn::make('value')
                    ->label('Monto')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->getStateUsing(fn ($record) => $record->status === 'reconciled'),
            ])
            ->filters([
                //
            ])
            ->groups([
                Tables\Grouping\Group::make('movementType.class')
                    ->label('Clase')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn ($record) => $record->movementType->class === 'income' ? 'Ingresos' : 'Egresos'),
            ])
            ->defaultGroup('movementType.class')
            ->groupingSettingsHidden()
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
