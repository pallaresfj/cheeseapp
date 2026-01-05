<?php

namespace App\Filament\Resources\LiquidationResource\Pages;

use App\Filament\Resources\LiquidationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLiquidations extends ListRecords
{
    protected static string $resource = LiquidationResource::class;

    public function getTitle(): string
    {
        return match ($this->activeTab) {
            'archived' => 'Liquidaciones Archivadas',
            default => 'Liquidaciones Aprobadas',
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'liquidated' => Tab::make('Aprobadas')
                ->icon('heroicon-o-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'liquidated'))
                ->badge(fn () => \App\Models\Liquidation::where('status', 'liquidated')->count()),
            'archived' => Tab::make('Archivadas')
                ->icon('heroicon-o-archive-box-x-mark')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived'))
                ->badge(fn () => \App\Models\Liquidation::where('status', 'archived')->count()),
        ];
    }
}
