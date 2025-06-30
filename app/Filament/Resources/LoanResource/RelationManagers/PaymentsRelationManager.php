<?php

namespace App\Filament\Resources\LoanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $modelLabel = 'Pago';
    protected static ?string $pluralLabel = 'Pagos';
    protected static ?string $title = 'Pagos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->default(now())
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Monto')
                    ->required()
                    ->numeric()
                    ->default(fn ($livewire) => $livewire->getOwnerRecord()?->installment_value)
                    ->hint(function ($livewire) {
                        $loan = $livewire->getOwnerRecord();
                        if (! $loan) return null;

                        $max = $loan->amount - $loan->paid_value;
                        return 'Máximo permitido: $' . number_format($max, 0, ',', '.');
                    })
                    ->rules(function ($livewire) {
                        $loan = $livewire->getOwnerRecord();
                        if (! $loan) return [];

                        $max = $loan->amount - $loan->paid_value;
                        return ['numeric', 'max:' . $max];
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('COP', locale: 'es_CO'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->disabled(fn ($livewire) => optional($livewire->getOwnerRecord())->status === 'paid')
                    ->using(function (array $data) {
                        try {
                            $data['loan_id'] = $this->getRelationship()->getParent()->getKey();
                            return \App\Models\LoanPayment::create($data);
                        } catch (\Illuminate\Database\QueryException $e) {
                            if (str_contains($e->getMessage(), 'excede el monto del préstamo')) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error al registrar pago')
                                    ->body('El valor pagado excede el monto del préstamo.')
                                    ->danger()
                                    ->send();
                                return null;
                            }

                            throw $e;
                        }
                    })
                    ->after(function ($record) {
                        if ($record) {
                            \Filament\Notifications\Notification::make()
                                ->title('Pago registrado')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->tooltip('Editar')
                    ->iconSize('h-6 w-6')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->required()
                            ->numeric()
                            ->default(fn ($livewire) => $livewire->getOwnerRecord()?->installment_value)
                            ->hint(function ($livewire, $record) {
                                $loan = $livewire->getOwnerRecord();
                                if (! $loan || ! $record) return null;

                                $max = $loan->amount - $loan->paid_value + $record->amount;
                                return 'Máximo permitido: $' . number_format($max, 0, ',', '.');
                            })
                            ->rules(function ($livewire, $record) {
                                $loan = $livewire->getOwnerRecord();
                                if (! $loan || ! $record) return [];

                                $max = $loan->amount - $loan->paid_value + $record->amount;
                                return ['numeric', 'max:' . $max];
                            }),
                    ]),
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
