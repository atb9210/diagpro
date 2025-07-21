<?php

namespace App\Filament\Admin\Resources\OrdiniResource\Pages;

use App\Filament\Admin\Resources\OrdiniResource;
use App\Filament\Admin\Resources\OrdiniResource\Widgets\OrdiniStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrdinis extends ListRecords
{
    protected static string $resource = OrdiniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            OrdiniStatsWidget::class,
        ];
    }
}
