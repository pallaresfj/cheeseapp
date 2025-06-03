<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalePaymentResource\Pages;
use App\Filament\Resources\SalePaymentResource\RelationManagers;
use App\Models\SalePayment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalePaymentResource extends Resource
{
    protected static ?string $model = SalePayment::class;

    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Pago de Venta';
    protected static ?string $pluralLabel = 'Pagos de Venta';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Cliente')
                    ->relationship('user', 'name')
                    ->placeholder('Seleccione cliente')
                    ->searchable()
                    ->native(false)
                    ->options(User::where('role', 'customer')->where('status', true)->pluck('name', 'id'))
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

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
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
            ])
            ->filters([
                //
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
            'index' => Pages\ListSalePayments::route('/'),
            'create' => Pages\CreateSalePayment::route('/create'),
            'edit' => Pages\EditSalePayment::route('/{record}/edit'),
        ];
    }
}
