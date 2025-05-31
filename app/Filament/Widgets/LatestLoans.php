<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestLoans extends BaseWidget
{
    protected static ?string $heading = 'Últimos Préstamos';
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated(false)
            ->query(
                Loan::query()
                    ->latest('date')
                    ->limit(5)
            )
            ->description('Últimos préstamos registrados.')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Proveedor')
                    ->wrap(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('COP', locale: 'es_CO')
                    ->alignEnd(),
                Tables\Columns\IconColumn::make('status')
                    ->label('')
                    ->alignCenter()
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
            ]);
    }
}
