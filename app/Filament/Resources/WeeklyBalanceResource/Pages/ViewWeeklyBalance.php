<?php

namespace App\Filament\Resources\WeeklyBalanceResource\Pages;

use App\Filament\Resources\WeeklyBalanceResource;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\WeeklyBalanceResource\Widgets\WeeklyBalanceStatsOverview;
use Carbon\Carbon;

class ViewWeeklyBalance extends ViewRecord
{
    protected static string $resource = WeeklyBalanceResource::class;
}
