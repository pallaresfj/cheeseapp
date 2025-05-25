<?php

namespace App\Filament\Resources\WeeklyBalanceResource\Pages;

use App\Filament\Resources\WeeklyBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions\Action;
use Filament\Forms;

class ManageWeeklyBalances extends ManageRecords
{
    protected static string $resource = WeeklyBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('conciliar')
                ->label('Crear Conciliación')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('branch_id')
                        ->label('Sucursal')
                        ->options(\App\Models\Branch::where('active', true)->pluck('name', 'id'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $min = \App\Models\Movement::where('branch_id', $state)->where('status', 'pending')->min('date');
                            $max = \App\Models\Movement::where('branch_id', $state)->where('status', 'pending')->max('date');
                            $set('from', $min);
                            $set('until', $max);
                        }),
                    Forms\Components\DatePicker::make('from')
                        ->label('Desde')
                        ->required(),
                    Forms\Components\DatePicker::make('until')
                        ->label('Hasta')
                        ->required(),
                    Forms\Components\DatePicker::make('date')
                        ->label('Fecha del balance')
                        ->default(now()),
                ])
                ->action(function (array $data) {
                    \Illuminate\Support\Facades\DB::statement(
                        'CALL reconcile_movements(?, ?, ?, ?)',
                        [
                            $data['branch_id'],
                            $data['from'],
                            $data['until'],
                            $data['date'],
                        ]
                    );
                })
                ->successNotificationTitle('Conciliación realizada correctamente'),
        ];
    }
}
