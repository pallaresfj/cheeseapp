<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function beforeCreate(): void
    {
        $userId = $this->form->getState()['user_id'] ?? null;

        $alreadyHasActiveLoan = \App\Models\Loan::where('user_id', $userId)
            ->whereIn('status', ['active', 'overdue', 'suspended'])
            ->exists();

        if ($alreadyHasActiveLoan) {
            \Filament\Notifications\Notification::make()
                ->title('Este proveedor ya tiene un préstamo activo, vencido o suspendido')
                ->danger()
                ->send();

            $this->halt(); // Detiene la creación del registro
        }
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}