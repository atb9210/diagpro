<?php

namespace App\Filament\Admin\Resources\TrafficSourceResource\Pages;

use App\Filament\Admin\Resources\TrafficSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrafficSources extends ListRecords
{
    protected static string $resource = TrafficSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
