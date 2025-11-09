<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\MembersChart;
use App\Filament\Widgets\TeacherChart;
use Filament\Schemas\Components\Grid;
class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            // First, your stats widget
            StatsOverview::class, // <--- Change this to your real stats widget class
            MembersChart::class,
            TeacherChart::class,
        ];
    }
}
