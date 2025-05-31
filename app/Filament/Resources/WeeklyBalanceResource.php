<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeeklyBalanceResource\Pages;
use App\Filament\Resources\WeeklyBalanceResource\RelationManagers;
use App\Models\WeeklyBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class WeeklyBalanceResource extends Resource
{
    protected static ?string $model = WeeklyBalance::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?string $label = 'Balance Semanal';
    protected static ?string $pluralLabel = 'Balances Semanales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Sucursal')
                            ->relationship('branch', 'name')
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha')
                            ->required(),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('incomes')
                                    ->label('Ingresos')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('expenses')
                                    ->label('Egresos')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('net_balance')
                                    ->label('Neto')
                                    ->numeric()
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date(),
                Tables\Columns\TextColumn::make('incomes')
                    ->label('Ingresos')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('expenses')
                    ->label('Egresos')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('net_balance')
                    ->label('Neto')
                    ->money('COP', locale: 'es_CO')
                    ->summarize(Sum::make()->label('')->money('COP', locale: 'es_CO'))
                    ->alignEnd(),
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
                Tables\Grouping\Group::make('branch.name')
                    ->label('Sucursal')
                    ->collapsible()
            ])
            ->defaultGroup('branch.name')
            ->groupingSettingsHidden()
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip('Borrar')
                    ->iconSize('h-6 w-6')
                    ->before(function ($record) {
                        $record->movements()->update([
                            'weekly_balance_id' => null,
                            'status' => 'pending',
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                $record->movements()->update([
                                    'weekly_balance_id' => null,
                                    'status' => 'pending',
                                ]);
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWeeklyBalances::route('/'),
            'view' => Pages\ViewWeeklyBalance::route('/{record}/view'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MovementsRelationManager::class,
        ];
    }
}
