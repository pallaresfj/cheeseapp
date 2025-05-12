<?php

namespace App\Filament\Resources\LoanResource\Pages;

use App\Filament\Resources\LoanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoan extends EditRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $userId = $this->form->getState()['user_id'] ?? null;
        $currentId = $this->record->id;

        $alreadyHasActiveLoan = \App\Models\Loan::where('user_id', $userId)
            ->where('id', '!=', $currentId)
            ->whereIn('status', ['active', 'overdue', 'suspended'])
            ->exists();

        if ($alreadyHasActiveLoan) {
            \Filament\Notifications\Notification::make()
                ->title('Este proveedor ya tiene un préstamo activo, vencido o suspendido')
                ->danger()
                ->send();

            $this->halt(); // Detiene la edición del registro
        }
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
