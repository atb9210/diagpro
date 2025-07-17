<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\ClientiChart;
use App\Filament\Admin\Widgets\ClientiStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected function getHeaderWidgets(): array
    {
        return [
            ClientiStatsOverview::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            ClientiChart::class,
        ];
    }
}
