<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Filament\Resources\LoanResource\RelationManagers;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Average;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-end-on-rectangle';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?string $label = 'Préstamo';
    protected static ?string $pluralLabel = 'Préstamos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Proveedor')
                    ->placeholder('Seleccione proveedor')
                    ->relationship('user', 'name', function (Builder $query, \Filament\Forms\Get $get) {
                        $query->where('role', 'supplier')
                              ->whereHas('farms');

                        // Solo aplicar filtro si es creación (no hay ID aún)
                        if (blank($get('id'))) {
                            $query->whereDoesntHave('loans', function ($q) {
                                $q->whereIn('status', ['active', 'overdue', 'suspended']);
                            });
                        }
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('farm_id')
                    ->label('Finca')
                    ->placeholder('Seleccione finca')
                    ->relationship('farm', 'name', modifyQueryUsing: fn (Builder $query, \Filament\Forms\Get $get) => 
                        $query->where('user_id', $get('user_id'))
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha')
                        ->default(now())
                        ->required()
                        ->disabledOn('edit'),
                    Forms\Components\TextInput::make('amount')
                        ->label('Monto')
                        ->required()
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $amount = $state ?? 0;
                            $paid = $get('paid_value') ?? 0;
                            $installments = $get('installments') ?? 1;
                            $set('saldo', round($amount - $paid, 2));
                            $set('installment_value', $installments > 0 ? round(($amount - $paid) / $installments, 2) : 0);
                        })
                        ->disabledOn('edit'),
                    Forms\Components\TextInput::make('installments')
                        ->label('Cuotas')
                        ->required()
                        ->numeric()
                        ->default(1)
                        ->live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $amount = $get('amount') ?? 0;
                            $paid = $get('paid_value') ?? 0;
                            $installments = $state ?? 1;
                            $set('installment_value', $installments > 0 ? round(($amount - $paid) / $installments, 2) : 0);
                        }),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('paid_value')
                        ->label('Pagado')
                        ->required()
                        ->numeric()
                        ->default(0.00)
                        ->live()
                        ->disabled()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $amount = $get('amount') ?? 0;
                            $paid = $state ?? 0;
                            $installments = $get('installments') ?? 1;
                            $set('saldo', round($amount - $paid, 2));
                            $set('installment_value', $installments > 0 ? round(($amount - $paid) / $installments, 2) : 0);
                        }),
                    Forms\Components\TextInput::make('saldo')
                        ->label('Saldo')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(function ($state, $record) {
                            return $record?->amount - $record?->paid_value;
                        }),
                    Forms\Components\TextInput::make('installment_value')
                        ->label('Cuota')
                        ->disabled()
                        ->dehydrated(),
                ]),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options(fn (\Filament\Forms\Get $get) => [
                        'active' => 'Activo',
                        'overdue' => 'Vencido',
                        'suspended' => 'Suspendido',
                        ...($get('status') === 'paid' ? ['paid' => 'Pagado'] : []),
                    ])
                    ->required()
                    ->hiddenOn('create')
                    ->disabled(fn (\Filament\Forms\Get $get) => $get('status') === 'paid'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->extremePaginationLinks()
            ->striped()
            ->columns([
                IconColumn::make('status')
                    ->label('')
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-currency-dollar',
                        'paid' => 'heroicon-o-check-circle',
                        'overdue' => 'heroicon-o-x-circle',
                        'suspended' => 'heroicon-o-pause-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paid' => 'info',
                        'overdue' => 'danger',
                        'suspended' => 'warning',
                    })
                    ->tooltip(fn (string $state): string => match ($state) {
                        'active' => 'Activo',
                        'paid' => 'Pagado',
                        'overdue' => 'Vencido',
                        'suspended' => 'Suspendido',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('proveedor_finca')
                    ->label('Proveedor - Finca')
                    ->getStateUsing(fn ($record) => "{$record->user->name} - {$record->farm->name}")
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                    ->orWhereHas('farm', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('installments')
                    ->label('Cuotas')
                    ->numeric()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('installment_value')
                    ->label('Cuota')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('paid_value')
                    ->label('Pagado')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money('COP', locale: 'es_CO')
                    ->getStateUsing(fn ($record) => $record->amount - $record->paid_value)
                    ->alignEnd(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('farm.branch', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->tooltip('Editar')
                    ->iconSize('h-6 w-6')
                    ->disabled(fn ($record) => $record->status === 'paid'),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip('Borrar')
                    ->iconSize('h-6 w-6')
                    ->disabled(fn ($record) =>
                        in_array($record->status, ['overdue', 'suspended']) ||
                        ($record->status === 'active' && $record->amount > $record->paid_value && $record->paid_value > 0)
                    ),
            ])
            ->bulkActions([
                /* Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]), */
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }
}
