<?php

namespace App\Filament\Admin\Resources\RicorrenzeAttiveResource\Pages;

use App\Filament\Admin\Resources\RicorrenzeAttiveResource;
use App\Filament\Admin\Resources\RicorrenzeAttiveResource\Widgets\RicorrenzeAttiveStatsWidget;
use App\Filament\Admin\Resources\RicorrenzeAttiveResource\Widgets\AttivazioniChartWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRicorrenzeAttives extends ListRecords
{
    protected static string $resource = RicorrenzeAttiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Le ricorrenze vengono create automaticamente tramite gli ordini
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            RicorrenzeAttiveStatsWidget::class,
            AttivazioniChartWidget::class,
        ];
    }
}
