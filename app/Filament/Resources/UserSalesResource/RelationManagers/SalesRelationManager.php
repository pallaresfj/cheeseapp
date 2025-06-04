<?php

namespace App\Filament\Resources\UserSalesResource\RelationManagers;

use App\Models\Branch;
use App\Models\CustomerClassification;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sales';
    protected static ?string $modelLabel = 'Venta';
    protected static ?string $pluralLabel = 'Ventas';
    protected static ?string $title = 'Ventas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn (RelationManager $livewire) => $livewire->getOwnerRecord()->id)
                            ->required(),
                        DatePicker::make('sale_date')
                            ->label('Fecha de Venta')
                            ->default(now())
                            ->required(),
                        Select::make('classification_id')
                            ->label('Tipo Venta')
                            ->placeholder('Seleccione tipo de venta')
                            ->relationship('classification', 'name')
                            ->required()
                            ->native(false)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $classification = CustomerClassification::find($state);
                                if ($classification) {
                                    $set('price_per_kilo', $classification->price);
                                } else {
                                    $set('price_per_kilo', 0);
                                }
                            }),
                        Select::make('branch_id')
                            ->label('Sucursal')
                            ->placeholder('Seleccione sucursal')
                            ->options(Branch::where('active', true)->orderBy('name')->pluck('name', 'id'))
                            ->native(false)
                            ->required(),
                        TextInput::make('kilos')
                            ->label('Kilos')
                            ->default(0.00)
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $set('amount_paid', ($state ?? 0) * ($get('price_per_kilo') ?? 0));
                            }),
                        TextInput::make('price_per_kilo')
                            ->label('Precio por Kilo')
                            ->default(0.00)
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $set('amount_paid', ($get('kilos') ?? 0) * ($state ?? 0));
                            })
                            ->afterStateHydrated(function ($component, $state) {
                                $record = $component->getRecord();
                                if ($record) {
                                    $classification = \App\Models\CustomerClassification::find($record->classification_id);
                                    $component->state($classification?->price ?? 0);
                                }
                            }),
                        TextInput::make('amount_paid')
                            ->label('Monto')
                            ->prefix('$')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->reactive()
                            ->default(0.00)
                            ->afterStateHydrated(function ($component) {
                                $record = $component->getRecord();
                                if ($record) {
                                    $component->state($record->amount_paid);
                                }
                            }),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount_paid')
            ->extremePaginationLinks()
            ->defaultSort('sale_date', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('sale_date')
                    ->label('Fecha')
                    ->date(),
                TextColumn::make('branch.name')
                    ->label('Sucursal'),
                TextColumn::make('classification.name')
                    ->label('Tipo'),
                TextColumn::make('kilos')
                    ->label('Kilos')
                    ->numeric()
                    ->summarize(Sum::make()->label(''))
                    ->alignEnd(),
                TextColumn::make('price_per_kilo')
                    ->label('Precio')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                TextColumn::make('amount_paid')
                    ->label('Venta')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
            ])
            ->filters([
                //
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
