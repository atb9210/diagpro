<?php

namespace App\Filament\Admin\Resources\FornitoreResource\Pages;

use App\Filament\Admin\Resources\FornitoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFornitores extends ListRecords
{
    protected static string $resource = FornitoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
