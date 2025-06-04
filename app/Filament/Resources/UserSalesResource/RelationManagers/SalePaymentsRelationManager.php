<?php

namespace App\Filament\Resources\UserSalesResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalePaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'salePayments';
    protected static ?string $modelLabel = 'Pago';
    protected static ?string $pluralLabel = 'Pagos';
    protected static ?string $title = 'Pagos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn (RelationManager $livewire) => $livewire->getOwnerRecord()->id)
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Monto')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->extremePaginationLinks()
            ->defaultSort('date', 'desc')
            ->striped()
            ->columns([
                Tables\Columns\IconColumn::make('status')
                    ->label('')
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'reconciled' => 'heroicon-o-check-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'reconciled' => 'success',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'reconciled' => 'Conciliado',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (RelationManager $livewire) {
                        $livewire->dispatch('refresh-parent');
                    }),
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
