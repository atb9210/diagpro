<?php

namespace App\Filament\Admin\Resources\OrdiniResource\Pages;

use App\Filament\Admin\Resources\OrdiniResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrdini extends ViewRecord
{
    protected static string $resource = OrdiniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}